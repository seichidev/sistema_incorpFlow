<?php

namespace App\Filament\Resources\AcabamentoResource\Pages;

use App\Filament\Resources\AcabamentoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditarAcabamento extends EditRecord
{
    protected static string $resource = AcabamentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Inativar')
                ->icon('heroicon-m-archive-box-arrow-down')
                ->modalHeading('Inativar acabamento')
                ->modalDescription('O acabamento fica inativo e pode ser reativado depois.')
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
