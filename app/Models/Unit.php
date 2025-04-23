<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class Unit extends BaseModel
{
    protected $appends = [
        'name_code',
        'display_name',
        'number_round_type',
    ];

    public array $child_model_class = [
        Item::class => [
            "foreignKey" => "unit_id",
            "preventDelete" => true
        ]
    ];

    public function getNameCodeAttribute()
    {
        $name = $this->name;
        if ($this->code)
        {
            $name .= " (" . $this->code . ")";
        }

        return $name;
    }

    public function getDisplayNameAttribute()
    {
        return $this->getDisplayName();
    }

    public function getNumberRoundTypeAttribute()
    {
        $code = trim(strtoupper($this->code));

        if ($code == "PC")
        {
            return "int";
        }

        return "float";
    }

    public function getDisplayName()
    {
        if ($this->code)
        {
            return $this->code;
        }

        return $this->name;
    }

    protected static function _getList(Builder $builder, string $id, string $value)
    {
        $records = $builder->get();

        $list = [];

        foreach($records as $record)
        {
            $list[$record->{$id}] = $record->{$value};
        }

        return $list;
    }

    public function round($value)
    {
        $code = trim(strtoupper($this->code));
        if ($code == "PC")
        {
            return round($value);
        }
        else
        {
            return round($value, 2);
        }
    }
}
