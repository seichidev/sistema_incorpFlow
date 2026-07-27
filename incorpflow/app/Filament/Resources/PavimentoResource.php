<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PavimentoResource\Pages;
use App\Models\Construcao;
use App\Models\Pavimento;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PavimentoResource extends Resource
{
    protected static ?string $model = Pavimento::class;

    protected static ?string $slug = 'pavimentos';
    protected static ?string $navigationLabel = 'Pavimentos';
    protected static ?string $modelLabel = 'Pavimento';
    protected static ?string $pluralModelLabel = 'Pavimentos';
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('construcao_id')
                    ->label('Construção')
                    ->options(Construcao::pluck('nome', 'id'))
                    ->searchable()
                    ->required(),

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
                    ->label('Quantidade de pavimentos idênticos')
                    ->integer()
                    ->default(1)
                    ->minValue(1)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('construcao.nome')
                    ->label('Construção')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('torre')
                    ->label('Torre / Bloco')
                    ->default('-')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('nome')
                    ->label('Nome')
                    ->searchable(),

                Tables\Columns\TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge(),

                Tables\Columns\TextColumn::make('nivel')
                    ->label('Nível (m)')
                    ->sortable(),

                Tables\Columns\TextColumn::make('quantidade')
                    ->label('Qtd.')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('construcao')
                    ->label('Construção')
                    ->relationship('construcao', 'nome'),

                Tables\Filters\SelectFilter::make('tipo')
                    ->options([
                        'Inferior'   => 'Inferior',
                        'Terreo'     => 'Térreo',
                        'Superior'   => 'Superior',
                        'Lazer'      => 'Lazer',
                        'Tipo'       => 'Tipo',
                        'Barrilete'  => 'Barrilete',
                        'CaixaDAgua' => "Caixa d'Água",
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->modalHeading('Deletar pavimento')
                    ->modalDescription('Tem certeza que deseja deletar este pavimento? Esta ação não pode ser desfeita.')
                    ->modalSubmitActionLabel('Sim, deletar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('construcao');
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\PavimentoResource\RelationManagers\UnidadesAutonomasRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPavimentos::route('/'),
            'create' => Pages\CreatePavimento::route('/create'),
            'edit'   => Pages\EditPavimento::route('/{record}/edit'),
        ];
    }
}
