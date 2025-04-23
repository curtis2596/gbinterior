<?php

namespace App\Models;

use App\Helpers\DateUtility;

class SaleReturn extends BaseModel
{
    public $appends = [
        "display_name",
    ];
    
    public array $child_model_class = [
        LedgerTransaction::class => [
            "foreignKey" => "sale_return_id",
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
        $date_fields = ["voucher_date"];
        foreach ($date_fields as $date_field) {
            if ($this->{$date_field}) {
                $this->{$date_field} = DateUtility::getDate($this->{$date_field}, DateUtility::DATE_FORMAT);
            }
        }
    }

    public function getVoucherDateAttribute($value)
    {
        if ($value) {
            return DateUtility::getDate($value, DateUtility::DATE_OUT_FORMAT);
        }

        return $value;
    }

    public function saleBill()
    {
        return $this->belongsTo(SaleBill::class, 'sale_bill_id');
    }

    public function ledgerTransaction()
    {
        return $this->hasMany(LedgerTransaction::class, 'sale_return_id');
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
