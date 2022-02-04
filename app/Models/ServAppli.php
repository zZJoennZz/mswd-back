<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServAppli extends Model
{
    use HasFactory;
    protected $fillable =[
      'application_id', 'service_id', 'first_name', 'middle_name', 'last_name', 'birthday', 'gender', 'email_address', 'contact_number', 
    ];
}
