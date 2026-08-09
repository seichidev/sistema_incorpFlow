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
            Actions\DeleteAction::make(),
        ];
    }
}
