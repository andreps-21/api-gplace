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
        Schema::create('size_images', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('size_width');
            $table->integer('size_height');
            $table->boolean('is_enabled');
            $table->tinyInteger('type')->default(1);
            $table->string('code')->nullable();
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
        Schema::dropIfExists('size_images');
    }
};
