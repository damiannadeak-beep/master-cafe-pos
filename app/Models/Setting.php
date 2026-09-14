<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value'];

    /**
     * Helper to get a setting value by key.
     */
    public static function getVal($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return ($setting !== null && $setting->value !== null && trim((string)$setting->value) !== '') ? $setting->value : $default;
    }
}
