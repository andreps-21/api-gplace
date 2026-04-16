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
        Schema::table('banner_size_image', function (Blueprint $table) {
            $table->foreignId('interface_position_id')->nullable()
                ->constrained('interface_positions')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('banner_size_image', function (Blueprint $table) {
            $table->dropForeign(['interface_position_id']);
            $table->dropColumn('interface_position_id');
        });
    }
};
