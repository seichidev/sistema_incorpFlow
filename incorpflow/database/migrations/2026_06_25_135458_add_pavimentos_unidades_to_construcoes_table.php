<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('construcoes', function (Blueprint $table) {
            $table->integer('pavimentos')->default(0);
            $table->integer('unidades')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('construcoes', function (Blueprint $table) {
            $table->dropColumn(['pavimentos', 'unidades']);
        });
    }
};
