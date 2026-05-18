<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class MoceanService
{
    public static function sendSMS($phone, $message)
    {
        $response = Http::withToken(env('MOCEAN_API_TOKEN'))
            ->post('https://rest.moceanapi.com/rest/2/sms', [
                'mocean-from' => 'CleanerApp',
                'mocean-to'   => $phone,
                'mocean-text' => $message,
            ]);

        return $response->json();
    }
}