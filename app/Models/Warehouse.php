<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class Warehouse extends BaseModel
{
    // protected static $model_cache_key = 'Warehouse';

    const TYPE_COMPANY = "company";
    const TYPE_PARTY = "party";

    const TYPE_LIST = [
        self::TYPE_COMPANY => "Company Warehouse",
        self::TYPE_PARTY => "Party Warehouse",
    ];

    public $appends = [
        "display_name",
    ];

    /**
     * set extra relationship array to overcome problem of accidential delete
     * this variable used in Controller.php -> delete()
     */
    public array $child_model_class = [
        WarehouseStock::class => [
            "foreignKey" => "warehouse_id",
            "preventDelete" => true
        ]
    ];

    public function party()
    {
        return $this->belongsTo(Party::class, "party_id");
    }

    public function state()
    {
        return $this->belongsTo(State::class, "state_id");
    }

    public function city()
    {
        return $this->belongsTo(City::class, "city_id");
    }

    public function warehouseStock()
    {
        return $this->hasMany(WarehouseStock::class, "warehouse_id");
    }

    // public function getDisplayName()
    // {
    //     $name = $this->name;

    //     if ($this->type == self::TYPE_PARTY)
    //     {
    //         if (!$this->party || !isset($this->party->name))
    //         {
    //             return $name;
    //             // throw_exception("party->name is not set");
    //         }
    //     }

    //     return $name;
    // }

    public function getDisplayName()
    {
        $name = $this->name;

        if ($this->type == self::TYPE_PARTY) {
            if ($this->relationLoaded("party"))
            {
                if (isset($this->party->name)) {                    
                    $name = $this->party->name . "-" . $name;
                }
            }
        }

        return $name;
    }

    protected static function _getList(Builder $builder, String $id, String $value)
    {
        $builder->with([
            "party" => function ($q) {
                $q->select("id", "name");
            }
        ]);

        $records = $builder->get();

        // d($records->toArray());

        $list = [];

        foreach ($records as $record) {
            if ($value == "display_name") {
                $list[$record->{$id}] = $record->getDisplayName();
            } else {
                $list[$record->{$id}] = $record->{$value};
            }
        }

        return $list;
    }

    public function getDisplayNameAttribute()
    {
        return $this->getDisplayName();
    }
}
