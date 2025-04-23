<?php

namespace App\Models;

use Exception;
use App\Models\WarehouseStock;

class ItemConversion extends BaseModel
{
    public static function boot()
    {
        parent::boot();

        self::deleting(function ($model) {
            WarehouseStock::updateQty($model->warehouse_id, $model->from_item_id, $model->from_qty);
            WarehouseStock::updateQty($model->warehouse_id, $model->to_item_id, -1 * $model->to_qty);
        });
    }

    public function Warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function fromitem()
    {
        return $this->belongsTo(Item::class, 'from_item_id');
    }

    public function toitem()
    {
        return $this->belongsTo(Item::class, 'to_item_id');
    }

    public function process()
    {
        return $this->belongsTo(Process::class, 'process_id');
    }


    public static function processStockMovement($data)
    {
        WarehouseStock::updateQty($data['warehouse_id'], $data['from_item_id'], -1 * $data['from_qty']);
        WarehouseStock::updateQty($data['warehouse_id'], $data['to_item_id'], $data['to_qty']);

        return self::create($data);
    }
}
