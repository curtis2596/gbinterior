<?php

namespace App\Models;

class ComplaintType extends BaseModel
{
    public array $child_model_class = [
        Complaint::class => [
            "foreignKey" => "complaint_type",
            "preventDelete" => true
        ], 
    ];
}
