<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * "Excluir definitivamente" não apaga a linha: preenche `excluido_em` e o
     * registro some da interface, mas continua no banco para consulta.
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
            Schema::table($tabela, function (Blueprint $table) {
                $table->timestamp('excluido_em')->nullable()->index();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tabelas as $tabela) {
            Schema::table($tabela, function (Blueprint $table) {
                $table->dropIndex(['excluido_em']);
                $table->dropColumn('excluido_em');
            });
        }
    }
};
