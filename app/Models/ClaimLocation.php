<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClaimLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'claim_id',
        'from_location',
        'to_location',
        'order',
        'distance'
    ];

    protected $casts = [
        'distance' => 'decimal:2',
        'order' => 'integer'
    ];

    public function claim()
    {
        return $this->belongsTo(Claim::class, 'claim_id');
    }
}
