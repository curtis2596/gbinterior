<?php

namespace App\Models;

class PurchaseOrderItem extends BaseModel
{
    public array $child_model_class = [
        PurchaseBillItem::class => [
            "foreignKey" => "purchase_order_item_id",
            "preventDelete" => true
        ],        
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
