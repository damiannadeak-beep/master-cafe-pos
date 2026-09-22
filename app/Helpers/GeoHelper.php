<?php

namespace App\Helpers;

/**
 * Helper class untuk kalkulasi geolokasi.
 * Digunakan oleh OrderController dan PaymentController untuk validasi GPS.
 */
class GeoHelper
{
    /**
     * Menghitung jarak antara dua koordinat GPS dalam satuan meter (Formula Haversine).
     *
     * @param  float  $lat1  Latitude titik pertama (dalam derajat)
     * @param  float  $lon1  Longitude titik pertama (dalam derajat)
     * @param  float  $lat2  Latitude titik kedua (dalam derajat)
     * @param  float  $lon2  Longitude titik kedua (dalam derajat)
     * @return float  Jarak dalam meter
     */
    public static function calculateDistanceMeters(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // Radius bumi dalam meter

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }
}
