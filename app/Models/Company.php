<?php

namespace App\Models;

class Company extends BaseModel
{
    public $timestamps = false;

    public function state()
    {
        return $this->belongsTo(State::class, 'state_id')->withDefault(['name' => '']);
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id')->withDefault(['name' => '']);
    }
}
