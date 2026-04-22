<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('products', 'cest')) {
            return;
        }
        Schema::table('products', function (Blueprint $table) {
            $table->string('cest', 20)->nullable()->change();
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('products', 'cest')) {
            return;
        }
        Schema::table('products', function (Blueprint $table) {
            $table->string('cest', 7)->nullable()->change();
        });
    }
};
