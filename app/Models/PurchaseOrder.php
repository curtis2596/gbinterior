<?php

namespace App\Models;

use App\Helpers\DateUtility;

class PurchaseOrder extends BaseModel
{
    public array $child_model_class = [
        PurchaseOrderItem::class => [
            "foreignKey" => "purchase_order_id",
            "preventDelete" => true
        ],        
    ];

    public static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            $model->beforeSave();
        });

        self::updating(function ($model) {
            $model->beforeSave();
        });
    }

    protected function beforeSave()
    {
        $date_fields = ["expected_delivery_date", "po_date"];
        foreach ($date_fields as $date_field) {
            if ($this->{$date_field}) {
                $this->{$date_field} = DateUtility::getDate($this->{$date_field}, DateUtility::DATE_FORMAT);
            }
        }
    }

    public function getExpectedDeliveryDateAttribute($value)
    {
        if ($value) {
            return DateUtility::getDate($value, DateUtility::DATE_OUT_FORMAT);
        }

        return $value;
    }

    public function getPoDateAttribute($value)
    {
        if ($value) {
            return DateUtility::getDate($value, DateUtility::DATE_OUT_FORMAT);
        }

        return $value;
    }

    public function party()
    {
        return $this->belongsTo(Party::class, 'party_id');
    }

    public function purchaseOrderItem()
    {
        return $this->hasMany(PurchaseOrderItem::class, 'purchase_order_id');
    }

    public function checkPending()
    {
        $pending_qty = 0;

        if (isset($this->purchaseOrderItem))
        {
            foreach($this->purchaseOrderItem as $purchaseOrderItem)
            {
                $pending_qty += $purchaseOrderItem->required_qty - $purchaseOrderItem->received_qty;
            }
        }

        $this->pending_qty = $pending_qty;
    }
}
