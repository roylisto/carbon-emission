<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Resources\FlightRouteResource;
use App\Models\Emission;
use App\Models\FlightRoute;
use App\Services\Squake;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class FlightRouteController extends Controller
{
    protected $squake;

    public function __construct(Squake $squake)
    {
        $this->squake = $squake;
    }

    public function calculate(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            '*.origin' => 'required|string|max:3',
            '*.destination' => 'required|string|max:3',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $outputItems = [];
        $checkOnSquake = [];
        $totalCarbonQuantity = 0;
        foreach ($input as $key => $item) {
            $item['type'] = 'flight';

            // find data in db
            $routeEmission = FlightRoute::findByOriginDestinationMethodology(
                $item['origin'],
                $item['destination'],
                isset($item['methodology']) ? $item['methodology'] : 'MYCLIMATE'
            );

            if (empty($routeEmission)) {
                array_push($checkOnSquake, $item);
            } else {
                $routeEmission['emission']['carbon_quantity'] = $routeEmission['emission']['carbon_quantity'] * $item['number_of_travelers'];
                $totalCarbonQuantity += $routeEmission['emission']['carbon_quantity'];
                array_push($outputItems, $routeEmission);
            }
        }

        // if some data not exist in DB then call squake API
        if (!empty($checkOnSquake)) {
            $squakeResponse = $this->squake->calculate([
                'expand' => ['items'],
                'items' => $checkOnSquake
            ]);

            if (isset($squakeResponse['errors'])) {
                return new FlightRouteResource(false, 'Flight Emission Calculation ', $squakeResponse['errors']);
            }

            // save new flight route emission
            foreach ($squakeResponse['items'] as $key => $item) {

                $check = $checkOnSquake[$key];
                $item['carbon_quantity'] = (int) ($item['carbon_quantity'] / $check['number_of_travelers']);

                try {
                    DB::beginTransaction();
                    // store to emission table
                    $emission = Emission::create($item);

                    // store to flight route
                    $insertData = FlightRoute::create([
                        'origin' => strtoupper($check['origin']),
                        'destination' => strtoupper($check['destination']),
                        'methodology' => isset($check['methodology']) ? strtoupper($check['methodology']) : 'MYCLIMATE',
                        'emission_id' => $emission->id,
                    ])->load('emission');
                    DB::commit();

                    $newRouteEmission = FlightRoute::with('emission')->find($insertData->id);
                    $newRouteEmission['emission']['carbon_quantity'] = $newRouteEmission['emission']['carbon_quantity'] * $check['number_of_travelers'];
                    $totalCarbonQuantity += $newRouteEmission['emission']['carbon_quantity'];
                    array_push($outputItems, $newRouteEmission);
                } catch (\Exception $e) {
                    DB::rollBack();
                    return new FlightRouteResource(false, 'Flight Emission Calculation', $e->getMessage());
                }
            }
        }

        $outputResponse = [
            "total_carbon_quantity" => $totalCarbonQuantity,
            "carbon_unit" => "gram",
            "items" => $outputItems
        ];

        return new FlightRouteResource(true, 'Flight Emission Calculation ', $outputResponse);
    }
}
