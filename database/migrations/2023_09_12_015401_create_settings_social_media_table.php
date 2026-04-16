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
        Schema::create('settings_social_media', function (Blueprint $table) {
            $table->foreignId('settings_id')->constrained('settings');
            $table->foreignId('social_media_id')->constrained('social_media');
            $table->string('user')->nullable();
            $table->string('password')->nullable();
            $table->string('token')->nullable();
            $table->string('url')->nullable();
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
        Schema::dropIfExists('settings_social_media');
    }
};
