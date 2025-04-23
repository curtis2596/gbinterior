<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends BaseModel
{
    
    public function state(){
        return $this->belongsTo(State::class);
    }    

}
