<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHomeBlockItemsTable extends Migration
{
    public function up()
    {
        Schema::create('home_block_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('home_block_id')->constrained('home_blocks')->onDelete('cascade');
            $table->unsignedBigInteger('item_id');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['home_block_id', 'item_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('home_block_items');
    }
}
