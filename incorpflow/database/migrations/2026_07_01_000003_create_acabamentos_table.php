<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acabamentos', function (Blueprint $table) {
            $table->id();

            // Vínculo polimórfico: um acabamento pertence a uma construção
            // (área de uso comum) ou a uma unidade autônoma (área privativa).
            // Gera acabavel_type, acabavel_id e o índice do par.
            $table->morphs('acabavel');

            $table->enum('escopo', ['Privativo', 'Comum']);

            // Campos de texto livre: no Quadro VI do empreendimento de referência
            // há células com mais de 500 caracteres.
            $table->text('dependencia');
            $table->text('piso_revestimento')->nullable();
            $table->text('piso_acabamento')->nullable();
            $table->text('piso_soleira')->nullable();
            $table->text('parede_revestimento')->nullable();
            $table->text('parede_acabamento')->nullable();
            $table->text('parede_rodape')->nullable();
            $table->text('teto_revestimento')->nullable();
            $table->text('teto_acabamento')->nullable();
            $table->text('peitoris')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acabamentos');
    }
};
