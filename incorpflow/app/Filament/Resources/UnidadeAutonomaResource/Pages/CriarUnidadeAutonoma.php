<?php

namespace App\Filament\Resources\UnidadeAutonomaResource\Pages;

use App\Filament\Resources\UnidadeAutonomaResource;
use Filament\Resources\Pages\CreateRecord;

class CriarUnidadeAutonoma extends CreateRecord
{
    protected static string $resource = UnidadeAutonomaResource::class;

    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        return route('filament.admin.resources.unidades-autonomas.index');
    }
}
