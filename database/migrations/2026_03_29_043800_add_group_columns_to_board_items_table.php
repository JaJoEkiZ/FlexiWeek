<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

    return new class extends Migration
    {
    public function up(): void
    {
        Schema::table('board_items', function (Blueprint $table) {
            $table->boolean('is_group')->default(false)->after('color');
            // 'parent_id' es nulo por defecto. NullOnDelete hace que si borramos un grupo bruscamente, sus hijos queden "sueltos".
            $table->foreignId('parent_id')->nullable()->after('is_group')->constrained('board_items')->nullOnDelete();
        });
    }
    public function down(): void
    {
        Schema::table('board_items', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['parent_id', 'is_group']);
        });
    }
};
