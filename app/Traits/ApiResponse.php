<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    public function sendApiResponse($data, string $message, int $code = 200): JsonResponse{
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message,
        ], $code);
    }

    public function sendApiError(string $message, int $code = 500): JsonResponse {
        return response()->json([
            'success' => false,
            'data' => [],
            'message' => $message
        ], $code);
    }
}
