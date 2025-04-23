<?php

namespace App\Models;

use App\Helpers\DateUtility;
use Exception;

class LedgerTransaction extends BaseModel
{
    /**
     * set extra relationship array to overcome problem of accidential delete
     * this variable used in Controller.php -> delete()
     */
    public Array $child_model_class = [
        
    ];

    public function mainAccount()
    {
        return $this->belongsTo(LedgerAccount::class, "main_account_id");
    }

    public function otherAccount()
    {
        return $this->belongsTo(LedgerAccount::class, "otther_account_id");
    }

    public function getVoucherDateAttribute($value)
    {
        if ($value) {
            return DateUtility::getDate($value, DateUtility::DATE_OUT_FORMAT);
        }

        return $value;
    }

    public static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            $model->beforeSave();
        });

        self::created(function ($model) {
            $model->afterSave();
        });

        self::updating(function ($model) {
            $model->beforeSave();
        });

        self::updated(function ($model) {
            $model->afterSave();
        });

        self::deleted(function ($model) {
            $model->afterDelete();
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

    protected function afterSave()
    {
        $this->mainAccount->updateBalance($this->amount);
    }

    protected function afterDelete()
    {
        $this->mainAccount->updateBalance(-1 * $this->amount);
    }
    

    public static function createDoubleEntry($save_arr)
    {
        $required_fields = [
            "main_account_id", "other_account_id", 
            "voucher_type",
            "voucher_date", "voucher_no",
            "amount"
        ];

        array_check_key_and_throw_error($save_arr, $required_fields, "LedgerTransaction->createDoubleEntry : {key} not found");

        if ($save_arr['amount'] <= 0)
        {
            throw_exception("Amount can be less than 0");
        }

        if ($save_arr['main_account_id'] == $save_arr['other_account_id'])
        {
            throw new Exception("Main Account and Other Account Can not be Same");
        }

        $main_account = LedgerAccount::findOrFail($save_arr['main_account_id']);
        $other_account = LedgerAccount::findOrFail($save_arr['other_account_id']);
        if ($main_account->code == LedgerAccount::CODE_stock || $other_account->code == LedgerAccount::CODE_stock)
        {
            $save_arr['is_stock_entry'] = 1;
        }

        self::create($save_arr);

        $reverse_save_arr = $save_arr;
        $reverse_save_arr['main_account_id'] = $save_arr['other_account_id'];
        $reverse_save_arr['other_account_id'] = $save_arr['main_account_id'];
        $reverse_save_arr['amount'] = -1 * $save_arr['amount'];

        self::create($reverse_save_arr);
    }
}
