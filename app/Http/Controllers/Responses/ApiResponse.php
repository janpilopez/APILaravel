<?php

namespace App\Http\Controllers\Responses;

class ApiResponse
{
    public static function success($data = [], $statusCode = 200, $message = 'success')
    {
        return response()->json([
            'message' => $message,
            'statusCode' => $statusCode,
            'error' => false,
            'data' => $data
        ], $statusCode);
    }

    public static function error($message, $statusCode = 500, $data = [])
    {
        return response()->json([
            'message' => $message,
            'statusCode' => $statusCode,
            'error' => true,
            'data' => $data
        ], $statusCode);
    }
}