<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConstrucaoResource\Pages;
use App\Filament\Resources\ConstrucaoResource\RelationManagers;
use App\Models\Construcao;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ConstrucaoResource extends Resource
{
    protected static ?string $model = Construcao::class;

    protected static ?string $navigationLabel = 'Construções';
    protected static ?string $modelLabel = 'Construção';
    protected static ?string $pluralModelLabel = 'Construções';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nome')
                    ->required()
                    ->maxLength(255),


                Forms\Components\TextInput::make('endereco')
                    ->required()
                    ->maxLength(255),


                Forms\Components\TextInput::make('cidade')
                    ->required()
                    ->maxLength(255),


                Forms\Components\TextInput::make('estado')
                    ->required()
                    ->maxLength(2),


                Forms\Components\Select::make('tipo')
		    ->options([
                        'Residencial' => 'Residencial',
		        'Comercial' => 'Comercial',
		        'Misto' => 'Misto',
		        'Industrial' => 'Industrial',
		    ])
		    ->required(),


                Forms\Components\Select::make('status')
                    ->options([
		        'Planejamento' => 'Planejamento',
			'Em construção' => 'Em construção',
			'Concluída' => 'Concluída',
			'Suspensa' => 'Suspensa',
		    ])
                    ->default('Planejamento')
	            ->required(),


                Forms\Components\TextInput::make('responsavel')
                    ->required()
                    ->maxLength(255),


                Forms\Components\Textarea::make('descricao')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nome')
                    ->searchable(),
                Tables\Columns\TextColumn::make('endereco')
                    ->searchable(),
                Tables\Columns\TextColumn::make('cidade')
                    ->searchable(),
                Tables\Columns\TextColumn::make('estado')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tipo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('responsavel')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConstrucaos::route('/'),
            'create' => Pages\CreateConstrucao::route('/create'),
            'edit' => Pages\EditConstrucao::route('/{record}/edit'),
        ];
    }
}
