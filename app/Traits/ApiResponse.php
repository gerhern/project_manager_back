<?php

namespace App\Traits;

trait ApiResponse
{
    public function sendApiResponse($data, string $message, int $code = 200){
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message,
        ], $code);
    }
}
