<?php

use Illuminate\Support\Facades\Route;

Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'Resource not found or invalid endpoint.'
    ], 404);
});