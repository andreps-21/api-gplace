<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('interface_position_size_images', function (Blueprint $table) {
            $table->foreignId('interface_position_id')->constrained('interface_positions')->onDelete('cascade');
            $table->foreignId('size_image_id')->constrained('size_images')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('interface_position_size_images');
    }
};
