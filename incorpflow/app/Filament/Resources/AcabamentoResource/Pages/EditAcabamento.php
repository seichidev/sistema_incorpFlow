<?php

namespace App\Filament\Resources\AcabamentoResource\Pages;

use App\Filament\Resources\AcabamentoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAcabamento extends EditRecord
{
    protected static string $resource = AcabamentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->modalHeading('Deletar acabamento')
                ->modalDescription('Tem certeza que deseja deletar este acabamento? Esta ação não pode ser desfeita.')
                ->modalSubmitActionLabel('Sim, deletar'),
        ];
    }
}
