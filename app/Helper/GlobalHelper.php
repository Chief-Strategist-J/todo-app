<?php

namespace App\Helper;

use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

function errorMsg($message, int $statusCode = 422, $data = []): void
{
    throw new HttpResponseException(response()->json([
        'success' => false,
        'message' => $message,
        'data' => $data
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



function sendNotification($title, $message, $emails, $scheduledTime = null, $todoId = 25)
{
    $url = 'https://onesignal.com/api/v1/notifications';

    $header = [
        'Authorization' => 'Basic ' . env('ONE_SIGNAL_REST_KEY'),
        'Content-Type' => 'application/json',
    ];

    $sendAfter = null;

    if ($scheduledTime !== null) {

        $scheduledTimeIST = Carbon::parse($scheduledTime, 'Asia/Kolkata');
        $currentTimeIST = Carbon::now('Asia/Kolkata');

        if ($scheduledTimeIST->lte($currentTimeIST)) {
            $scheduledTimeIST = $currentTimeIST->addSeconds(5);
        }

        $scheduledTimeUTC = $scheduledTimeIST->setTimezone('UTC');
        $sendAfter = $scheduledTimeUTC->format('Y-m-d H:i:s\Z');
    }

    $req = [
        'app_id' => env('ONE_SIGNAL_APP_ID'),
        'headings' => ['en' => $title],
        'contents' => ['en' => $message],
        'filters' => [
            [
                'field' => 'tag',
                'key' => 'email',
                'relation' => '=',
                'value' => $emails,
            ],
        ],
        'buttons' => [
            [
                'id' => 'view_detail',
                'text' => 'View Details',
            ],
        ],
    ];

    if ($sendAfter !== null) {
        $req['send_after'] = $sendAfter;
    }

    Http::withHeaders($header)->post($url, $req);
}
