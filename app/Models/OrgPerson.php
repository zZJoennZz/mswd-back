<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrgPerson extends Model
{
    use HasFactory;
    protected $fillable = [
        'first_name', 'middle_initial', 'last_name', 'suffix', 'gender', 'birthday', 'img_path'
    ];
}
