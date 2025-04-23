<?php

namespace App\Models;

use App\Helpers\DateUtility;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewComplaint extends BaseModel
{
    public $dates = ["date"];

    public function party()
    {
        return $this->belongsTo(Party::class, 'party_id');
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

    protected function beforeSave()
    {
        $date_fields = ["date"];
        foreach ($date_fields as $date_field) {
            if ($this->{$date_field}) {
                $this->{$date_field} = DateUtility::getDate($this->{$date_field}, DateUtility::DATE_FORMAT);
            }
        }
    }

    public function getDateAttribute($value)
    {
        if ($value) {
            return DateUtility::getDate($value, DateUtility::DATE_OUT_FORMAT);
        }

        return $value;
    }

    public function newComplaintItem(){
        return $this->hasMany(NewComplaintItem::class,'complaint_id');
    }

    public function user(){
        return $this->belongsTo(User::class,'assign_to');
    }

}
