<?php

namespace App\Models;

use App\Helpers\DateUtility;
use Exception;

class LedgerPayment extends BaseModel
{
    /**
     * set extra relationship array to overcome problem of accidential delete
     * this variable used in Controller.php -> delete()
     */
    public Array $child_model_class = [
        LedgerTransaction::class => [
            "foreignKey" => "payment_id",
            "preventDelete" => false
        ]
    ];

    public function fromAccount()
    {
        return $this->belongsTo(LedgerAccount::class, "from_account_id");
    }

    public function toAccount()
    {
        return $this->belongsTo(LedgerAccount::class, "to_account_id");
    }

    public function ledgerTransaction()
    {
        return $this->hasMany(LedgerTransaction::class, 'payment_id');
    }

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

    public function getVoucherDateAttribute($value)
    {
        if ($value) {
            return DateUtility::getDate($value, DateUtility::DATE_OUT_FORMAT);
        }

        return $value;
    }

    protected function beforeSave()
    {
        if ($this->from_account_id == $this->to_account_id)
        {
            throw new Exception("From Account and Other Account Can not be Same");
        }

        $date_fields = ["voucher_date"];
        foreach ($date_fields as $date_field) {
            if ($this->{$date_field}) {
                $this->{$date_field} = DateUtility::getDate($this->{$date_field}, DateUtility::DATE_FORMAT);
            }
        }
    }
}
