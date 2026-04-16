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
        Schema::table('interface_position_size_images', function (Blueprint $table) {
            $table->dropForeign(['interface_position_id']);
            $table->foreign('interface_position_id')->references('id')->on('interface_positions')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('interface_position_size_images', function (Blueprint $table) {
            //
        });
    }
};


?>