<?php

namespace App\Models;

class SaleBillSaleOrder extends BaseModel
{
    public function saleBill()
    {
        return $this->belongsTo(SaleBill::class, 'sale_bill_id');
    }

    public function saleOrder()
    {
        return $this->belongsTo(SaleOrder::class, 'sale_order_id');
    }
}
