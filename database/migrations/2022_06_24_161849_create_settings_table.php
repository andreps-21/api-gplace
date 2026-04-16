<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('logo')->nullable();
            $table->string('nif');
            $table->string('full_name');
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->string('maps')->nullable();
            $table->string('contact')->nullable();
            $table->foreignId('city_id')->constrained();
            $table->string('zip_code');
            $table->string('address');
            $table->string('district')->nullable();
            $table->string('number')->nullable();

            $table->string('instagram_url')->nullable();
            $table->string('instagram_user')->nullable();
            $table->string('instagram_password')->nullable();

            $table->string('facebook_url')->nullable();
            $table->string('facebook_user')->nullable();
            $table->string('facebook_password')->nullable();

            $table->string('youtube_url')->nullable();
            $table->string('youtube_user')->nullable();
            $table->string('youtube_password')->nullable();

            $table->string('twitter_url')->nullable();
            $table->string('twitter_user')->nullable();
            $table->string('twitter_password')->nullable();

            $table->char('status', 1);
            $table->text('pixels')->nullable();
            $table->text('ads')->nullable();
            $table->text('meta_tags')->nullable();
            $table->text('footer')->nullable();

            $table->string('terms')->nullable();
            $table->string('privacy_policy')->nullable();
            $table->string('note')->nullable();
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
        Schema::dropIfExists('settings');
    }
}
