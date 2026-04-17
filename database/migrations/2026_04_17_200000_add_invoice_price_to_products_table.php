<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Valor unitário de referência na nota fiscal (compra / custo documentado), distinto do preço de venda.
     */
    public function up(): void
    {
        if (Schema::hasColumn('products', 'invoice_price')) {
            return;
        }
        Schema::table('products', function (Blueprint $table) {
            $table->double('invoice_price', 12, 2)->nullable()->after('promotion_price');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('products', 'invoice_price')) {
            return;
        }
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('invoice_price');
        });
    }
};
