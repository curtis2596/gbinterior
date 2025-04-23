<?php

namespace App\Models;

use App\Helpers\DateUtility;

class SaleBill extends BaseModel
{
    public $appends = [
        "display_name"
    ];
    
    public array $child_model_class = [
        SaleBillItem::class => [
            "foreignKey" => "sale_bill_id",
            "preventDelete" => false
        ],
        SaleBillSaleOrder::class => [
            "foreignKey" => "sale_bill_id",
            "preventDelete" => false
        ],
        LedgerTransaction::class => [
            "foreignKey" => "sale_bill_id",
            "preventDelete" => false
        ],     
        SaleReturn::class => [
            "foreignKey" => "sale_bill_id",
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

    public function saleBillItem()
    {
        return $this->hasMany(SaleBillItem::class, 'sale_bill_id');
    }

    public function saleBillSaleOrder()
    {
        return $this->hasMany(SaleBillSaleOrder::class, 'sale_bill_id');
    }

    public function ledgerTransaction()
    {
        return $this->hasMany(LedgerTransaction::class, 'sale_bill_id');
    }

    public function saleReturn()
    {
        return $this->hasOne(SaleReturn::class, "sale_bill_id");
    }

    public function getDisplayNameAttribute()
    {
        return $this->voucher_no;
    }
}
