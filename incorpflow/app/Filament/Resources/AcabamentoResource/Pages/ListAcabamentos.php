<?php

namespace App\Filament\Resources\AcabamentoResource\Pages;

use App\Filament\Resources\AcabamentoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAcabamentos extends ListRecords
{
    protected static string $resource = AcabamentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Novo Acabamento'),
        ];
    }
}
