<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouponsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_unit_id')->nullable()->constrained();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->boolean('is_enabled');
            $table->char('sponsor', 1)->nullable();
            $table->char('apply', 1);
            $table->decimal('discount', 12, 2);
            $table->decimal('min_order', 12, 2);
            $table->smallInteger('quantity');
            $table->smallInteger('balance');
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
        Schema::dropIfExists('coupons');
    }
}
