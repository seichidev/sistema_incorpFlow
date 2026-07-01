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
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pavimentos');
    }
};
