<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pavimentos', function (Blueprint $table) {
            $table->decimal('area_construida', 10, 2)->nullable()->after('nivel');
        });

        Schema::table('unidades_autonomas', function (Blueprint $table) {
            $table->decimal('area_privativa', 10, 2)->nullable()->after('tipo');
            $table->decimal('area_comum', 10, 2)->nullable()->after('area_privativa');
            $table->decimal('area_equivalente', 10, 2)->nullable()->after('area_comum');
        });
    }

    public function down(): void
    {
        Schema::table('pavimentos', function (Blueprint $table) {
            $table->dropColumn('area_construida');
        });

        Schema::table('unidades_autonomas', function (Blueprint $table) {
            $table->dropColumn(['area_privativa', 'area_comum', 'area_equivalente']);
        });
    }
};
