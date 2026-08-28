<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pavimentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('construcao_id')->constrained('construcoes')->cascadeOnDelete();
            $table->string('nome');
            $table->string('torre')->nullable();
            $table->enum('tipo', [
                'Inferior',
                'Terreo',
                'Superior',
                'Lazer',
                'Tipo',
                'Barrilete',
                'CaixaDAgua',
            ]);
            $table->decimal('nivel', 8, 2)->nullable();
            $table->integer('quantidade')->default(1);

            // Área de construção do pavimento — Quadro I da NBR 12.721.
            // Precisão exata: ponto flutuante binário não representa 71,6917 sem erro.
            $table->decimal('area_construida', 12, 4)->nullable();

            $table->timestamps();
            $table->softDeletes();

            // O PostgreSQL não cria índice para chave estrangeira automaticamente.
            $table->index('construcao_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pavimentos');
    }
};
