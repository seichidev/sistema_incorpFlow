<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EquipamentoResource\Pages;
use App\Models\Construcao;
use App\Models\Equipamento;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EquipamentoResource extends Resource
{
    protected static ?string $model = Equipamento::class;

    protected static ?string $slug = 'equipamentos';
    protected static ?string $navigationLabel = 'Equipamentos';
    protected static ?string $modelLabel = 'Equipamento';
    protected static ?string $pluralModelLabel = 'Equipamentos';
    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('construcao_id')
                    ->label('Construção')
                    ->options(fn (): array => Construcao::orderBy('nome')->pluck('nome', 'id')->all())
                    ->searchable()
                    ->placeholder('Sem construção')
                    ->helperText('Opcional: o equipamento pode ser cadastrado antes da construção e vinculado depois.'),

                ...static::camposDoEquipamento(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('construcao.nome')
                    ->label('Construção')
                    ->placeholder('Sem construção')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('nome')
                    ->label('Equipamento')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('acabamento')
                    ->label('Acabamento')
                    ->default('-'),

                Tables\Columns\TextColumn::make('detalhes_gerais')
                    ->label('Detalhes')
                    ->limit(60)
                    ->default('-'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('construcao_id')
                    ->label('Vínculo')
                    ->nullable()
                    ->placeholder('Todos')
                    ->trueLabel('Com construção')
                    ->falseLabel('Sem construção'),

                Tables\Filters\SelectFilter::make('construcao')
                    ->label('Construção')
                    ->relationship('construcao', 'nome'),

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
                    ->modalHeading('Inativar equipamento')
                    ->modalDescription('O equipamento fica inativo e pode ser reativado depois.')
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
            // withTrashed: equipamento de construção inativa continua mostrando o
            // nome dela; "Sem construção" fica só para quem não tem vínculo.
            ->with(['construcao' => fn ($query) => $query->withTrashed()]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListarEquipamentos::route('/'),
            'create' => Pages\CriarEquipamento::route('/create'),
            'edit'   => Pages\EditarEquipamento::route('/{record}/edit'),
        ];
    }

    /**
     * Campos do equipamento, também usados no formulário dentro da construção.
     * Sugerem o que já foi cadastrado e, ao criar, preenchem os campos vazios a
     * partir do último equipamento de mesmo nome.
     */
    public static function camposDoEquipamento(): array
    {
        return [
            Forms\Components\TextInput::make('nome')
                ->label('Equipamento')
                ->required()
                ->maxLength(255)
                ->datalist(fn (): array => Equipamento::sugestoesDeNome())
                ->live(onBlur: true)
                ->afterStateUpdated(fn (?string $state, Get $get, Set $set, string $operation) => static::preencherPeloNome($state, $get, $set, $operation)),

            Forms\Components\TextInput::make('tipo_ou_marca')
                ->label('Tipo / Marca')
                ->datalist(fn (Get $get): array => Equipamento::valoresUsados('tipo_ou_marca', $get('nome'))),

            Forms\Components\TextInput::make('acabamento')
                ->label('Acabamento')
                ->maxLength(255)
                ->datalist(fn (Get $get): array => Equipamento::valoresUsados('acabamento', $get('nome'))),

            Forms\Components\Textarea::make('detalhes_gerais')
                ->label('Detalhes Gerais')
                ->rows(3)
                ->columnSpanFull(),
        ];
    }

    private static function preencherPeloNome(?string $nome, Get $get, Set $set, string $operation): void
    {
        // Só ao criar: na edição, trocar o nome não deve mexer no que já foi descrito.
        if ($operation !== 'create' || ! ($referencia = Equipamento::referenciaPara($nome))) {
            return;
        }

        $preenchidos = [];

        foreach (['tipo_ou_marca' => 'Tipo / Marca', 'acabamento' => 'Acabamento', 'detalhes_gerais' => 'Detalhes Gerais'] as $campo => $rotulo) {
            // Nunca sobrescreve o que já foi digitado.
            if (blank($get($campo)) && filled($referencia->{$campo})) {
                $set($campo, $referencia->{$campo});
                $preenchidos[] = $rotulo;
            }
        }

        if ($preenchidos === []) {
            return;
        }

        $origem = $referencia->construcao()->withTrashed()->value('nome');

        Notification::make()
            ->title('Preenchido a partir de um equipamento já cadastrado')
            ->body(implode(', ', $preenchidos) . ' vieram de "' . $referencia->nome . '"'
                . ($origem ? " ({$origem})" : '') . '. Revise antes de salvar.')
            ->info()
            ->send();
    }
}
