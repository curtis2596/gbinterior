<?php

namespace App\Models;

class SaleBillItem extends BaseModel
{
    public array $child_model_class = [
        
    ];

    public static function boot()
    {
        parent::boot();

        self::deleted(function ($model) {
            $model->afterDelete();
        });
    }

    public function afterDelete()
    {
        $sale_order_item = $this->saleOrderItem()->first();
        if ($sale_order_item)
        {
            $sale_order_item->sent_qty -= $this->qty;
            $sale_order_item->save();
        }
    }

    public function saleBill()
    {
        return $this->belongsTo(SaleBill::class, 'sale_bill_id');
    }

    public function saleOrderItem()
    {
        return $this->belongsTo(SaleOrderItem::class, 'sale_order_item_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function saleBillItemWarehouse()
    {
        return $this->hasMany(SaleBillItemWarehouse::class, 'sale_bill_item_id');
    }
}
