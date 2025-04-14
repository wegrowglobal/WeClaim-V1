<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Changelog extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'version',
        'type',
        'is_published',
        'published_at',
        'created_by'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    /**
     * The content attribute should not be automatically escaped
     * as it may contain HTML that we want to render.
     */
    protected $htmlFields = ['content'];

    /**
     * Get the user who created the changelog entry.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope a query to only include published changelogs.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope a query to order by published date in descending order.
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('published_at', 'desc');
    }
}
