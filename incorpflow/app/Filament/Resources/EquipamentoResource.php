<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EquipamentoResource\Pages;
use App\Models\Construcao;
use App\Models\Equipamento;
use Filament\Forms;
use Filament\Forms\Form;
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
                    ->required(),

                Forms\Components\TextInput::make('nome')
                    ->label('Equipamento')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Textarea::make('tipo_ou_marca')
                    ->label('Tipo / Marca')
                    ->rows(3),

                Forms\Components\TextInput::make('acabamento')
                    ->label('Acabamento')
                    ->maxLength(255),

                Forms\Components\Textarea::make('detalhes_gerais')
                    ->label('Detalhes Gerais')
                    ->rows(3)
                    ->columnSpanFull(),
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
            ->with('construcao');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListarEquipamentos::route('/'),
            'create' => Pages\CriarEquipamento::route('/create'),
            'edit'   => Pages\EditarEquipamento::route('/{record}/edit'),
        ];
    }
}
