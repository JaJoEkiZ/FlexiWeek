<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Agregamos la columna sort_order (sin ->after() para SQL Server)
        Schema::table('tasks', function (Blueprint $table) {
            $table->integer('sort_order')->default(0);
        });

        // 2. Actualizamos los registros existentes (Sintaxis compatible con SQL Server)
        DB::table('tasks')->update(['sort_order' => DB::raw('id')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};
