<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAccountToProvidersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('providers', function (Blueprint $table) {
            $table->foreignId('bank_id')->nullable()->constrained();
            $table->string('agency', 13)->nullable();
            $table->string('account', 18)->nullable();
            $table->char('account_type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('providers', function (Blueprint $table) {
            $table->dropForeign(['bank_id']);
            $table->dropColumn(['bank_id', 'agency', 'account', 'account_type']);
        });
    }
}
