<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Notifications\ResetPasswordNotification;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $attributes = [
        'role_id' => 2  // Default to Staff role
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
        'bank_name',
        'bank_account_holder',
        'bank_account_number',
        'department_id',
        'password',
        'password_setup_token',
        'role_id'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
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
}
