<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Resources\FlightRouteResource;
use App\Models\Emission;
use App\Models\FlightRoute;
use App\Services\Squake;
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
        $items = $request->all();

        $validator = Validator::make($items, [
            '*.origin' => 'required|string|max:3',
            '*.destination' => 'required|string|max:3',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        foreach ($items as &$item) {
            $item['type'] = 'flight';
        }

        $squakeResponse = $this->squake->calculate([
            'expand' => ['items'],
            'items' => $items
        ]);

        // save flight route
        foreach ($items as $key => &$item) {

            $res = $squakeResponse['items'][$key];
            $res['carbon_quantity'] = (int) ($res['carbon_quantity'] / $item['number_of_travelers']);

            // store to emission table
            $emission = Emission::create($res);

            // store to flight route
            FlightRoute::create([
                'origin' => $item['origin'],
                'destination' => $item['destination'],
                'methodology' => $item['methodology'] ?? null,
                'emission_id' => $emission->id,
            ]);
        }

        return new FlightRouteResource(true, 'Flight Emission Calculation ', $squakeResponse->json());
    }
}
