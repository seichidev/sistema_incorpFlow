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
            Actions\DeleteAction::make(),
        ];
    }
}
