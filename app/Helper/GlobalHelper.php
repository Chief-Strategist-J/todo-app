<?php

namespace App\Helper;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;

function errorMsg($message, int $statusCode = 422): void
{
    throw new HttpResponseException(response()->json([
        'success' => false,
        'message' => $message,
        'data' => []
    ], $statusCode));
}


function successMessage(string $message = "done", bool $status = true, $data = []): JsonResponse
{
    return response()->json([
        "message" => $message,
        "status" => $status,
        "data" => $data
    ]);
}
