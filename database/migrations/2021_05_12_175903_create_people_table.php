<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePeopleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('people', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('formal_name')->nullable();
            $table->string('nif')->unique()->nullable();
            $table->string('email');
            $table->string('street')->nullable();
            $table->string('number')->nullable();
            $table->string('zip_code')->nullable();
            $table->foreignId('city_id')->nullable()->constrained();
            $table->string('phone')->nullable();
            $table->string('district')->nullable();
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
        Schema::dropIfExists('people');
    }
}
