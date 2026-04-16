<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductProvidersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_providers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained();
            $table->foreignId('product_id')->constrained();
            $table->string('um');
            $table->decimal('price', 12, 2);
            $table->decimal('vl_km', 12, 2);
            $table->decimal('vl_transfer', 12, 2);
            $table->boolean('is_enabled');
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
        Schema::dropIfExists('product_providers');
    }
}
