<?php

namespace App\Models\Claim;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    public function claim()
    {
        return $this->belongsTo(Claim::class);
    }
} 