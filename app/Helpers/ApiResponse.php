<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    /**
     * Respons sukses standar (HTTP 200).
     */
    public static function success($data = null, string $message = 'Sukses', int $statusCode = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if (!is_null($data)) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Respons data baru berhasil dibuat (HTTP 201).
     */
    public static function created($data = null, string $message = 'Berhasil dibuat'): JsonResponse
    {
        return self::success($data, $message, 201);
    }

    /**
     * Respons error standar (HTTP 4xx / 5xx).
     */
    public static function error(string $message = 'Terjadi kesalahan', int $statusCode = 400, $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
            'error' => $message,
        ];

        if (!is_null($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Respons akses ditolak / tidak diizinkan (HTTP 403).
     */
    public static function forbidden(string $message = 'Akses ditolak'): JsonResponse
    {
        return self::error($message, 403);
    }

    /**
     * Respons data tidak ditemukan (HTTP 404).
     */
    public static function notFound(string $message = 'Data tidak ditemukan'): JsonResponse
    {
        return self::error($message, 404);
    }
}
