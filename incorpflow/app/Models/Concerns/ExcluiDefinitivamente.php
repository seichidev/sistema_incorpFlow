<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Exclusão definitiva sem apagar a linha.
 *
 * O registro excluído recebe `excluido_em` e passa a ser filtrado por um
 * escopo global próprio. Como esse escopo não é o SoftDeletingScope, ele
 * continua valendo nas consultas que usam withTrashed() ou
 * withoutGlobalScopes([SoftDeletingScope::class]) — ou seja, em todas as
 * listagens, filtros "inativos", selects e na rota de edição.
 *
 * Para consultar os excluídos: Model::withoutGlobalScope('ocultar_excluidos').
 */
trait ExcluiDefinitivamente
{
    public static function bootExcluiDefinitivamente(): void
    {
        static::addGlobalScope('ocultar_excluidos', function (Builder $query): void {
            $query->whereNull($query->qualifyColumn('excluido_em'));
        });
    }

    public function initializeExcluiDefinitivamente(): void
    {
        $this->mergeCasts(['excluido_em' => 'datetime']);
    }

    /**
     * Retorna true para que as ações do Filament reconheçam o sucesso.
     */
    public function excluirDefinitivamente(): bool
    {
        DB::transaction(function (): void {
            $this->excluirDependentesDefinitivamente();

            $agora = $this->freshTimestamp();

            // deleted_at também é preenchido: um registro excluído nunca pode
            // contar como ativo nas consultas que só olham deleted_at.
            $this->forceFill([
                'excluido_em' => $agora,
                $this->getDeletedAtColumn() => $this->{$this->getDeletedAtColumn()} ?? $agora,
            ])->saveQuietly();
        });

        return true;
    }

    public function foiExcluidoDefinitivamente(): bool
    {
        return $this->excluido_em !== null;
    }

    /**
     * Composição: cada modelo repassa a exclusão aos registros que dependem dele.
     */
    protected function excluirDependentesDefinitivamente(): void
    {
        //
    }
}
