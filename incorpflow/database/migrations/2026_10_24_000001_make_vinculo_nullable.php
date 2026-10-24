<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Equipamentos e acabamentos podem ser cadastrados antes da construção ou
     * da unidade autônoma a que pertencem, e vinculados depois.
     *
     * A chave estrangeira de equipamentos continua valendo: o vínculo pode
     * faltar, mas não pode apontar para uma construção inexistente.
     */
    public function up(): void
    {
        Schema::table('equipamentos', function (Blueprint $table) {
            $table->unsignedBigInteger('construcao_id')->nullable()->change();
        });

        Schema::table('acabamentos', function (Blueprint $table) {
            $table->string('acabavel_type')->nullable()->change();
            $table->unsignedBigInteger('acabavel_id')->nullable()->change();
        });

        // O par polimórfico é tudo ou nada: um tipo sem id (ou um id sem tipo)
        // seria um vínculo que não aponta para lugar nenhum.
        DB::statement('ALTER TABLE acabamentos ADD CONSTRAINT acabamentos_vinculo_completo
            CHECK ((acabavel_type IS NULL) = (acabavel_id IS NULL))');
    }

    public function down(): void
    {
        // Voltar a NOT NULL falharia com registros sem vínculo na tabela —
        // inclusive os inativos e os excluídos, que continuam no banco.
        $semVinculo = DB::table('equipamentos')->whereNull('construcao_id')->count()
            + DB::table('acabamentos')->whereNull('acabavel_id')->count();

        if ($semVinculo > 0) {
            throw new RuntimeException(
                "Há {$semVinculo} equipamento(s) ou acabamento(s) sem vínculo. "
                . 'Vincule ou apague esses registros no banco antes de reverter esta migration.'
            );
        }

        DB::statement('ALTER TABLE acabamentos DROP CONSTRAINT acabamentos_vinculo_completo');

        Schema::table('acabamentos', function (Blueprint $table) {
            $table->string('acabavel_type')->nullable(false)->change();
            $table->unsignedBigInteger('acabavel_id')->nullable(false)->change();
        });

        Schema::table('equipamentos', function (Blueprint $table) {
            $table->unsignedBigInteger('construcao_id')->nullable(false)->change();
        });
    }
};
