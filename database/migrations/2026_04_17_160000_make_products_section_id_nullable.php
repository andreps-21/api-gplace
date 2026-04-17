<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Secção principal opcional no cadastro de produto (SPA / API).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['section_id']);
        });

        DB::statement('ALTER TABLE products MODIFY section_id BIGINT UNSIGNED NULL');

        Schema::table('products', function (Blueprint $table) {
            $table->foreign('section_id')->references('id')->on('sections');
        });
    }

    public function down(): void
    {
        if (DB::table('products')->whereNull('section_id')->exists()) {
            throw new \RuntimeException(
                'Não é possível reverter: existem produtos sem section_id. Atribua uma secção antes de reverter esta migração.'
            );
        }

        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['section_id']);
        });

        DB::statement('ALTER TABLE products MODIFY section_id BIGINT UNSIGNED NOT NULL');

        Schema::table('products', function (Blueprint $table) {
            $table->foreign('section_id')->references('id')->on('sections');
        });
    }
};
