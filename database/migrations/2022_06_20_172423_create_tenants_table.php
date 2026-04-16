<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->constrained('people')->onDelete('cascade');
            $table->string('contact')->nullable();
            $table->string('contact_phone', 15)->nullable();
            $table->string('cellphone', 15)->nullable();
            $table->date('dt_accession');
            $table->decimal('value', 12, 2);
            $table->tinyInteger('signature');
            $table->tinyInteger('status');
            $table->tinyInteger('due_day');
            $table->string('due_date');
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
        Schema::dropIfExists('tenants');
    }
}

