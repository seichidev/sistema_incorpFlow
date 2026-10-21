<?php

namespace App\Filament\Resources\ConstrucaoResource\Pages;

use App\Filament\Resources\ConstrucaoResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CriarConstrucao extends CreateRecord
{
    protected static string $resource = ConstrucaoResource::class;

    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        return route('filament.admin.resources.construcoes.index');
    }
}
