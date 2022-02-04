<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;
    protected $fillable = [
        'internal_name', 'service_name', 'availability', 'who_may_avail', 'requirements', 'duration', 'notes'
    ];
}
