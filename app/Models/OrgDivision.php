<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrgDivision extends Model
{
    use HasFactory;
    protected $fillable = [
        'division_name', 'sub_division_of', 'order'
    ];
}
