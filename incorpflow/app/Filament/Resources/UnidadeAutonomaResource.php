<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UnidadeAutonomaResource\Pages;
use App\Models\Construcao;
use App\Models\Pavimento;
use App\Models\UnidadeAutonoma;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UnidadeAutonomaResource extends Resource
{
    protected static ?string $model = UnidadeAutonoma::class;

    protected static ?string $slug = 'unidades-autonomas';
    protected static ?string $navigationLabel = 'Unidades Autônomas';
    protected static ?string $modelLabel = 'Unidade Autônoma';
    protected static ?string $pluralModelLabel = 'Unidades Autônomas';
    protected static ?string $navigationIcon = 'heroicon-o-home-modern';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('construcao_id')
                    ->label('Construção')
                    ->options(Construcao::pluck('nome', 'id'))
                    ->searchable()
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Forms\Set $set) => $set('pavimento_id', null))
                    ->dehydrated(false),

                Forms\Components\Select::make('pavimento_id')
                    ->label('Pavimento')
                    ->options(fn (Get $get) => Pavimento::where('construcao_id', $get('construcao_id'))->pluck('nome', 'id'))
                    ->searchable()
                    ->required()
                    ->live(),

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

                Forms\Components\TextInput::make('torre')
                    ->label('Torre')
                    ->maxLength(100)
                    ->placeholder('Ex: Torre Leste'),

                Forms\Components\Toggle::make('permutada')
                    ->label('Permutada')
                    ->helperText('Unidade dada como pagamento do terreno')
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('pavimento.construcao.nome')
                    ->label('Construção')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('pavimento.nome')
                    ->label('Pavimento')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('numero')
                    ->label('Número')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tipo')
                    ->label('Tipo')
                    ->searchable()
                    ->badge(),

                Tables\Columns\TextColumn::make('torre')
                    ->label('Torre')
                    ->searchable()
                    ->default('-'),

                Tables\Columns\IconColumn::make('permutada')
                    ->label('Permutada')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('construcao')
                    ->label('Construção')
                    ->relationship('pavimento.construcao', 'nome'),

                Tables\Filters\SelectFilter::make('pavimento')
                    ->label('Pavimento')
                    ->relationship('pavimento', 'nome'),

                Tables\Filters\TernaryFilter::make('permutada')
                    ->label('Permutada'),
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('pavimento.construcao');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUnidadesAutonomas::route('/'),
            'create' => Pages\CreateUnidadeAutonoma::route('/create'),
            'edit'   => Pages\EditUnidadeAutonoma::route('/{record}/edit'),
        ];
    }
}
