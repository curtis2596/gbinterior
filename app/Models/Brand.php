<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends BaseModel
{
    protected $table = 'brands'; 
    CONST TYPE_COMPANY = "company";
    CONST TYPE_PARTY = "party";

    const TYPE_LIST = [
        self::TYPE_COMPANY => "Company Brand",
        self::TYPE_PARTY => "Party Brand",
    ];
}
