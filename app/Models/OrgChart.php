<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrgChart extends Model
{
    use HasFactory;
    protected $fillable = [
        'person_id', 'position_id', 'division_id', 'order'
    ];
}
