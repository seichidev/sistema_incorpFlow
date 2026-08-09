<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const CONSTRAINT = 'unidades_autonomas_tipo_check';

    /**
     * Tipos de unidade autônoma conforme os Quadros II e IV-A da NBR 12.721.
     * Áreas comuns (hall, escada, lazer) não são unidades autônomas — elas
     * compõem as áreas de uso comum rateadas entre as unidades.
     */
    private const TIPOS = [
        'Apartamento',
        'Vaga de Garagem',
        'Sala Comercial',
        'Loja',
        'Box',
    ];

    public function up(): void
    {
        // Normaliza valores livres existentes que não pertencem à lista de tipos
        DB::table('unidades_autonomas')
            ->whereNotIn('tipo', self::TIPOS)
            ->update(['tipo' => 'Apartamento']);

        // No PostgreSQL o enum do Laravel equivale a varchar + CHECK
        $lista = collect(self::TIPOS)
            ->map(fn (string $t) => "'" . str_replace("'", "''", $t) . "'")
            ->implode(', ');

        DB::statement(
            'ALTER TABLE unidades_autonomas ADD CONSTRAINT ' . self::CONSTRAINT
            . ' CHECK (tipo IN (' . $lista . '))'
        );
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE unidades_autonomas DROP CONSTRAINT IF EXISTS ' . self::CONSTRAINT);
    }
};
