<?php

namespace App\Filament\Resources\PavimentoResource\Pages;

use App\Filament\Resources\PavimentoResource;
use Filament\Resources\Pages\CreateRecord;

class CriarPavimento extends CreateRecord
{
    protected static string $resource = PavimentoResource::class;

    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        return route('filament.admin.resources.pavimentos.index');
    }
}
