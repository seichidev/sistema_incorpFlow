<?php

namespace App\Filament\Resources\EquipamentoResource\Pages;

use App\Filament\Resources\EquipamentoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditarEquipamento extends EditRecord
{
    protected static string $resource = EquipamentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Inativar')
                ->icon('heroicon-m-archive-box-arrow-down')
                ->modalHeading('Inativar equipamento')
                ->modalDescription('O equipamento fica inativo e pode ser reativado depois.')
                ->modalSubmitActionLabel('Sim, inativar')
                ->successNotificationTitle('Registro inativado'),
            Actions\RestoreAction::make()
                ->label('Reativar')
                ->successNotificationTitle('Registro reativado'),
            Actions\ForceDeleteAction::make()
                ->label('Excluir definitivamente'),
        ];
    }
}
