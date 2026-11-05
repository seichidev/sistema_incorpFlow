<?php

namespace App\Filament\Resources\ConstrucaoResource\RelationManagers;

use App\Filament\Resources\EquipamentoResource;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EquipamentosRelationManager extends RelationManager
{
    protected static string $relationship = 'equipamentos';
    protected static ?string $title = 'Equipamentos';

    public function form(Form $form): Form
    {
        return $form
            ->schema(EquipamentoResource::camposDoEquipamento());
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScopes([SoftDeletingScope::class]))
            ->columns([
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
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Criar Equipamento'),
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
}
