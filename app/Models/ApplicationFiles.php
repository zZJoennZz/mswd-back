<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationFiles extends Model
{
    use HasFactory;
    protected $fillable = [
        "app_id", "file_name", "file"
    ];
}
