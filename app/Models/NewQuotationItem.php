<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewQuotationItem extends BaseModel
{
    
    public function Item(){
        return $this->belongsTo(Item::class,'item_id');
    }

    
}
