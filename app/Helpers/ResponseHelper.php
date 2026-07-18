<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class ResponseHelper
{
    public static function success(
        string $message,
        mixed $data = [],
        int $statusCode = 200,
        ?array $refresh = null,
        ?string $redirect = null,
    ): JsonResponse
    {
        $payload = is_array($data) ? $data : $data;
        $redirect ??= is_array($data) && isset($data['redirect']) ? (string) $data['redirect'] : null;
        $refresh ??= [
            'datatable' => ! request()->isMethodSafe(),
            'summary' => false,
            'drawer' => false,
        ];

        return response()->json([
            'success' => true,
            // Kept during the transition so existing consumers remain compatible.
            'status' => true,
            'message' => $message,
            'data' => $payload,
            'refresh' => $refresh,
            'redirect' => $redirect,
        ], $statusCode);
    }

    public static function error(
        string $message,
        mixed $errors = [],
        int $statusCode = 422,
        ?string $errorCode = null,
        ?string $reference = null,
    ): JsonResponse
    {
        $payload = [
            'success' => false,
            // Kept during the transition so existing consumers remain compatible.
            'status' => false,
            'message' => $message,
            'errors' => $errors,
        ];

        if ($errorCode) {
            $payload['error_code'] = $errorCode;
        }

        if ($reference) {
            $payload['reference'] = $reference;
        }

        return response()->json($payload, $statusCode);
    }
}
