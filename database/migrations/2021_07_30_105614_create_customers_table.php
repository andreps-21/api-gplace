<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('person_id')->unsigned();
            $table->string('state_registration')->nullable();
            $table->string('municipal_registration')->nullable();
            $table->tinyInteger('origin');
            $table->date('birth_date')->nullable();
            $table->tinyInteger('type')->default(1);
            $table->tinyInteger('status');
            $table->text('notes')->nullable();

            $table->string('contact')->nullable();
            $table->string('contact_phone', 15)->nullable();
            $table->string('contact_email')->nullable();
            $table->timestamp('sync_at')->nullable();



            $table->foreign('person_id')->references('id')->on('people')->onDelete('cascade');
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
        Schema::dropIfExists('customers');
    }
}
