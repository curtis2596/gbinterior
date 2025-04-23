<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class RouteName extends BaseModel
{
    use HasFactory;

    protected static Array $unique_fields = ["name"];
}
