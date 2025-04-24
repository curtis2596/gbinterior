<?php

namespace App\Models;

class Transport extends BaseModel
{
    // public $timestamps = false;
    protected $tableName = "transports";
    public static function getList(String $id = "id", String $value = "nameId", $conditions = [], $order_by = "name", $order_dir = "ASC")
    {
        $builder = self::query();

        $conditions["is_active"] = 1; 
        foreach($conditions as $k => $v)
        {
            if (is_array($v))
            {
                $builder->whereIn($k, $v);
            }
            else
            {
                $builder->where($k, $v);
            }
        }

        $records = $builder->with([ 
        ])->orderBy($order_by, $order_dir)->get();
        $list = []; 
        foreach($records as $k => $record)
        {
            $name = "";
            if ($value == "display_name")
            { 
                $name = $record->name;
            }            
            else
            {            
                $name = $record->{$value};
            }
            
            $list[$record->{$id}] = $name;
        } 
         
        return $list;
    }
 
}
