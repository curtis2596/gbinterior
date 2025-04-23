<?php

namespace App\Models;

use App\Models\WarehouseStock;
use Exception;

class WarehouseInventoryMovement extends BaseModel
{
    protected $dates = ['challan_date'];

    // public static function boot()
    // {
    //     parent::boot();

    //     self::deleting(function ($model) {
    //         WarehouseStock::updateQty($model->from_warehouse_id, $model->item_id, $model->qty);
    //         WarehouseStock::updateQty($model->to_warehouse_id, $model->item_id, -1 * $model->qty);
    //     });
    // }

    public function fromWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function warehouseinventoryitem()
    {
        return $this->hasMany(WarehouseInventoryMovementItem::class, 'warehouse_movement_id');
    }


    // public static function processStockMovement($data)
    // {        
    //     // dd($data);
    //     WarehouseStock::updateQty($data['from_warehouse_id'], $data['item_id'], -1 * $data['qty']);
    //     WarehouseStock::updateQty($data['to_warehouse_id'], $data['item_id'], $data['qty']);

    //     return self::create($data);
    // }
}
