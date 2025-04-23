<?php

namespace App\Models;

class PurchaseBillItemWarehouse extends BaseModel
{
    public static function boot()
    {
        parent::boot();

        self::created(function ($model) {

            $model->load("purchaseBillItem");

            WarehouseStock::updateQty($model->warehouse_id, $model->purchaseBillItem->item_id, $model->qty);            
        });

        self::deleting(function ($model) {

            $model->load("purchaseBillItem");

            WarehouseStock::updateQty($model->warehouse_id, $model->purchaseBillItem->item_id, -1 * $model->qty);            
        });
    }

    public function purchaseBillItem()
    {
        return $this->belongsTo(PurchaseBillItem::class, 'purchase_bill_item_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }
}
