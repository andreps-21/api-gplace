<?php

use App\Models\PaymentMethod;
use App\Models\Store;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Garante formas de pagamento para balcão / venda rápida (Crédito, Débito, PIX, Dinheiro)
     * e liga-as a todas as lojas existentes.
     */
    public function up(): void
    {
        $definicoes = [
            ['code' => PaymentMethod::CREDIT_CARD, 'description' => 'Crédito'],
            ['code' => PaymentMethod::DEBIT_CARD, 'description' => 'Débito'],
            ['code' => PaymentMethod::PIX, 'description' => 'PIX'],
            ['code' => PaymentMethod::CASH, 'description' => 'Dinheiro'],
        ];

        $ids = [];
        foreach ($definicoes as $row) {
            $pm = PaymentMethod::query()->updateOrCreate(
                ['code' => $row['code']],
                [
                    'description' => $row['description'],
                    'is_enabled' => true,
                ],
            );
            $ids[] = $pm->id;
        }

        if ($ids === []) {
            return;
        }

        Store::query()->chunkById(100, function ($stores) use ($ids) {
            foreach ($stores as $store) {
                $store->paymentMethods()->syncWithoutDetaching($ids);
            }
        });
    }
};
