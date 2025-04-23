<?php

namespace App\Models;

use App\Helpers\DateUtility;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends BaseModel
{
    public array $child_model_class = [
        LeadItem::class => [
            "foreignKey" => "lead_id ",
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
        $date_fields = ["date", "follow_up_date"];
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

    public function getFollowUpDateAttribute($value)
    {
        if ($value) {
            return DateUtility::getDate($value, DateUtility::DATE_OUT_FORMAT);
        }

        return $value;
    }

    public function leadItem()
    {
        return $this->hasMany(LeadItem::class, "lead_id");
    }

    public function party()
    {
        return $this->belongsTo(Party::class, "party_id");
    }

    public function user()
    {
        return $this->belongsTo(User::class, "follow_up_user_id");
    }

    public function sources()
    {
        return $this->belongsTo(Source::class, "lead_source_id");
    }
}
