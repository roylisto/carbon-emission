<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use App\Http\Resources\FlightRouteResource;
use App\Models\Emission;
use App\Models\FlightRoute;
use App\Services\Squake;

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
            '*.origin' => 'required|string|size:3',
            '*.destination' => 'required|string|size:3',
            '*.number_of_travelers' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $dataToCheck = [];
        $totalCarbonQuantity = 0;
        $outputItems = [];

        // Prepare data for DB and Squake calls in one loop
        foreach ($input as $key => $item) {
            $item['type'] = 'flight';
            $dataToCheck[$key] = $item;

            $routeEmission = FlightRoute::findByOriginDestinationMethodology(
                $item['origin'],
                $item['destination'],
                isset($item['methodology']) ? $item['methodology'] : 'MYCLIMATE'
            );

            if (!empty($routeEmission)) {
                $routeEmission['emission']['carbon_quantity'] = $routeEmission['emission']['carbon_quantity'] * $item['number_of_travelers'];
                $totalCarbonQuantity += $routeEmission['emission']['carbon_quantity'];
                $outputItems[] = $routeEmission;
                unset($dataToCheck[$key]); // Remove processed items from Squake check list
            }
        }

        // Call Squake API only if there's data left to check
        if (!empty($dataToCheck)) {
            $squakeResponse = $this->squake->calculate([
                'expand' => ['items'],
                'items' => array_values($dataToCheck), // Ensure proper indexing
            ]);

            if (isset($squakeResponse['errors'])) {
                return new FlightRouteResource(false, 'Flight Emission Calculation ', $squakeResponse['errors']);
            }

            // Process Squake response and save data
            try {
                DB::beginTransaction();
                foreach ($squakeResponse['items'] as $key => $item) {
                    $check = $dataToCheck[$key];
                    $item['carbon_quantity'] = (int) ($item['carbon_quantity'] / $check['number_of_travelers']);

                    $emission = Emission::create($item);
                    $insertData = FlightRoute::create([
                        'origin' => strtoupper($check['origin']),
                        'destination' => strtoupper($check['destination']),
                        'methodology' => isset($check['methodology']) ? strtoupper($check['methodology']) : 'MYCLIMATE',
                        'emission_id' => $emission->id,
                    ])->load('emission');

                    $newRouteEmission = FlightRoute::with('emission')->find($insertData->id);
                    $newRouteEmission['emission']['carbon_quantity'] = $newRouteEmission['emission']['carbon_quantity'] * $check['number_of_travelers'];
                    $totalCarbonQuantity += $newRouteEmission['emission']['carbon_quantity'];
                    $outputItems[] = $newRouteEmission;
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return new FlightRouteResource(false, 'Flight Emission Calculation', $e->getMessage());
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
