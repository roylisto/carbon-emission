<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\FlightRoute;

use App\Http\Resources\FlightRouteResource;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class FlightRouteController extends Controller
{
    public function calculate(Request $request)
    {
        $squakeToken = env('SQUAKE_TOKEN');
        $squakeUrl = env('SQUAKE_URL');

        $validator = Validator::make($request->all(), [
            'origin' => 'required|string|max:3',
            'destination' => 'required|string|max:3',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $origin = $request->input('origin');
        $destination = $request->input('destination');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $squakeToken,
            'Content-Type' => 'application/json',
        ])->post($squakeUrl, [
            'expand' => ['items'],
            'items' => [
                [
                    'type' => 'flight',
                    'methodology' => 'ademe',
                    'number_of_travelers' => 1,
                    'origin' => $origin,
                    'destination' => $destination
                ]
            ],
        ]);

        return new FlightRouteResource(true, 'Emission from ' . $origin . ' to ' . $destination, $response->json());
    }
}
