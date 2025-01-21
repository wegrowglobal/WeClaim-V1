<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClaimAccommodation extends Model
{
    use HasFactory;

    protected $fillable = [
        'claim_id',
        'location',
        'price',
        'check_in',
        'check_out',
        'receipt_path'
    ];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'price' => 'decimal:2'
    ];

    public function claim()
    {
        return $this->belongsTo(Claim::class);
    }
} 