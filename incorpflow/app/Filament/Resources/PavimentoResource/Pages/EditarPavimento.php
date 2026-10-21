<?php

namespace App\Filament\Resources\PavimentoResource\Pages;

use App\Filament\Resources\PavimentoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditarPavimento extends EditRecord
{
    protected static string $resource = PavimentoResource::class;

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
        return route('filament.admin.resources.pavimentos.index');
    }
}
