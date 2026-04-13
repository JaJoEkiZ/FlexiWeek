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
        Schema::table('tasks', function (Blueprint $table) {
            $table->decimal('estimated_minutes', 10, 2)->default(0)->change();
        });

        Schema::table('subtasks', function (Blueprint $table) {
            $table->decimal('estimated_minutes', 10, 2)->default(0)->change();
            $table->decimal('spent_minutes', 10, 2)->default(0)->change();
        });

        Schema::table('task_time_logs', function (Blueprint $table) {
            $table->decimal('minutes_spent', 10, 2)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->integer('estimated_minutes')->default(0)->change();
        });

        Schema::table('subtasks', function (Blueprint $table) {
            $table->integer('estimated_minutes')->default(0)->change();
            $table->integer('spent_minutes')->default(0)->change();
        });

        Schema::table('task_time_logs', function (Blueprint $table) {
            $table->integer('minutes_spent')->change();
        });
    }
};
