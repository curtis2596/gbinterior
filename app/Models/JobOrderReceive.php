<?php

namespace App\Models;

class JobOrderReceive extends BaseModel
{
    protected $dates = ['receive_date'];

    public array $child_model_class = [
        JobOrderReceiveItem::class => [
            "foreignKey" => "job_order_receive_id",
            "preventDelete" => false
        ],
        LedgerTransaction::class => [
            "foreignKey" => "manufacture_id",
            "preventDelete" => false
        ],
    ];

    public function joborder()
    {
        return $this->belongsTo(JobOrderItem::class, 'job_order_id');
    }
    
    public function jobOrderReceiveItem()
    {
        return $this->hasMany(JobOrderReceiveItem::class, 'job_order_receive_id');
    }

    public function party()
    {
        return $this->belongsTo(Party::class, 'party_id');
    }

    public function toItem()
    {
        return $this->belongsTo(Item::class, 'to_item_id');
    }

    public function jobOrders()
    {
        return $this->belongsTo(JobOrder::class, 'job_order_id','id');
    }

    public function ledgerTransaction()
    {
        return $this->hasMany(LedgerTransaction::class, 'manufacture_id');
    }

}
