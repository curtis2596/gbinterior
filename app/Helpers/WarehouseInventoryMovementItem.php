<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseInventoryMovementItem extends BaseModel
{
    use HasFactory;
    public static function boot()
    {
        parent::boot();

        self::deleting(function ($model) {
            $movement = WarehouseInventoryMovement::find($model->warehouse_movement_id);

            if ($movement) {
                WarehouseStock::updateQty($movement->from_warehouse_id, $model->item_id, $model->qty);
                WarehouseStock::updateQty($movement->to_warehouse_id, $model->item_id, -1 * $model->qty);
            }
        });
    }

    public static function processStockMovement($data)
    {
        $movement = WarehouseInventoryMovement::find($data['warehouse_movement_id']);

        WarehouseStock::updateQty($movement->from_warehouse_id, $data['item_id'], -1 * $data['qty']);

        WarehouseStock::updateQty($movement->to_warehouse_id, $data['item_id'], $data['qty']);

        return self::create($data);
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
