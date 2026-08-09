<?php

namespace App\Filament\Resources\ConstrucaoResource\Pages;

use App\Filament\Resources\ConstrucaoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListarConstrucoes extends ListRecords
{
    protected static string $resource = ConstrucaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
