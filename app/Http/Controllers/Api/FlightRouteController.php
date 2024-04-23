<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\ResponseResource;
use App\Services\Squake;
use App\Traits\TravelEmissionCalculationTrait;

class FlightRouteController extends Controller
{
    use TravelEmissionCalculationTrait;

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

        $outputResponse = $this->calculateEmission($input, 'App\Models\FlightRoute');

        return new ResponseResource(true, 'Flight Emission Calculation', $outputResponse);
    }
}
