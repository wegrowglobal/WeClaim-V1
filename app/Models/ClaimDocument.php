<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ClaimDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'claim_id',
        'toll_file_name',
        'toll_file_path',
        'email_file_name',
        'email_file_path',
        'uploaded_by'
    ];

    public function claim(): BelongsTo
    {
        return $this->belongsTo(Claim::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Helper methods for file URLs
    public function getTollFileUrl(): ?string
    {
        return $this->toll_file_path ? Storage::disk('public')->url($this->toll_file_path) : null;
    }

    public function getEmailFileUrl(): ?string
    {
        return $this->email_file_path ? Storage::disk('public')->url($this->email_file_path) : null;
    }

    // Clean up files when document is deleted
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($document) {
            if ($document->toll_file_path) {
                Storage::disk('public')->delete($document->toll_file_path);
            }
            if ($document->email_file_path) {
                Storage::disk('public')->delete($document->email_file_path);
            }
        });
    }
}
