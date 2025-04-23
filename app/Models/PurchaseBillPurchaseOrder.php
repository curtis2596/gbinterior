<?php

namespace App\Models;

class PurchaseBillPurchaseOrder extends BaseModel
{
    public function purchaseBill()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_bill_id');
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }
}
