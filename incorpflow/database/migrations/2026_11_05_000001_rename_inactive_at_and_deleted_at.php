<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Nomes das colunas em inglês:
     *   deleted_at  → inactive_at  (inativado: o SoftDeletes, reativável)
     *   excluido_em → deleted_at   (excluído definitivamente, sem apagar a linha)
     *
     * Só renomeia: os valores já gravados continuam nas mesmas linhas.
     */
    private array $tabelas = [
        'construcoes',
        'pavimentos',
        'unidades_autonomas',
        'equipamentos',
        'acabamentos',
    ];

    public function up(): void
    {
        foreach ($this->tabelas as $tabela) {
            // Nessa ordem: deleted_at precisa sair antes de excluido_em ocupar o nome.
            Schema::table($tabela, fn (Blueprint $table) => $table->renameColumn('deleted_at', 'inactive_at'));
            Schema::table($tabela, function (Blueprint $table) use ($tabela) {
                $table->renameColumn('excluido_em', 'deleted_at');
                $table->renameIndex("{$tabela}_excluido_em_index", "{$tabela}_deleted_at_index");
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tabelas as $tabela) {
            Schema::table($tabela, function (Blueprint $table) use ($tabela) {
                $table->renameIndex("{$tabela}_deleted_at_index", "{$tabela}_excluido_em_index");
                $table->renameColumn('deleted_at', 'excluido_em');
            });
            Schema::table($tabela, fn (Blueprint $table) => $table->renameColumn('inactive_at', 'deleted_at'));
        }
    }
};
