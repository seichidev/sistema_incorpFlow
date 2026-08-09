<?php

namespace App\Filament\Resources\UnidadeAutonomaResource\Pages;

use App\Filament\Resources\UnidadeAutonomaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListarUnidadesAutonomas extends ListRecords
{
    protected static string $resource = UnidadeAutonomaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
