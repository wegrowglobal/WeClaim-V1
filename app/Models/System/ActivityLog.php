<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'type',
        'activity_type',
        'description',
        'ip_address',
        'user_agent',
        'data',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that performed the activity.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include admin activities.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAdmin($query)
    {
        return $query->where('type', 'admin');
    }

    /**
     * Scope a query to only include user activities.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUser($query)
    {
        return $query->where('type', 'user');
    }

    /**
     * Scope a query to only include system activities.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSystem($query)
    {
        return $query->where('type', 'system');
    }

    /**
     * Scope a query to only include activities of a specific type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $activityType
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, $activityType)
    {
        return $query->where('activity_type', $activityType);
    }

    /**
     * Scope a query to only include activities within a date range.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $startDate
     * @param  string  $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Get user-friendly activity type name.
     *
     * @return string
     */
    public function getActivityNameAttribute()
    {
        $types = [
            'login' => 'Login',
            'logout' => 'Logout',
            'claim_created' => 'Claim Created',
            'claim_updated' => 'Claim Updated',
            'claim_status_change' => 'Claim Status Changed',
            'claim_note_added' => 'Claim Note Added',
            'document_uploaded' => 'Document Uploaded',
            'document_downloaded' => 'Document Downloaded',
            'export' => 'Report Exported',
            'user_created' => 'User Created',
            'user_updated' => 'User Updated',
            'user_deleted' => 'User Deleted',
            'password_reset' => 'Password Reset',
        ];

        return $types[$this->activity_type] ?? ucfirst(str_replace('_', ' ', $this->activity_type));
    }
} 