<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('construcao_id')->constrained('construcoes')->cascadeOnDelete();
            $table->string('nome');
            $table->text('tipo_ou_marca')->nullable();
            $table->string('acabamento')->nullable();
            $table->text('detalhes_gerais')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipamentos');
    }
};
