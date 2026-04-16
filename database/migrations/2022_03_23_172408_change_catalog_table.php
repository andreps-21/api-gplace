<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeCatalogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('catalogs', function (Blueprint $table) {
            $table->text('text_email');
            $table->string('url', 100);
            $table->string('image')->nullable();
            $table->dropColumn(['anexo', 'language']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('catalogs', function (Blueprint $table) {
            $table->dropColumn(['text_email', 'url', 'image']);
        });
    }
}
