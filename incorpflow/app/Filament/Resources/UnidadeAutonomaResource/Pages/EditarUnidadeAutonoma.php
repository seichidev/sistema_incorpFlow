<?php

namespace App\Filament\Resources\UnidadeAutonomaResource\Pages;

use App\Filament\Resources\UnidadeAutonomaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditarUnidadeAutonoma extends EditRecord
{
    protected static string $resource = UnidadeAutonomaResource::class;

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
}
