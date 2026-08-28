<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UnidadeAutonomaResource\Pages;
use App\Models\Construcao;
use App\Models\Pavimento;
use App\Models\UnidadeAutonoma;
use App\Rules\NumerosSemDuplicidade;
use App\Rules\QuantidadeCorrespondeAosNumeros;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UnidadeAutonomaResource extends Resource
{
    protected static ?string $model = UnidadeAutonoma::class;

    protected static ?string $slug = 'unidades-autonomas';
    protected static ?string $navigationLabel = 'Unidades Autônomas';
    protected static ?string $modelLabel = 'Unidade Autônoma';
    protected static ?string $pluralModelLabel = 'Unidades Autônomas';
    protected static ?string $navigationIcon = 'heroicon-o-home-modern';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('construcao_id')
                    ->label('Construção')
                    ->options(fn (): array => Construcao::orderBy('nome')->pluck('nome', 'id')->all())
                    ->searchable()
                    ->required()
                    ->live()
                    ->afterStateHydrated(function (Forms\Components\Select $component, $record) {
                        if ($record?->pavimento) {
                            $component->state($record->pavimento->construcao_id);
                        }
                    })
                    ->afterStateUpdated(fn (Forms\Set $set) => $set('pavimento_id', null))
                    ->dehydrated(false),

                Forms\Components\Select::make('pavimento_id')
                    ->label('Pavimento')
                    ->options(fn (Get $get) => Pavimento::where('construcao_id', $get('construcao_id'))->pluck('nome', 'id'))
                    ->searchable()
                    ->required()
                    ->live(),

                Forms\Components\Textarea::make('numero')
                    ->label('Número(s)')
                    ->required()
                    ->rows(3)
                    ->placeholder('Ex: 101, 104, 105, 108, 201, 204, 205, 208')
                    ->live(onBlur: true)
                    ->rules([new NumerosSemDuplicidade()])
                    ->helperText(fn (?string $state): string => match ($n = count(UnidadeAutonoma::separarNumeros($state))) {
                        0 => 'Números de todas as unidades desta tipologia, como no Quadro IV-B',
                        1 => '1 unidade na lista',
                        default => "{$n} unidades na lista",
                    })
                    ->columnSpanFull(),

                Forms\Components\Select::make('tipo')
                    ->label('Tipo')
                    ->options([
                        'Apartamento'     => 'Apartamento',
                        'Vaga de Garagem' => 'Vaga de Garagem',
                        'Sala Comercial'  => 'Sala Comercial',
                        'Loja'            => 'Loja',
                        'Box'             => 'Box',
                    ])
                    ->searchable()
                    ->required(),

                Forms\Components\TextInput::make('tipologia')
                    ->label('Tipologia')
                    ->maxLength(100)
                    ->placeholder('Ex: Tipo A')
                    ->helperText('Agrupamento de unidades com o mesmo projeto — usado nos Quadros da NBR')
                    ->datalist([
                        'Tipo A',
                        'Tipo B',
                        'Tipo C',
                        'Tipo D',
                        'Tipo E',
                        'Tipo F',
                    ]),

                Forms\Components\Fieldset::make('Áreas (Quadro II)')
                    ->schema([
                        Forms\Components\TextInput::make('area_privativa')
                            ->label('Área privativa (m²)')
                            ->numeric()
                            ->step(0.0001),

                        Forms\Components\TextInput::make('area_comum')
                            ->label('Área comum (m²)')
                            ->numeric()
                            ->step(0.0001),

                        Forms\Components\TextInput::make('area_equivalente')
                            ->label('Área equivalente (m²)')
                            ->numeric()
                            ->step(0.0001)
                            ->helperText('Área equivalente de construção — 4 casas decimais'),
                    ])
                    ->columns(3),

                Forms\Components\Toggle::make('permutada')
                    ->label('Permutada')
                    ->helperText('Unidade dada como pagamento do terreno')
                    ->default(false),

                Forms\Components\TextInput::make('quantidade')
                    ->label('Unidades idênticas')
                    ->integer()
                    ->default(1)
                    ->minValue(1)
                    ->required()
                    ->helperText('Precisa ser igual ao número de itens da lista acima')
                    ->rules([
                        fn (Get $get): QuantidadeCorrespondeAosNumeros => new QuantidadeCorrespondeAosNumeros($get('numero')),
                    ]),
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

                Tables\Columns\TextColumn::make('pavimento.torre')
                    ->label('Torre / Bloco')
                    ->default('-')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('pavimento.nome')
                    ->label('Pavimento')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('numero')
                    ->label('Número(s)')
                    ->limit(40)
                    ->tooltip(fn (UnidadeAutonoma $record) => $record->numero)
                    ->searchable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('tipo')
                    ->label('Tipo')
                    ->searchable()
                    ->badge(),

                Tables\Columns\TextColumn::make('tipologia')
                    ->label('Tipologia')
                    ->default('-')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('quantidade')
                    ->label('Qtd.')
                    ->badge()
                    ->color(fn (UnidadeAutonoma $record): string => $record->quantidade_confere ? 'success' : 'danger')
                    ->icon(fn (UnidadeAutonoma $record): ?string => $record->quantidade_confere ? null : 'heroicon-m-exclamation-triangle')
                    ->tooltip(fn (UnidadeAutonoma $record): ?string => $record->quantidade_confere
                        ? null
                        : "A lista de números tem {$record->quantidade_listada} unidade(s)")
                    ->sortable(),

                Tables\Columns\TextColumn::make('area_privativa')
                    ->label('Á. Privativa (m²)')
                    ->numeric(4)
                    ->default('-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('area_equivalente')
                    ->label('Á. Equivalente (m²)')
                    ->numeric(4)
                    ->default('-')
                    ->sortable(),

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
                    ->query(fn (Builder $query, array $data) =>
                        $data['value']
                            ? $query->whereHas('pavimento', fn ($q) => $q->where('construcao_id', $data['value']))
                            : $query
                    )
                    ->options(fn (): array => Construcao::orderBy('nome')->pluck('nome', 'id')->all()),

                Tables\Filters\SelectFilter::make('pavimento')
                    ->label('Pavimento')
                    ->relationship('pavimento', 'nome'),

                Tables\Filters\TernaryFilter::make('permutada')
                    ->label('Permutada'),

                Tables\Filters\TrashedFilter::make()
                    ->label('Situação')
                    ->placeholder('Somente ativos')
                    ->trueLabel('Ativos e inativos')
                    ->falseLabel('Somente inativos'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->label('Inativar')
                    ->icon('heroicon-m-archive-box-arrow-down')
                    ->modalHeading('Inativar unidade autônoma')
                    ->modalDescription('A tipologia fica inativa junto com seus acabamentos. Ambos podem ser reativados depois.')
                    ->modalSubmitActionLabel('Sim, inativar')
                    ->successNotificationTitle('Registro inativado'),
                Tables\Actions\RestoreAction::make()
                    ->label('Reativar')
                    ->successNotificationTitle('Registro reativado'),
                Tables\Actions\ForceDeleteAction::make()
                    ->label('Excluir definitivamente'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Inativar selecionados'),
                    Tables\Actions\RestoreBulkAction::make()
                        ->label('Reativar selecionados'),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->label('Excluir definitivamente'),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class])
            ->with('pavimento.construcao');
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\UnidadeAutonomaResource\RelationManagers\AcabamentosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListarUnidadesAutonomas::route('/'),
            'create' => Pages\CriarUnidadeAutonoma::route('/create'),
            'edit'   => Pages\EditarUnidadeAutonoma::route('/{record}/edit'),
        ];
    }
}
