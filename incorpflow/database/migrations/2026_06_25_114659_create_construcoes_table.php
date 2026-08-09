<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('construcoes', function (Blueprint $table) {
            $table->id();
	    $table->string('nome');
	    $table->string('endereco');
            $table->string('cidade');
	    $table->string('estado', 2);
	    $table->enum('tipo', ['Residencial', 'Comercial', 'Misto', 'Industrial']);
	    $table->enum('status', ['Em construção', 'Concluída', 'Planejamento', 'Suspensa'])->default('Planejamento');
	    $table->string('responsavel');
	    $table->text('descricao')->nullable();
	    $table->timestamps();
	    $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('construcoes');
    }
};
