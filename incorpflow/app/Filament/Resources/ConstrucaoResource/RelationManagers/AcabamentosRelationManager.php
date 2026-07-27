<?php

namespace App\Filament\Resources\ConstrucaoResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class AcabamentosRelationManager extends RelationManager
{
    protected static string $relationship = 'acabamentos';
    protected static ?string $title = 'Acabamentos';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('escopo')
                    ->label('Escopo')
                    ->options([
                        'Privativo' => 'Privativo (Q7)',
                        'Comum'     => 'Comum (Q8)',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('dependencia')
                    ->label('Dependência')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Ex: Sala Estar/Jantar, Hall Elevadores')
                    ->columnSpanFull(),

                Forms\Components\Fieldset::make('Piso')
                    ->schema([
                        Forms\Components\TextInput::make('piso_revestimento')->label('Revestimento'),
                        Forms\Components\TextInput::make('piso_acabamento')->label('Acabamento'),
                        Forms\Components\TextInput::make('piso_soleira')->label('Soleira'),
                    ])
                    ->columns(3),

                Forms\Components\Fieldset::make('Parede')
                    ->schema([
                        Forms\Components\TextInput::make('parede_revestimento')->label('Revestimento'),
                        Forms\Components\TextInput::make('parede_acabamento')->label('Acabamento'),
                        Forms\Components\TextInput::make('parede_rodape')->label('Rodapé'),
                    ])
                    ->columns(3),

                Forms\Components\Fieldset::make('Teto')
                    ->schema([
                        Forms\Components\TextInput::make('teto_revestimento')->label('Revestimento'),
                        Forms\Components\TextInput::make('teto_acabamento')->label('Acabamento'),
                    ])
                    ->columns(2),

                Forms\Components\TextInput::make('peitoris')
                    ->label('Peitoris')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('escopo')
                    ->label('Escopo')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'Privativo' => 'info',
                        'Comum'     => 'success',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('dependencia')
                    ->label('Dependência')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('piso_revestimento')
                    ->label('Piso')
                    ->limit(40)
                    ->default('-'),

                Tables\Columns\TextColumn::make('parede_revestimento')
                    ->label('Parede')
                    ->limit(40)
                    ->default('-'),

                Tables\Columns\TextColumn::make('teto_revestimento')
                    ->label('Teto')
                    ->limit(40)
                    ->default('-'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Novo Acabamento'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('escopo')
                    ->options([
                        'Privativo' => 'Privativo',
                        'Comum'     => 'Comum',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->modalHeading('Deletar acabamento')
                    ->modalDescription('Tem certeza que deseja deletar este acabamento?')
                    ->modalSubmitActionLabel('Sim, deletar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
