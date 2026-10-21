<?php

namespace App\Filament\Resources\ConstrucaoResource\Pages;

use App\Filament\Resources\ConstrucaoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditarConstrucao extends EditRecord
{
    protected static string $resource = ConstrucaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Inativar')
                ->icon('heroicon-m-archive-box-arrow-down')
                ->successNotificationTitle('Registro inativado'),
            Actions\RestoreAction::make()
                ->label('Reativar')
                ->successNotificationTitle('Registro reativado'),
            Actions\ForceDeleteAction::make()
                ->label('Excluir definitivamente'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return route('filament.admin.resources.construcoes.index');
    }
}
