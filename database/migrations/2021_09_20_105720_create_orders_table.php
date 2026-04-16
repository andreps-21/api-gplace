<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{

    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('code', 12);
            $table->tinyInteger('status');
            $table->bigInteger('customer_id')->unsigned();
            $table->foreign('customer_id')->references('id')->on('customers');
            $table->decimal('vl_amount', 12, 2);
            $table->decimal('vl_icms', 12, 2);
            $table->decimal('vl_ipi', 12, 2);
            $table->decimal('vl_freight', 11,2);
            $table->decimal('vl_discount', 11,2);
            $table->decimal('total', 11,2);
            $table->bigInteger('payment_method_id')->unsigned();
            $table->foreign('payment_method_id')->references('id')->on('payment_methods')->onDelete('cascade');
            $table->string('code_payment')->nullable();
            $table->string('delivery_place', 120);
            $table->string('description', 60)->nullable();
            $table->timestamp('sync_at')->nullable();
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
        Schema::dropIfExists('orders');
    }
}
