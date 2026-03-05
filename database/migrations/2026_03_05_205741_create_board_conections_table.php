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
        Schema::create('board_conections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('from_item_id');
            $table->unsignedBigInteger('to_item_id');
            $table->string('type')->default('depends_start'); // 'depends_start' o 'depends_end'
            $table->timestamps();

            $table->foreign('from_item_id')->references('id')->on('board_items');
            $table->foreign('to_item_id')->references('id')->on('board_items');
        });
    }

    /** 
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('board_conections');
    }
};
