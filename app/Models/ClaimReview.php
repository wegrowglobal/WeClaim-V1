<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClaimReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'claim_id',
        'reviewer_id',
        'remarks',
        'review_order',
        'department',
        'reviewed_at',
        'status',
        'rejection_details',
        'requires_basic_info',
        'requires_trip_details',
        'requires_accommodation_details',
        'requires_documents'
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'rejection_details' => 'array',
        'requires_basic_info' => 'boolean',
        'requires_trip_details' => 'boolean',
        'requires_accommodation_details' => 'boolean',
        'requires_documents' => 'boolean'
    ];

    public function claim(): BelongsTo
    {
        return $this->belongsTo(Claim::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function getRequiredSectionsAttribute(): array
    {
        $sections = [];
        
        if ($this->requires_basic_info) {
            $sections[] = 'Basic Information';
        }
        if ($this->requires_trip_details) {
            $sections[] = 'Trip Details';
        }
        if ($this->requires_accommodation_details) {
            $sections[] = 'Accommodation Details';
        }
        if ($this->requires_documents) {
            $sections[] = 'Documents';
        }

        return $sections;
    }

    public function getSectionsNeedingRevisionAttribute(): array
    {
        return array_filter([
            'basic_info' => $this->requires_basic_info,
            'trip_details' => $this->requires_trip_details,
            'accommodation_details' => $this->requires_accommodation_details,
            'documents' => $this->requires_documents
        ], function($value) {
            return $value === true;
        });
    }

    public function needsRevision(): bool
    {
        return $this->requires_basic_info ||
               $this->requires_trip_details ||
               $this->requires_accommodation_details ||
               $this->requires_documents;
    }
}
