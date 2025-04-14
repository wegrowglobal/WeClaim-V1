<?php

namespace App\Models\Claim;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClaimLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'claim_id',
        'from_location',
        'to_location',
        'distance',
        'order'
    ];

    protected $casts = [
        'distance' => 'decimal:2',
        'order' => 'integer'
    ];

    public function claim(): BelongsTo
    {
        return $this->belongsTo(Claim::class);
    }

    // Helper method to format the location for display
    public function getFormattedRoute(): string
    {
        return "{$this->from_location} → {$this->to_location}";
    }

    // Helper method to get distance in km with unit
    public function getFormattedDistance(): string
    {
        return number_format($this->distance, 2) . ' km';
    }
}
