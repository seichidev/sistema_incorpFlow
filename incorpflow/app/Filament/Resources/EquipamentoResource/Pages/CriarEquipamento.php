<?php

namespace App\Filament\Resources\EquipamentoResource\Pages;

use App\Filament\Resources\EquipamentoResource;
use Filament\Resources\Pages\CreateRecord;

class CriarEquipamento extends CreateRecord
{
    protected static string $resource = EquipamentoResource::class;

    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        return route('filament.admin.resources.equipamentos.index');
    }
}
