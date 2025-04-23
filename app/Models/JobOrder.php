<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobOrder extends BaseModel
{
    public $dates = ["expected_complete_date"];

    public array $child_model_class = [
        JobOrderItem::class => [
            "foreignKey" => "job_order_id",
            "preventDelete" => false
        ],        
    ];

    public function party()
    {
        return $this->belongsTo(Party::class, 'party_id');
    }

    public function JobOrderItem()
    {
        return $this->hasMany(JobOrderItem::class, 'job_order_id');
    }

    public function process()
    {
        return $this->belongsTo(Process::class, 'process_id');
    }

    public function jobOrderReceive()
    {
        return $this->hasOne(JobOrderReceive::class, "job_order_id");
    }

    public function checkPending()
    {
        $pending_qty = 0;

        if (isset($this->JobOrderItem))
        {
            foreach($this->JobOrderItem as $JobOrderItem)
            {
                $pending_qty += $JobOrderItem->required_qty - $JobOrderItem->sent_qty;
            }
        }

        $this->pending_qty = $pending_qty;
    }

}
