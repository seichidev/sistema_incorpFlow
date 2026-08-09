<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ajusta o schema aos valores reais dos Quadros da NBR 12.721.
 *
 * Acabamento: no Quadro VIII a coluna DEPENDÊNCIAS chega a 365 caracteres,
 * acima do varchar(255). Os demais campos descritivos chegam a 210. Como no
 * PostgreSQL text e varchar têm o mesmo desempenho, todos passam a text.
 *
 * Áreas: os Quadros I e II usam 4 casas decimais (ex.: 71.6917). Com
 * decimal(10,2) o valor era truncado e a soma das unidades não fechava
 * com a área global.
 */
return new class extends Migration
{
    private const CAMPOS_ACABAMENTO = [
        'dependencia',
        'piso_revestimento',
        'piso_acabamento',
        'piso_soleira',
        'parede_revestimento',
        'parede_acabamento',
        'parede_rodape',
        'teto_revestimento',
        'teto_acabamento',
        'peitoris',
    ];

    public function up(): void
    {
        Schema::table('acabamentos', function (Blueprint $table) {
            foreach (self::CAMPOS_ACABAMENTO as $campo) {
                $nullable = $campo !== 'dependencia';
                $table->text($campo)->nullable($nullable)->change();
            }
        });

        Schema::table('pavimentos', function (Blueprint $table) {
            $table->decimal('area_construida', 12, 4)->nullable()->change();
        });

        Schema::table('unidades_autonomas', function (Blueprint $table) {
            $table->decimal('area_privativa', 12, 4)->nullable()->change();
            $table->decimal('area_comum', 12, 4)->nullable()->change();
            $table->decimal('area_equivalente', 12, 4)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('acabamentos', function (Blueprint $table) {
            foreach (self::CAMPOS_ACABAMENTO as $campo) {
                $nullable = $campo !== 'dependencia';
                $table->string($campo)->nullable($nullable)->change();
            }
        });

        Schema::table('pavimentos', function (Blueprint $table) {
            $table->decimal('area_construida', 10, 2)->nullable()->change();
        });

        Schema::table('unidades_autonomas', function (Blueprint $table) {
            $table->decimal('area_privativa', 10, 2)->nullable()->change();
            $table->decimal('area_comum', 10, 2)->nullable()->change();
            $table->decimal('area_equivalente', 10, 2)->nullable()->change();
        });
    }
};
