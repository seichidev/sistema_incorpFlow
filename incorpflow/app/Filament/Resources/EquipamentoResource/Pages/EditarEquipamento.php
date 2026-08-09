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
                ->modalHeading('Deletar equipamento')
                ->modalDescription('Tem certeza que deseja deletar este equipamento? Esta ação não pode ser desfeita.')
                ->modalSubmitActionLabel('Sim, deletar'),
        ];
    }
}
