<?php

namespace App\Models;

use App\Helpers\DateUtility;

class PurchaseBill extends BaseModel
{
    public $appends = [
        "display_name",
    ];

    public array $child_model_class = [
        PurchaseBillItem::class => [
            "foreignKey" => "purchase_bill_id",
            "preventDelete" => false
        ],
        PurchaseBillPurchaseOrder::class => [
            "foreignKey" => "purchase_bill_id",
            "preventDelete" => false
        ],
        LedgerTransaction::class => [
            "foreignKey" => "purchase_bill_id",
            "preventDelete" => false
        ],        
        PurchaseReturn::class => [
            "foreignKey" => "purchase_bill_id",
            "preventDelete" => false
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
        $date_fields = ["bill_date"];
        foreach ($date_fields as $date_field) {
            if ($this->{$date_field}) {
                $this->{$date_field} = DateUtility::getDate($this->{$date_field}, DateUtility::DATE_FORMAT);
            }
        }
    }

    public function getBillDateAttribute($value)
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

    public function purchaseBillItem()
    {
        return $this->hasMany(PurchaseBillItem::class, 'purchase_bill_id');
    }

    public function purchaseBillPurchaseOrder()
    {
        return $this->hasMany(PurchaseBillPurchaseOrder::class, 'purchase_bill_id');
    }

    public function ledgerTransaction()
    {
        return $this->hasMany(LedgerTransaction::class, 'purchase_bill_id');
    }

    public function purchaseReturn()
    {
        return $this->hasOne(PurchaseReturn::class, 'purchase_bill_id');
    }

    public function getDisplayName()
    {
        return $this->voucher_no;
    }

    public function getDisplayNameAttribute()
    {
        return $this->getDisplayName();
    }
}
