<?php

namespace App\Filament\Resources\ConstrucaoResource\RelationManagers;

use App\Filament\Resources\PavimentoResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PavimentosRelationManager extends RelationManager
{
    protected static string $relationship = 'pavimentos';
    protected static ?string $title = 'Pavimentos';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nome')
                    ->label('Nome')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('tipo')
                    ->label('Tipo')
                    ->options([
                        'Inferior'   => 'Inferior',
                        'Terreo'     => 'Térreo',
                        'Superior'   => 'Superior',
                        'Lazer'      => 'Lazer',
                        'Tipo'       => 'Tipo',
                        'Barrilete'  => 'Barrilete',
                        'CaixaDAgua' => "Caixa d'Água",
                    ])
                    ->required(),

                Forms\Components\TextInput::make('torre')
                    ->label('Torre / Bloco')
                    ->maxLength(100)
                    ->placeholder('Ex: Torre A, Bloco 1'),

                Forms\Components\TextInput::make('nivel')
                    ->label('Nível (m)')
                    ->numeric()
                    ->step(0.01)
                    ->placeholder('Ex: 571.85'),

                Forms\Components\TextInput::make('quantidade')
                    ->label('Pavimentos idênticos')
                    ->integer()
                    ->default(1)
                    ->minValue(1)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('torre')
                    ->label('Torre / Bloco')
                    ->default('-')
                    ->searchable(),

                Tables\Columns\TextColumn::make('nome')
                    ->label('Nome')
                    ->searchable(),

                Tables\Columns\TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge(),

                Tables\Columns\TextColumn::make('nivel')
                    ->label('Nível (m)'),

                Tables\Columns\TextColumn::make('quantidade')
                    ->label('Qtd.'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Novo Pavimento'),
            ])
            ->actions([
                Tables\Actions\Action::make('editar')
                    ->label('Editar')
                    ->icon('heroicon-m-pencil-square')
                    ->url(fn ($record) => PavimentoResource::getUrl('edit', ['record' => $record])),
                Tables\Actions\DeleteAction::make()
                    ->modalHeading('Deletar pavimento')
                    ->modalDescription('Tem certeza que deseja deletar este pavimento?')
                    ->modalSubmitActionLabel('Sim, deletar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
