<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email',
        'ip_address',
        'user_agent',
        'device',
        'browser',
        'platform',
        'status',
        'location',
    ];

    /**
     * Get the user that owns the login activity.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 