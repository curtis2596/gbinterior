<?php

namespace App\Models;

class PurchaseBillItem extends BaseModel
{
    public array $child_model_class = [
        PurchaseBillItemWarehouse::class => [
            "foreignKey" => "purchase_bill_item_id",
            "preventDelete" => true
        ],        
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
        $purchase_order_item = $this->purchaseOrderItem()->first();
        if ($purchase_order_item)
        {
            $purchase_order_item->received_qty -= $this->qty;
            $purchase_order_item->save();
        }
    }

    public function purchaseBill()
    {
        return $this->belongsTo(PurchaseBill::class, 'purchase_bill_id');
    }

    public function purchaseOrderItem()
    {
        return $this->belongsTo(PurchaseOrderItem::class, 'purchase_order_item_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function purchaseBillItemWarehouse()
    {
        return $this->hasMany(PurchaseBillItemWarehouse::class, 'purchase_bill_item_id');
    }
}
