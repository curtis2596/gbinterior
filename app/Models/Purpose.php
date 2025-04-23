<?php

namespace App\Models;

class Purpose extends BaseModel
{
    public array $child_model_class = [
        Enquiry::class => [
            "foreignKey" => "purpose_id",
            "preventDelete" => true
        ], 
    ];
}
