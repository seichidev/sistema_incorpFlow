<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Amplia unidades_autonomas.numero para text.
 *
 * Os Quadros da NBR 12.721 agrupam as unidades por tipologia e listam os
 * números de todas as unidades do grupo em um único bloco. No Quadro IV-B do
 * Malibu o Apartamento Tipo F reúne 112 unidades, cuja lista ocupa 598
 * caracteres — acima do varchar(255) original.
 *
 * No PostgreSQL text e varchar têm o mesmo desempenho, então não há custo
 * em remover o limite.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('unidades_autonomas', function (Blueprint $table) {
            $table->text('numero')->change();
        });
    }

    /**
     * O rollback falha se alguma unidade já tiver uma lista acima de 255
     * caracteres — comportamento esperado ao estreitar uma coluna.
     */
    public function down(): void
    {
        Schema::table('unidades_autonomas', function (Blueprint $table) {
            $table->string('numero')->change();
        });
    }
};
