<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Notifications\ResetPasswordNotification;
use App\Models\Auth\Role;
use App\Models\Claim\Claim;
use App\Models\User\Department;
use App\Models\User\BankingInformation;
use App\Models\User\LoginActivity;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $attributes = [
        'role_id' => 1  // Update default to Staff role (1)
    ];

    protected $fillable = [
        'first_name',
        'second_name',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'zip_code',
        'country',
        'profile_picture',
        'signature_path',
        'department_id',
        'password',
        'password_setup_token',
        'password_setup_expires_at',
        'role_id'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'password_setup_expires_at' => 'datetime',
    ];

    public function claims()
    {
        return $this->hasMany(Claim::class, 'user_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function bankingInformation()
    {
        return $this->hasOne(BankingInformation::class);
    }

    /**
     * Get the login activities for the user.
     */
    public function loginActivities()
    {
        return $this->hasMany(LoginActivity::class);
    }
}
