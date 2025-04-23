<?php

namespace App\Models;

class Category extends BaseModel
{
    public array $child_model_class = [
        Party::class => [
            "foreignKey" => "category_id",
            "preventDelete" => true
        ],        
    ];
}
