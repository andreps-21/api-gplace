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
        Schema::table('settings_erps', function (Blueprint $table) {
            $table->string('terminal')->nullable();
            $table->string('id_emp')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('settings_erps', function (Blueprint $table) {
            $table->dropColumn('terminal');
            $table->dropColumn('id_emp');
        });
    }
};
