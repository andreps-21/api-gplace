<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('video')->nullable();
            $table->string('reference');
            $table->tinyInteger('origin')->default(1);
            $table->string('commercial_name');
            $table->string('description')->nullable();

            $table->bigInteger('um_id')->unsigned();
            $table->foreign('um_id')->references('id')->on('measurement_units');



            $table->string('tag')->nullable();
            $table->double('price', 12, 2);
            $table->double('promotion_price', 12, 2);
            $table->decimal('discount', 12, 2)->nullable();
            $table->decimal('spots', 12, 2)->nullable();
            $table->decimal('scores', 12, 2)->nullable();
            $table->string('payment_condition')->nullable();


            $table->decimal('weight', 12, 2)->nullable();
            $table->decimal('cubic_weight', 12, 2)->nullable();

            $table->bigInteger('brand_id')->nullable()->unsigned();
            $table->foreign('brand_id')->references('id')->on('brands');

            $table->bigInteger('presentation_id')->nullable()->unsigned();
            $table->foreign('presentation_id')->references('id')->on('presentations');

            $table->bigInteger('family_id')->nullable()->unsigned();
            $table->foreign('family_id')->references('id')->on('families');

            $table->text('about')->nullable();
            $table->text('recommendation')->nullable();
            $table->text('benefits')->nullable();
            $table->text('formula')->nullable();
            $table->text('application_mode')->nullable();
            $table->text('dosage')->nullable();
            $table->text('lack')->nullable();
            $table->text('other_information')->nullable();
            $table->boolean('is_enabled');
            $table->timestamp('sync_at')->nullable();
            $table->tinyInteger('rating')->default(0);
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
        Schema::dropIfExists('products');
    }
}
