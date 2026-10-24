<?php

namespace App\Providers;

use Filament\Actions\ForceDeleteAction;
use Filament\Tables\Actions\ForceDeleteAction as TableForceDeleteAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configurarExclusaoDefinitiva();
    }

    /**
     * "Excluir definitivamente" mantém a linha no banco: o registro só deixa
     * de aparecer na interface (ver App\Models\Concerns\ExcluiDefinitivamente).
     * Configurado aqui para valer em todas as telas, inclusive as futuras.
     */
    private function configurarExclusaoDefinitiva(): void
    {
        $excluir = static fn (Model $record): bool => method_exists($record, 'excluirDefinitivamente')
            ? $record->excluirDefinitivamente()
            : (bool) $record->forceDelete();

        $descricao = 'O registro deixa de aparecer no sistema e não poderá ser reativado.';

        ForceDeleteAction::configureUsing(fn (ForceDeleteAction $action) => $action
            ->modalDescription($descricao)
            ->successNotificationTitle('Registro excluído')
            ->using($excluir), isImportant: true);

        TableForceDeleteAction::configureUsing(fn (TableForceDeleteAction $action) => $action
            ->modalDescription($descricao)
            ->successNotificationTitle('Registro excluído')
            ->using($excluir), isImportant: true);

        ForceDeleteBulkAction::configureUsing(fn (ForceDeleteBulkAction $action) => $action
            ->modalDescription('Os registros selecionados deixam de aparecer no sistema e não poderão ser reativados.')
            ->successNotificationTitle('Registros excluídos')
            ->using(fn (Collection $records) => $records->each(function (Model $record) use ($excluir): void {
                $excluir($record);
            })), isImportant: true);
    }
}
