<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;

abstract class Controller
{
    protected function sendExpoPushNotification($expoToken, $title, $body, $data = [])
{
    if (!$expoToken) return; // If they don't have a token, skip it quietly

    Http::post('https://exp.host/--/api/v2/push/send', [
        'to' => $expoToken,
        'sound' => 'default',
        'title' => $title,
        'body' => $body,
        'data' => $data, // Use this to tell the phone which screen to open (e.g., ChatScreen)
    ]);
}
}

