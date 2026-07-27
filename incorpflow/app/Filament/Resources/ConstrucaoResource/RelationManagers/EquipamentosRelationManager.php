<?php

namespace App\Filament\Resources\ConstrucaoResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class EquipamentosRelationManager extends RelationManager
{
    protected static string $relationship = 'equipamentos';
    protected static ?string $title = 'Equipamentos';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nome')
                    ->label('Equipamento')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Textarea::make('tipo_ou_marca')
                    ->label('Tipo / Marca')
                    ->rows(3),

                Forms\Components\TextInput::make('acabamento')
                    ->label('Acabamento')
                    ->maxLength(255),

                Forms\Components\Textarea::make('detalhes_gerais')
                    ->label('Detalhes Gerais')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nome')
                    ->label('Equipamento')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('acabamento')
                    ->label('Acabamento')
                    ->default('-'),

                Tables\Columns\TextColumn::make('detalhes_gerais')
                    ->label('Detalhes')
                    ->limit(60)
                    ->default('-'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Novo Equipamento'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->modalHeading('Deletar equipamento')
                    ->modalDescription('Tem certeza que deseja deletar este equipamento?')
                    ->modalSubmitActionLabel('Sim, deletar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
