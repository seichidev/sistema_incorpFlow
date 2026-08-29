<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConstrucaoResource\Pages;
use App\Models\Construcao;
use App\Models\UnidadeAutonoma;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

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
                    ->label('Nome completo')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('endereco')
                    ->label('Endereço')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('estado')
                    ->live()
                    ->afterStateUpdated(fn (Set $set): mixed => $set('cidade', null))
                    ->options([
                        'AC' => 'Acre', 'AL' => 'Alagoas', 'AP' => 'Amapá', 'AM' => 'Amazonas',
                        'BA' => 'Bahia', 'CE' => 'Ceará', 'DF' => 'Distrito Federal', 'ES' => 'Espírito Santo',
                        'GO' => 'Goiás', 'MA' => 'Maranhão', 'MT' => 'Mato Grosso', 'MS' => 'Mato Grosso do Sul',
                        'MG' => 'Minas Gerais', 'PA' => 'Pará', 'PB' => 'Paraíba', 'PR' => 'Paraná',
                        'PE' => 'Pernambuco', 'PI' => 'Piauí', 'RJ' => 'Rio de Janeiro', 'RN' => 'Rio Grande do Norte',
                        'RS' => 'Rio Grande do Sul', 'RO' => 'Rondônia', 'RR' => 'Roraima', 'SC' => 'Santa Catarina',
                        'SP' => 'São Paulo', 'SE' => 'Sergipe', 'TO' => 'Tocantins',
                    ])
                    ->required()
                    ->searchable(),

                Forms\Components\Select::make('cidade')
                    ->label('Cidade')
                    ->options(function (Get $get): array {
                        $estado = $get('estado');

                        if (blank($estado)) {
                            return [];
                        }

                        return Cache::remember("ibge-municipios-{$estado}", now()->addDay(), function () use ($estado): array {
                            return Http::timeout(5)
                                ->get("https://servicodados.ibge.gov.br/api/v1/localidades/estados/{$estado}/municipios")
                                ->throw()
                                ->collect()
                                ->pluck('nome', 'nome')
                                ->sort()
                                ->all();
                        });
                    })
                    ->disabled(fn (Get $get): bool => blank($get('estado')))
                    ->searchable()
                    ->required(),

                Forms\Components\Select::make('tipo')
                    ->label('Tipo')
                    ->options([
                        'Residencial' => 'Residencial',
                        'Comercial'   => 'Comercial',
                        'Misto'       => 'Misto',
                        'Industrial'  => 'Industrial',
                    ])
                    ->required(),

                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'Planejamento'  => 'Planejamento',
                        'Em construção' => 'Em construção',
                        'Concluída'     => 'Concluída',
                        'Suspensa'      => 'Suspensa',
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
                    ->modalHeading('Inativar construção')
                    ->modalDescription('A construção fica inativa junto com seus pavimentos, unidades, equipamentos e acabamentos. Todos podem ser reativados depois.')
                    ->modalSubmitActionLabel('Sim, inativar')
                    ->successNotificationTitle('Registro inativado'),
                Tables\Actions\RestoreAction::make()
                    ->label('Reativar')
                    ->modalHeading('Reativar construção')
                    ->modalDescription('A construção volta a ficar ativa, junto com os registros inativados com ela.')
                    ->successNotificationTitle('Construção reativada'),
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
            ->select('construcoes.*')
            ->withoutGlobalScopes([SoftDeletingScope::class])
            ->withSum('pavimentos as total_pavimentos', 'quantidade')
            ->addSelect([
                'total_unidades' => UnidadeAutonoma::query()
                    ->join('pavimentos', 'pavimentos.id', '=', 'unidades_autonomas.pavimento_id')
                    ->whereColumn('pavimentos.construcao_id', 'construcoes.id')
                    ->whereNull('pavimentos.deleted_at')
                    ->whereNull('unidades_autonomas.deleted_at') // CORREÇÃO: Ignorar unidades que sofreram soft delete
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
            'index'  => Pages\ListarConstrucoes::route('/'),
            'create' => Pages\CriarConstrucao::route('/create'),
            'edit'   => Pages\EditarConstrucao::route('/{record}/edit'),
        ];
    }
}
