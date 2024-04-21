<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class Squake
{
    protected $baseCall;

    public function __construct()
    {
        $this->baseCall = Http::baseUrl(env('SQUAKE_URL'))
            ->withHeaders(([
                'Authorization' => 'Bearer ' . env('SQUAKE_TOKEN'),
                'Content-Type' => 'application/json',
            ]));
    }

    public function calculate($payload)
    {
        $response = $this->baseCall->post('/calculations', $payload);

        return $response;
    }
}
