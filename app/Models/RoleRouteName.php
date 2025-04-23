<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class RoleRouteName extends BaseModel
{
    use HasFactory;

    protected static Array $unique_fields = ["role_id", "route_name_id"];

    public function role()  
    {
        return $this->belongsTo(Role::class, "role_id");
    }

    public function routeName()
    {
        return $this->belongsTo(RouteName::class, "route_name_id");
    }
}
