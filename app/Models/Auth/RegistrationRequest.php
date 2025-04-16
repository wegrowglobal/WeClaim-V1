<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User\Department;
use App\Models\Auth\Role;
use Carbon\Carbon;

class RegistrationRequest extends Model
{
    protected $fillable = [
        'first_name',
        'second_name',
        'email',
        'role_id',
        'department_id',
        'status',
        'token',
        'token_expires_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'token_expires_at' => 'datetime',
    ];

    /**
     * Get the role associated with the registration request.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the department associated with the registration request.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Scope a query to only include pending requests.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'Pending');
    }

    /**
     * Check if the token is expired.
     */
    public function isTokenExpired(): bool
    {
        return $this->token_expires_at ? Carbon::now()->gt($this->token_expires_at) : true;
    }
} 