<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RegistrationRequest extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'department',
        'status',
        'token'
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($request) {
            $request->token = Str::random(64);
        });
    }
} 