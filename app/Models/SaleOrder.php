<?php

namespace App\Models;

use App\Helpers\DateUtility;

class SaleOrder extends BaseModel
{
    public $dates = ["order_date", "expected_delivery_date"];

    public array $child_model_class = [
        SaleOrderItem::class => [
            "foreignKey" => "sale_order_id",
            "preventDelete" => false
        ],        
    ];

    // public static function boot()
    // {
    //     parent::boot();

    //     self::creating(function ($model) {
    //         $model->beforeSave();
    //     });

    //     self::updating(function ($model) {
    //         $model->beforeSave();
    //     });
    // }

    // protected function beforeSave()
    // {
    //     $date_fields = ["expected_delivery_date"];
    //     foreach ($date_fields as $date_field) {
    //         if ($this->{$date_field}) {
    //             $this->{$date_field} = DateUtility::getDate($this->{$date_field}, DateUtility::DATE_FORMAT);
    //         }
    //     }
    // }

    public function party()
    {
        return $this->belongsTo(Party::class, 'party_id');
    }

    public function saleOrderItem()
    {
        return $this->hasMany(SaleOrderItem::class, 'sale_order_id');
    }

    public function checkPending()
    {
        $pending_qty = 0;

        if (isset($this->saleOrderItem))
        {
            foreach($this->saleOrderItem as $saleOrderItem)
            {
                $pending_qty += $saleOrderItem->required_qty - $saleOrderItem->sent_qty;
            }
        }

        $this->pending_qty = $pending_qty;
    }
}
