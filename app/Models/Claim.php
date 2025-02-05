<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Claim extends Model
{
    use HasFactory;

    protected $table = 'claim';

    const STATUS_SUBMITTED = 'Submitted';
    const STATUS_APPROVED_ADMIN = 'Approved Admin';
    const STATUS_APPROVED_MANAGER = 'Approved Manager';
    const STATUS_APPROVED_HR = 'Approved HR';
    const STATUS_PENDING_DATUK = 'Pending Datuk';
    const STATUS_APPROVED_DATUK = 'Approved Datuk';
    const STATUS_APPROVED_FINANCE = 'Approved Finance';
    const STATUS_REJECTED = 'Rejected';
    const STATUS_DONE = 'Done';
    const STATUS_CANCELLED = 'Cancelled';


    protected $fillable = [
        'user_id',
        'reviewer_id',
        'title',
        'description',
        'claim_company',
        'petrol_amount',
        'toll_amount',
        'total_distance',
        'date_from',
        'date_to',
        'status',
        'submitted_at'
    ];

    protected $casts = [
        'date_from' => 'date',
        'date_to' => 'date',
        'submitted_at' => 'datetime',
        'petrol_amount' => 'decimal:2',
        'toll_amount' => 'decimal:2',
        'total_distance' => 'decimal:2'
    ];

    protected $dates = [
        'date_from',
        'date_to',
        'created_at',
        'updated_at'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function locations(): HasMany
    {
        return $this->hasMany(ClaimLocation::class);
    }

    public function accommodations(): HasMany
    {
        return $this->hasMany(ClaimAccommodation::class);
    }

    public function documents(): HasOne
    {
        return $this->hasOne(ClaimDocument::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ClaimReview::class);
    }

    public function rejections()
    {
        return $this->hasMany(ClaimReview::class)->where('status', self::STATUS_REJECTED);
    }

    public function getTotalAmount(): float
    {
        return $this->petrol_amount + $this->toll_amount;
    }

    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_SUBMITTED => 'bg-amber-50 text-amber-700',
            self::STATUS_REJECTED => 'bg-red-50 text-red-700',
            self::STATUS_PENDING_DATUK => 'bg-indigo-50 text-indigo-700',
            self::STATUS_APPROVED_HR => 'bg-purple-50 text-purple-700',
            default => 'bg-gray-50 text-gray-700'
        };
    }

    public function getStatusIconClass(): string
    {
        return match ($this->status) {
            self::STATUS_SUBMITTED => 'text-amber-600',
            self::STATUS_REJECTED => 'text-red-600',
            self::STATUS_PENDING_DATUK => 'text-indigo-600',
            self::STATUS_APPROVED_HR => 'text-purple-600',
            default => 'text-gray-600'
        };
    }

    public function getPendingDuration(): ?string
    {
        if ($this->status === self::STATUS_PENDING_DATUK) {
            $lastUpdate = $this->updated_at;
            $now = now();
            $days = (int)$lastUpdate->diffInDays($now);
            $hours = (int)$lastUpdate->diffInHours($now) % 24;
            $minutes = (int)$lastUpdate->diffInMinutes($now) % 60;
            
            if ($days > 0) {
                return "{$days}d {$hours}h";
            } elseif ($hours > 0) {
                return "{$hours}h {$minutes}m";
            } else {
                return "{$minutes}m";
            }
        }
        
        return null;
    }

    public function isLongPending(): bool
    {
        if ($this->status === self::STATUS_PENDING_DATUK) {
            return $this->updated_at->diffInDays(now()) >= 3;
        }
        return false;
    }

    public function storeTripDetails(array $locations)
    {
        // Validate locations before processing
        if (empty($locations)) {
            throw new \InvalidArgumentException('Cannot store empty locations');
        }

        $this->locations()->delete();

        foreach ($locations as $location) {
            if (empty($location['from_location'])) {
                throw new \InvalidArgumentException('Invalid location data');
            }

            $this->locations()->create([
                'from_location' => $location['from_location'],
                'to_location' => $location['to_location'],
                'distance' => (float) ($location['distance'] ?? 0),
                'order' => (int) ($location['order'] ?? 1)
            ]);
        }
    }
}
