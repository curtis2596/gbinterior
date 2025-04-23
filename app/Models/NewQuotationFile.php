<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewQuotationFile extends BaseModel
{
    public static function getFileSavePath()
    {
        return 'files/quotations/'; // Base path for all attachments
    }
}
