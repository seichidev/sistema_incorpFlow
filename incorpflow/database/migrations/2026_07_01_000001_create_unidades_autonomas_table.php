<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unidades_autonomas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pavimento_id')->constrained('pavimentos')->cascadeOnDelete();

            // Uma ocorrência representa uma tipologia, não uma unidade: `numero`
            // guarda a relação de identificadores e `quantidade` diz quantos são.
            $table->text('numero');

            $table->enum('tipo', [
                'Apartamento',
                'Vaga de Garagem',
                'Sala Comercial',
                'Loja',
                'Box',
            ]);
            $table->string('tipologia', 100)->nullable();
            $table->boolean('permutada')->default(false);
            $table->integer('quantidade')->default(1);

            // Áreas do Quadro II — precisão exata, quatro casas decimais.
            $table->decimal('area_privativa', 12, 4)->nullable();
            $table->decimal('area_comum', 12, 4)->nullable();
            $table->decimal('area_equivalente', 12, 4)->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('pavimento_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unidades_autonomas');
    }
};
