<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Type extends BaseModel
{

        const STATIC_DATA = [
            1 => 'Type 1',
            2 => 'Type 2',
            3 => 'Type 3',
            4 => 'Type 4'
        ];
    
        public static function getStaticList(String $key_field = "id", String $value_field = "name")
        {
            $list = [];
    
            foreach (self::STATIC_DATA as $id => $name) {
                $list[$id] = $name;
            }
    
            return $list;
        }

}
