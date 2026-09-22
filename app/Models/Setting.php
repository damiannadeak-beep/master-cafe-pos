<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value'];

    /**
     * Cache statis per-request untuk menghindari query DB berulang.
     * Otomatis reset setiap request baru (lifecycle PHP).
     */
    protected static array $settingsCache = [];

    /**
     * Helper to get a setting value by key (cached per-request).
     */
    public static function getVal($key, $default = null)
    {
        // Return dari cache jika sudah pernah di-query di request ini
        if (array_key_exists($key, static::$settingsCache)) {
            $cachedValue = static::$settingsCache[$key];
            return ($cachedValue !== null && trim((string)$cachedValue) !== '') ? $cachedValue : $default;
        }

        // Jika cache masih kosong, load SEMUA settings sekaligus (1 query untuk semua)
        if (empty(static::$settingsCache)) {
            $allSettings = self::pluck('value', 'key')->toArray();
            static::$settingsCache = $allSettings;

            if (array_key_exists($key, static::$settingsCache)) {
                $cachedValue = static::$settingsCache[$key];
                return ($cachedValue !== null && trim((string)$cachedValue) !== '') ? $cachedValue : $default;
            }
        }

        // Key tidak ditemukan di database
        static::$settingsCache[$key] = null;
        return $default;
    }

    /**
     * Flush cache (panggil setelah update setting agar data fresh).
     */
    public static function flushCache(): void
    {
        static::$settingsCache = [];
    }
}

