<?php

namespace App\Models;

class SaleOrderItem extends BaseModel
{
    public array $child_model_class = [
        SaleBillItem::class => [
            "foreignKey" => "sale_order_item_id",
            "preventDelete" => true
        ],        
    ];

    public function saleOrder()
    {
        return $this->belongsTo(SaleOrder::class, 'sale_order_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
