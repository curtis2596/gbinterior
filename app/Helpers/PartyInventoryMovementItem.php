<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartyInventoryMovementItem extends BaseModel
{
    use HasFactory;

    public static function boot()
    {
        parent::boot();

        self::deleting(function ($model) {
            WarehouseStock::updateQty($model->warehouse_id, $model->item_id, $model->qty);            
        });
    }

    public static function processStockMovement($data)
    {
        WarehouseStock::updateQty($data['warehouse_id'], $data['item_id'], -1 * $data['qty']);

        return self::create($data);
    }
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
