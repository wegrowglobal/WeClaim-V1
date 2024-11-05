<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClaimLocation extends Model
{
    protected $fillable = [
        'claim_id',
        'location',
        'order',
        'distance'
    ];

    protected $casts = [
        'distance' => 'decimal:2'
    ];

    use HasFactory;

    public function claim()
    {
        return $this->belongsTo(Claim::class, 'claim_id');
    }
}
