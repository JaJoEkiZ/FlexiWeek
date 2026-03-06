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
        Schema::create('board_item_subtask', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('board_item_id');
            $table->string('title');
            $table->boolean('is_completed')->default(false);
            $table->timestamps();

            $table->foreign('board_item_id')->references('id')->on('board_items');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('board_item_subtask');
    }
};
