<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;
class ResponseHelper
{
    public static function apiResponse($success = true, $message = 'Success', $data = null, $statusCode = 200, $errors = null): JsonResponse
    {
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => $success ? $data : null,
            'errors' => $success ? null : $errors,
        ], $statusCode);
    }
}
