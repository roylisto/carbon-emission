<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use App\Models\Emission;
use App\Http\Resources\ResponseResource;

trait TravelEmissionCalculationTrait
{
    protected function calculateEmission($input, $routeModel, $additionalValidation = [])
    {
        $commonValidationRules = [
            '*.origin' => 'required|string',
            '*.destination' => 'required|string',
            '*.number_of_travelers' => 'required|integer|min:1',
        ];

        // Add methodology validation for train routes
        if ($routeModel === 'App\Models\TrainRoute') {
            $validationRules['*.methodology'] = 'required|string';
        }
        $validationRules = array_merge($commonValidationRules, $additionalValidation);

        $dataToCheck = [];
        $totalCarbonQuantity = 0;
        $outputItems = [];

        foreach ($input as $key => $item) {
            $item['type'] = $routeModel === 'App\Models\TrainRoute' ? 'train' : 'flight';
            $dataToCheck[$key] = $item;

            if ($routeModel === 'App\Models\TrainRoute') {
                $defaultMethodology = 'BASIC';
            } else {
                $defaultMethodology = 'MYCLIMATE';
            }

            $methodology = $item['methodology'] ?? $defaultMethodology;

            $routeEmission = $routeModel::findBy(
                $item['origin'],
                $item['destination'],
                $methodology,
                $routeModel === 'App\Models\TrainRoute' ? $item['train_type'] : null
            );

            if (!empty($routeEmission)) {
                $routeEmission['emission']['carbon_quantity'] *= $item['number_of_travelers'];
                $totalCarbonQuantity += $routeEmission['emission']['carbon_quantity'];
                $outputItems[] = $routeEmission;
                unset($dataToCheck[$key]);
            }
        }

        $dataToCheck = array_values($dataToCheck);
        if (!empty($dataToCheck)) {
            // Call Squake API
            $squakeResponse = $this->squake->calculate([
                'expand' => ['items'],
                'items' => $dataToCheck,
            ]);

            if (isset($squakeResponse['errors'])) {
                return new ResponseResource(false, 'Emission Calculation', $squakeResponse['errors']);
            }

            try {
                DB::beginTransaction();
                foreach ($squakeResponse['items'] as $key => $item) {
                    $check = $dataToCheck[$key];

                    $item['carbon_quantity'] = (int) ($item['carbon_quantity'] / $check['number_of_travelers']);

                    if ($routeModel === 'App\Models\TrainRoute') {
                        $defaultMethodology = 'BASIC';
                    } else {
                        $defaultMethodology = 'MYCLIMATE';
                    }

                    $item['methodology'] = $item['methodology'] ?? $defaultMethodology;

                    $emission = Emission::create($item);
                    $insertData = $routeModel::create([
                        'origin' => strtoupper($check['origin']),
                        'destination' => strtoupper($check['destination']),
                        'methodology' => strtoupper($item['methodology']),
                        'train_type' => $routeModel === 'App\Models\TrainRoute' ? strtoupper($check['train_type']) : null,
                        'emission_id' => $emission->id,
                    ])->load('emission');

                    $newRouteEmission = $routeModel::with('emission')->find($insertData->id);
                    $newRouteEmission['emission']['carbon_quantity'] *= $check['number_of_travelers'];
                    $totalCarbonQuantity += $newRouteEmission['emission']['carbon_quantity'];
                    $outputItems[] = $newRouteEmission;
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return new ResponseResource(false, 'Emission Calculation', $e->getMessage());
            }
        }

        return [
            'total_carbon_quantity' => $totalCarbonQuantity,
            'carbon_unit' => 'gram',
            'items' => $outputItems
        ];
    }
}
