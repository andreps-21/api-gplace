<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProvidersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('providers', function (Blueprint $table) {
            $table->id();
            $table->string('state_registration')->nullable();
            $table->string('municipal_registration')->nullable();
            $table->tinyInteger('type');
            $table->string('contact')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->tinyInteger('status');

            $table->boolean('own_equipment');
            $table->boolean('own_transport');
            $table->date('birth_date')->nullable();
            $table->text('notes')->nullable();

            $table->bigInteger('profession_id')->unsigned();
            $table->foreign('profession_id')->references('id')->on('professions');

            $table->bigInteger('person_id')->unsigned();
            $table->foreign('person_id')->references('id')->on('people');

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
        Schema::dropIfExists('providers');
    }
}
