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
            $table->string('numero');
            $table->string('tipo');
            $table->string('torre')->nullable();
            $table->boolean('permutada')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unidades_autonomas');
    }
};
