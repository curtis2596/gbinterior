<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Role extends BaseModel
{
    use HasFactory;

    /**
     * name of table fields which uniquly identify the record
     */
    protected static Array $unique_fields = ["name"];

    protected $appends = [
        'display_name',
    ];

    /**
     * set extra relationship array to overcome problem of accidential delete
     * this variable used in Controller.php -> delete()
     */
    public Array $child_model_class = [
        UserRole::class => [
            "foreignKey" => "role_id",
            "preventDelete" => true,
            "label" => "User's Role"
        ],
        RoleRouteName::class => [
            "foreignKey" => "role_id",
            "preventDelete" => false,
            "label" => "Routes"
        ],
    ];

    const TYPE_SYSTEM_ADMIN = "system_admin";    

    public function userRole()
    {
        return $this->hasMany(UserRole::class, 'role_id');
    }

    public function routeNames()
    {
        return $this->belongsToMany(RouteName::class, RoleRouteName::class);
    }

    public function scopeWithAll($query) 
    {
        $query->with('userRole');
    }

    protected function displayName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->name,
        );
    }
}
