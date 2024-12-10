<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemConfig extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
        'group'
    ];

    protected $casts = [
        'value' => 'json',
    ];

    public static function getConfig($key, $default = null)
    {
        $config = self::where('key', $key)->first();
        return $config ? $config->value : $default;
    }
}
