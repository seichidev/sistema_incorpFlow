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
            $table->morphs('acabavel');
            $table->enum('escopo', ['Privativo', 'Comum']);
            $table->string('dependencia');
            $table->string('piso_revestimento')->nullable();
            $table->string('piso_acabamento')->nullable();
            $table->string('piso_soleira')->nullable();
            $table->string('parede_revestimento')->nullable();
            $table->string('parede_acabamento')->nullable();
            $table->string('parede_rodape')->nullable();
            $table->string('teto_revestimento')->nullable();
            $table->string('teto_acabamento')->nullable();
            $table->string('peitoris')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acabamentos');
    }
};
