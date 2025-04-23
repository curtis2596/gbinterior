<?php

namespace App\Models;

class JobOrderItem extends BaseModel
{
    public function jobOrder()
    {
        return $this->belongsTo(JobOrder::class, 'job_order_id');
    }

    public function fromItem()
    {
        return $this->belongsTo(Item::class, 'from_item_id');
    }
    public function toItem()
    {
        return $this->belongsTo(Item::class, 'to_item_id');
    }
}
