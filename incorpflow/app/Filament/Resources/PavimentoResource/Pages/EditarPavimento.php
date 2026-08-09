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
            Actions\DeleteAction::make(),
        ];
    }
}
