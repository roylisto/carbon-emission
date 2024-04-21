<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\FlightRoute;

use App\Http\Resources\FlightRouteResource;
use App\Services\Squake;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class FlightRouteController extends Controller
{
    protected $squake;

    public function __construct(Squake $squake)
    {
        $this->squake = $squake;
    }
    public function calculate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'origin' => 'required|string|max:3',
            'destination' => 'required|string|max:3',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $origin = $request->input('origin');
        $destination = $request->input('destination');

        $response = $this->squake->calculate([
            'expand' => ['items'],
            'items' => [
                [
                    'type' => 'flight',
                    'methodology' => 'ademe',
                    'number_of_travelers' => 1,
                    'origin' => $origin,
                    'destination' => $destination,
                ]
            ],
        ]);

        return new FlightRouteResource(true, 'Emission from ' . $origin . ' to ' . $destination, $response->json());
    }
}
