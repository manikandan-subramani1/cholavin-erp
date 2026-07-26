<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class ResponseHelper
{
    public static function success(string $message, mixed $data = [], int $statusCode = 200, array $refresh = []): JsonResponse
    {
        return response()->json([
            'status' => true,
            'success' => true,
            'message' => $message,
            'data' => $data,
            'refresh' => array_replace([
                'datatable' => false,
                'summary' => false,
                'drawer' => false,
            ], $refresh),
        ], $statusCode);
    }

    public static function error(string $message, mixed $errors = [], int $statusCode = 422): JsonResponse
    {
        return response()->json([
            'status' => false,
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'refresh' => [
                'datatable' => false,
                'summary' => false,
                'drawer' => false,
            ],
        ], $statusCode);
    }
}
