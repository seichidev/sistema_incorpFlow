<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AcabamentoResource\Pages;
use App\Models\Acabamento;
use App\Models\Construcao;
use App\Models\UnidadeAutonoma;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AcabamentoResource extends Resource
{
    protected static ?string $model = Acabamento::class;

    protected static ?string $slug = 'acabamentos';
    protected static ?string $navigationLabel = 'Acabamentos';
    protected static ?string $modelLabel = 'Acabamento';
    protected static ?string $pluralModelLabel = 'Acabamentos';
    protected static ?string $navigationIcon = 'heroicon-o-paint-brush';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('acabavel_type')
                    ->label('Pertence a')
                    ->options([
                        'App\Models\Construcao'      => 'Construção',
                        'App\Models\UnidadeAutonoma' => 'Unidade Autônoma',
                    ])
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Forms\Set $set) => $set('acabavel_id', null)),

                Forms\Components\Select::make('acabavel_id')
                    ->label('Registro')
                    ->options(function (Get $get) {
                        return match ($get('acabavel_type')) {
                            'App\Models\Construcao'      => Construcao::pluck('nome', 'id'),
                            'App\Models\UnidadeAutonoma' => UnidadeAutonoma::query()
                                ->join('pavimentos', 'pavimentos.id', '=', 'unidades_autonomas.pavimento_id')
                                ->join('construcoes', 'construcoes.id', '=', 'pavimentos.construcao_id')
                                ->whereNull('pavimentos.deleted_at')
                                ->whereNull('construcoes.deleted_at')
                                ->selectRaw("unidades_autonomas.id, construcoes.nome || ' — ' || unidades_autonomas.numero || ' (' || unidades_autonomas.tipo || ')' as label")
                                ->pluck('label', 'unidades_autonomas.id'),
                            default => [],
                        };
                    })
                    ->searchable()
                    ->required(),

                Forms\Components\Select::make('escopo')
                    ->label('Escopo')
                    ->options([
                        'Privativo' => 'Privativo (Q7)',
                        'Comum'     => 'Comum (Q8)',
                    ])
                    ->required(),

                // Todos os campos descritivos são `text` no banco: o Quadro VIII
                // chega a 365 caracteres em DEPENDÊNCIAS e a 210 em PEITORIS.
                Forms\Components\Textarea::make('dependencia')
                    ->label('Dependência')
                    ->required()
                    ->rows(2)
                    ->placeholder('Ex: Sala Estar/Jantar, Hall Elevadores')
                    ->columnSpanFull(),

                Forms\Components\Fieldset::make('Piso')
                    ->schema([
                        Forms\Components\Textarea::make('piso_revestimento')->label('Revestimento')->rows(2),
                        Forms\Components\Textarea::make('piso_acabamento')->label('Acabamento')->rows(2),
                        Forms\Components\Textarea::make('piso_soleira')->label('Soleira')->rows(2),
                    ])
                    ->columns(3),

                Forms\Components\Fieldset::make('Parede')
                    ->schema([
                        Forms\Components\Textarea::make('parede_revestimento')->label('Revestimento')->rows(2),
                        Forms\Components\Textarea::make('parede_acabamento')->label('Acabamento')->rows(2),
                        Forms\Components\Textarea::make('parede_rodape')->label('Rodapé')->rows(2),
                    ])
                    ->columns(3),

                Forms\Components\Fieldset::make('Teto')
                    ->schema([
                        Forms\Components\Textarea::make('teto_revestimento')->label('Revestimento')->rows(2),
                        Forms\Components\Textarea::make('teto_acabamento')->label('Acabamento')->rows(2),
                    ])
                    ->columns(2),

                Forms\Components\Textarea::make('peitoris')
                    ->label('Peitoris')
                    ->rows(2)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('acabavel_type')
                    ->label('Tipo')
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'App\Models\Construcao'      => 'Construção',
                        'App\Models\UnidadeAutonoma' => 'Unidade Autônoma',
                        default                      => $state,
                    })
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'App\Models\Construcao'      => 'warning',
                        'App\Models\UnidadeAutonoma' => 'info',
                        default                      => 'gray',
                    }),

                Tables\Columns\TextColumn::make('escopo')
                    ->label('Escopo')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'Privativo' => 'info',
                        'Comum'     => 'success',
                        default     => 'gray',
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

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('acabavel_type')
                    ->label('Tipo')
                    ->options([
                        'App\Models\Construcao'      => 'Construção',
                        'App\Models\UnidadeAutonoma' => 'Unidade Autônoma',
                    ]),

                Tables\Filters\SelectFilter::make('escopo')
                    ->options([
                        'Privativo' => 'Privativo',
                        'Comum'     => 'Comum',
                    ]),

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
                    ->modalHeading('Inativar acabamento')
                    ->modalDescription('O acabamento fica inativo e pode ser reativado depois.')
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
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListarAcabamentos::route('/'),
            'create' => Pages\CriarAcabamento::route('/create'),
            'edit'   => Pages\EditarAcabamento::route('/{record}/edit'),
        ];
    }
}
