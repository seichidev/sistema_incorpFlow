<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cria índices nas chaves estrangeiras.
 *
 * No PostgreSQL, ao contrário do MySQL, uma FOREIGN KEY não gera índice na
 * coluna filha. Como `foreignId()->constrained()` só cria a constraint, todas
 * as consultas por construcao_id / pavimento_id vinham fazendo varredura
 * sequencial — o mesmo valendo para o CASCADE ao apagar um pai.
 *
 * Afeta os filtros por construção, os RelationManagers e o subquery de
 * total_unidades em ConstrucaoResource.
 */
return new class extends Migration
{
    private const INDICES = [
        'pavimentos'         => 'construcao_id',
        'unidades_autonomas' => 'pavimento_id',
        'equipamentos'       => 'construcao_id',
    ];

    public function up(): void
    {
        foreach (self::INDICES as $tabela => $coluna) {
            Schema::table($tabela, function (Blueprint $table) use ($coluna) {
                $table->index($coluna);
            });
        }
    }

    public function down(): void
    {
        foreach (self::INDICES as $tabela => $coluna) {
            Schema::table($tabela, function (Blueprint $table) use ($tabela, $coluna) {
                $table->dropIndex($tabela . '_' . $coluna . '_index');
            });
        }
    }
};
