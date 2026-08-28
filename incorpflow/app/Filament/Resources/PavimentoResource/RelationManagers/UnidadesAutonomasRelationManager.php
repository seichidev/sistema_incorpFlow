<?php

namespace App\Filament\Resources\PavimentoResource\RelationManagers;

use App\Models\UnidadeAutonoma;
use App\Rules\NumerosSemDuplicidade;
use App\Rules\QuantidadeCorrespondeAosNumeros;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UnidadesAutonomasRelationManager extends RelationManager
{
    protected static string $relationship = 'unidadesAutonomas';
    protected static ?string $title = 'Unidades Autônomas';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
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

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScopes([SoftDeletingScope::class]))
            ->columns([
                Tables\Columns\TextColumn::make('numero')
                    ->label('Número(s)')
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->numero)
                    ->searchable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('tipo')
                    ->label('Tipo')
                    ->searchable()
                    ->badge(),

                Tables\Columns\TextColumn::make('tipologia')
                    ->label('Tipologia')
                    ->default('-')
                    ->searchable(),

                Tables\Columns\TextColumn::make('area_privativa')
                    ->label('Á. Privativa (m²)')
                    ->numeric(4)
                    ->default('-'),

                Tables\Columns\TextColumn::make('area_equivalente')
                    ->label('Á. Equivalente (m²)')
                    ->numeric(4)
                    ->default('-'),

                Tables\Columns\TextColumn::make('quantidade')
                    ->label('Qtd.')
                    ->badge()
                    ->color(fn (UnidadeAutonoma $record): string => $record->quantidade_confere ? 'success' : 'danger')
                    ->icon(fn (UnidadeAutonoma $record): ?string => $record->quantidade_confere ? null : 'heroicon-m-exclamation-triangle')
                    ->tooltip(fn (UnidadeAutonoma $record): ?string => $record->quantidade_confere
                        ? null
                        : "A lista de números tem {$record->quantidade_listada} unidade(s)")
                    ->sortable(),

                Tables\Columns\IconColumn::make('permutada')
                    ->label('Permutada')
                    ->boolean(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Nova Unidade'),
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
}
