<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSecurity extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_security_logs'; // Explicitly define table name

    /**
     * Indicates if the model should be timestamped.
     *
     * We have 'attempted_at', so we don't need default timestamps.
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'ip_address',
        'user_agent',
        'attempted_at',
        'successful',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'attempted_at' => 'datetime',
        'successful' => 'boolean',
    ];
} 