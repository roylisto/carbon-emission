<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Hotel;
use App\Services\Squake;
use Illuminate\Support\Facades\DB;
use App\Models\Emission;

class HotelController extends Controller
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
            '*.number_of_nights' => 'required|int|min:1',

        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        return $this->calculateEmission($input);
    }

    private function calculateEmission($input)
    {
        $dataToCheck = [];
        $totalCarbonQuantity = 0;
        $outputItems = [];

        foreach ($input as $key => $item) {
            $item['type'] = 'hotel';
            $dataToCheck[$key] = $item;

            $methodology = $item['methodology'] ?? 'SQUAKE';

            $emission = Hotel::findBy(
                $methodology,
                $item['country'] ?? null,
                $item['stars'] ?? null,
                $item['hcmi_member'] ?? null,
                $item['room_type'] ?? null,
            );

            if (!empty($emission)) {
                $emission['emission']['carbon_quantity'] *= $item['number_of_nights'];
                $totalCarbonQuantity += $emission['emission']['carbon_quantity'];
                $outputItems[] = $emission;
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
                return new ResponseResource(false, 'Squake Error', $squakeResponse['errors']);
            }

            try {
                DB::beginTransaction();
                foreach ($squakeResponse['items'] as $key => $item) {
                    $check = $dataToCheck[$key];

                    $item['carbon_quantity'] = (int) ($item['carbon_quantity'] / $check['number_of_nights']);

                    $item['methodology'] = $item['methodology'] ?? 'SQUAKE';

                    $emission = Emission::create($item);
                    $insertData = Hotel::create([
                        'methodology' => strtoupper($item['methodology']),
                        'country' => isset($check['country']) ? strtoupper($check['country']) : null,
                        'stars' => isset($check['stars']) ? $check['stars'] : null,
                        'hcmi_member' => isset($check['hcmi_member']) ? $check['hcmi_member'] : false,
                        'room_type' => isset($check['room_type']) ? strtoupper($check['room_type']) : null,
                        'emission_id' => $emission->id,
                    ])->load('emission');

                    $newEmission = Hotel::with('emission')->find($insertData->id);
                    $newEmission['emission']['carbon_quantity'] *= $check['number_of_nights'];
                    $totalCarbonQuantity += $newEmission['emission']['carbon_quantity'];
                    $outputItems[] = $newEmission;
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return new ResponseResource(false, 'Hotel Emission Calculation DB Transaction', $e->getMessage());
            }
        }

        $outputResponse = [
            'total_carbon_quantity' => $totalCarbonQuantity,
            'carbon_unit' => 'gram',
            'items' => $outputItems
        ];

        return new ResponseResource(true, 'Hotel Emission Calculation', $outputResponse);
    }
}
