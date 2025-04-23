<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class NewQuotation extends BaseModel
{
    
    public array $child_model_class = [
        NewQuotationItem::class => [
            "foreignKey" => "quotation_id",
            "preventDelete" => false
        ],
        NewQuotationFile::class => [
            "foreignKey" => "quotation_id",
            "preventDelete" => false
        ],
    ];

    public function quotationItem()
    {
        return $this->hasMany(NewQuotationItem::class, "quotation_id");
    }

    public function party()
    {
        return $this->belongsTo(Party::class, "party_id");
    }

    public function quotationFiles()
    {
        return $this->hasMany(NewQuotationFile::class, 'quotation_id');
    }
    public function getPdfUrlAttribute()
    {
        return $this->pdf_path ? Storage::url($this->pdf_path) : null;
    }
    public function user()
    {
        return $this->belongsTo(User::class, "follow_up_user_id");
    }
}
