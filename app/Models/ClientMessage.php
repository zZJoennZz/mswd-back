<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientMessage extends Model
{
    use HasFactory;
    protected $fillable = [
        "email_address", "full_name", "subject", "message", "notes", "status"
    ];
}
