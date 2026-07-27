<?php

namespace App\Filament\Resources\PavimentoResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class UnidadesAutonomasRelationManager extends RelationManager
{
    protected static string $relationship = 'unidadesAutonomas';
    protected static ?string $title = 'Unidades Autônomas';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('numero')
                    ->label('Número')
                    ->required()
                    ->maxLength(20),

                Forms\Components\TextInput::make('tipo')
                    ->label('Tipo')
                    ->required()
                    ->maxLength(100)
                    ->datalist([
                        'Apartamento',
                        'Vaga de Garagem',
                        'Hall',
                        'Área de Lazer',
                        'Piscina',
                        'Salão de Festas',
                        'Academia',
                        'Coworking',
                        'Playground',
                        'Guarita',
                    ]),

                Forms\Components\Toggle::make('permutada')
                    ->label('Permutada')
                    ->helperText('Unidade dada como pagamento do terreno')
                    ->default(false),

                Forms\Components\TextInput::make('quantidade')
                    ->label('Unidades idênticas')
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
                Tables\Columns\TextColumn::make('numero')
                    ->label('Número')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tipo')
                    ->label('Tipo')
                    ->searchable()
                    ->badge(),

                Tables\Columns\TextColumn::make('quantidade')
                    ->label('Qtd.')
                    ->sortable(),

                Tables\Columns\IconColumn::make('permutada')
                    ->label('Permutada')
                    ->boolean(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Nova Unidade'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->modalHeading('Deletar unidade autônoma')
                    ->modalDescription('Tem certeza que deseja deletar esta unidade? Esta ação não pode ser desfeita.')
                    ->modalSubmitActionLabel('Sim, deletar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
