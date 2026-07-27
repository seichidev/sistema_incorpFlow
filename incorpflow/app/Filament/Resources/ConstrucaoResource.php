<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConstrucaoResource\Pages;
use App\Models\Construcao;
use App\Models\UnidadeAutonoma;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ConstrucaoResource extends Resource
{
    protected static ?string $model = Construcao::class;

    protected static ?string $navigationLabel = 'Construções';
    protected static ?string $modelLabel = 'Construção';
    protected static ?string $pluralModelLabel = 'Construções';

    protected static ?string $slug = 'construcoes';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nome')
                    ->label("Nome completo")
	            ->required()
                    ->maxLength(255),


                Forms\Components\TextInput::make('endereco')
		    ->label("Endereço")
                    ->required()
                    ->maxLength(255),


                Forms\Components\TextInput::make('cidade')
                    ->label("Cidade")
	            ->required()
                    ->maxLength(255),


                Forms\Components\TextInput::make('estado')
		    ->label('UF')
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
		    ->label('Responsável')
                    ->required()
                    ->maxLength(255),


                Forms\Components\Textarea::make('descricao')
                    ->label('Explique com detalhes sobre essa construção')
		    ->columnSpanFull(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nome')->searchable(),
                Tables\Columns\TextColumn::make('endereco')->searchable(),
                Tables\Columns\TextColumn::make('cidade')->searchable(),
                Tables\Columns\TextColumn::make('estado')->searchable(),
                Tables\Columns\TextColumn::make('tipo')->searchable(),
                Tables\Columns\TextColumn::make('status')->searchable(),
                Tables\Columns\TextColumn::make('total_pavimentos')
                    ->label('Pavimentos')
                    ->default(0)
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_unidades')
                    ->label('Unidades')
                    ->default(0)
                    ->sortable(),
                Tables\Columns\TextColumn::make('responsavel')->searchable(),
                
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
                Tables\Actions\DeleteAction::make()
                    ->modalHeading('Deletar construção')
                    ->modalDescription('Tem certeza que deseja deletar esta construção? Esta ação não pode ser desfeita.')
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
        return parent::getEloquentQuery()
            ->withSum('pavimentos as total_pavimentos', 'quantidade')
            ->addSelect([
                'total_unidades' => UnidadeAutonoma::query()
                    ->join('pavimentos', 'pavimentos.id', '=', 'unidades_autonomas.pavimento_id')
                    ->whereColumn('pavimentos.construcao_id', 'construcoes.id')
                    ->whereNull('pavimentos.deleted_at')
                    ->selectRaw('coalesce(sum(unidades_autonomas.quantidade), 0)'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\ConstrucaoResource\RelationManagers\PavimentosRelationManager::class,
            \App\Filament\Resources\ConstrucaoResource\RelationManagers\EquipamentosRelationManager::class,
            \App\Filament\Resources\ConstrucaoResource\RelationManagers\AcabamentosRelationManager::class,
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

