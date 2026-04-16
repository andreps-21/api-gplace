<?php

namespace App\Services\Stock;

use App\Models\StockMovement;

class StockMovementService
{
    public function record(
        int $storeId,
        int $productId,
        int $quantityDelta,
        int $balanceAfter,
        string $movementType,
        ?int $userId = null,
        ?int $orderId = null,
        ?int $stockLotId = null,
        ?string $note = null
    ): StockMovement {
        return StockMovement::query()->create([
            'store_id' => $storeId,
            'product_id' => $productId,
            'user_id' => $userId,
            'order_id' => $orderId,
            'stock_lot_id' => $stockLotId,
            'movement_type' => $movementType,
            'quantity_delta' => $quantityDelta,
            'balance_after' => $balanceAfter,
            'note' => $note,
        ]);
    }
}
