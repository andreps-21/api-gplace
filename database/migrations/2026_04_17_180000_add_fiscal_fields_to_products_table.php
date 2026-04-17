<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Campos de referência para linha da NF-e (cadastro mestre; valores de operação ficam no documento).
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('ncm', 8)->nullable()->after('origin');
            $table->string('cest', 7)->nullable()->after('ncm');
            $table->string('cfop_default', 4)->nullable()->after('cest');
            $table->string('csosn_default', 3)->nullable()->after('cfop_default');
            $table->string('cst_icms_default', 2)->nullable()->after('csosn_default');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'ncm',
                'cest',
                'cfop_default',
                'csosn_default',
                'cst_icms_default',
            ]);
        });
    }
};
