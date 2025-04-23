<?php

namespace App\Helpers;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

Trait GetListTrait
{
    public static function fetchList(Builder $builder, String $id_field, String $value_field)
    {       
        if (Schema::hasColumn($builder->getTable(), $id_field) && Schema::hasColumn($builder->getTable(), $value_field))
        {
            return $builder->pluck($value_field, $id_field)->toArray();
        }

        $records = $builder->get();

        $list = [];

        foreach($records as $record)
        {
            $list[$record->{$id_field}] = $record->{$value_field};
        }

        return $list;
    }
}