<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class State extends BaseModel
{
    
    public function city(){
        return $this->hasMany(City::class);
    }

    public function user(){
        return $this->hasMany(User::class);
    }

}
