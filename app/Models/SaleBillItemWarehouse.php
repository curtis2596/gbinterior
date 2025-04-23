<?php

namespace App\Models;

class SaleBillItemWarehouse extends BaseModel
{
    public static function boot()
    {
        parent::boot();

        self::created(function ($model) {

            $model->load("saleBillItem");

            WarehouseStock::updateQty($model->warehouse_id, $model->saleBillItem->item_id, -1 * $model->qty);
        });

        self::deleting(function ($model) {

            $model->load("saleBillItem");

            WarehouseStock::updateQty($model->warehouse_id, $model->saleBillItem->item_id, $model->qty);
        });
    }

    public function saleBillItem()
    {
        return $this->belongsTo(SaleBillItem::class, 'sale_bill_item_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }
}
