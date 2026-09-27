<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $fillable = ['name', 'phone', 'email', 'subject', 'message', 'locale', 'read_at', 'ip'];

    protected $casts = ['read_at' => 'datetime'];
}
