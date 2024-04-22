<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TrainRoute;
use Illuminate\Http\Request;
use App\Services\Squake;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\ResponseResource;
use App\Models\Emission;
use Illuminate\Support\Facades\DB;

class TrainRouteController extends Controller
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
            '*.origin' => 'required|string',
            '*.destination' => 'required|string',
            '*.number_of_travelers' => 'required|integer|min:1',
            '*.methodology' => 'required|string',
            '*.train_type' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $dataToCheck = [];
        $totalCarbonQuantity = 0;
        $outputItems = [];

        // Prepare data for DB and Squake calls in one loop
        foreach ($input as $key => $item) {
            $item['type'] = 'train';
            $dataToCheck[$key] = $item;

            $routeEmission = TrainRoute::findBy(
                $item['origin'],
                $item['destination'],
                isset($item['methodology']) ? $item['methodology'] : 'BASIC',
                $item['train_type']
            );

            if (!empty($routeEmission)) {
                $routeEmission['emission']['carbon_quantity'] = $routeEmission['emission']['carbon_quantity'] * $item['number_of_travelers'];
                $totalCarbonQuantity += $routeEmission['emission']['carbon_quantity'];
                $outputItems[] = $routeEmission;
                unset($dataToCheck[$key]); // Remove processed items from Squake check list
            }
        }

        // Call Squake API only if there's data left to check
        $dataToCheck = array_values($dataToCheck);
        if (!empty($dataToCheck)) {
            $squakeResponse = $this->squake->calculate([
                'expand' => ['items'],
                'items' => array_values($dataToCheck), // Ensure proper indexing
            ]);

            if (isset($squakeResponse['errors'])) {
                return new ResponseResource(false, 'Train Emission Calculation ', $squakeResponse['errors']);
            }

            // Process Squake response and save data
            try {
                DB::beginTransaction();
                foreach ($squakeResponse['items'] as $key => $item) {
                    $check = $dataToCheck[$key];
                    $item['carbon_quantity'] = (int) ($item['carbon_quantity'] / $check['number_of_travelers']);

                    $emission = Emission::create($item);
                    $insertData = TrainRoute::create([
                        'origin' => strtoupper($check['origin']),
                        'destination' => strtoupper($check['destination']),
                        'methodology' => isset($check['methodology']) ? strtoupper($check['methodology']) : 'BASIC',
                        'train_type' => strtoupper($check['train_type']),
                        'emission_id' => $emission->id,
                    ])->load('emission');

                    $newRouteEmission = TrainRoute::with('emission')->find($insertData->id);
                    $newRouteEmission['emission']['carbon_quantity'] = $newRouteEmission['emission']['carbon_quantity'] * $check['number_of_travelers'];
                    $totalCarbonQuantity += $newRouteEmission['emission']['carbon_quantity'];
                    $outputItems[] = $newRouteEmission;
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return new ResponseResource(false, 'Train Emission Calculation', $e->getMessage());
            }
        }

        $outputResponse = [
            "total_carbon_quantity" => $totalCarbonQuantity,
            "carbon_unit" => "gram",
            "items" => $outputItems
        ];

        return new ResponseResource(true, 'Train Emission Calculation ', $outputResponse);
    }
}
