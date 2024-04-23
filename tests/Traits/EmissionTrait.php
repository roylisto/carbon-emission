<?php

namespace Tests\Traits;

use Illuminate\Http\Response;

trait EmissionTrait
{
    private function checkTravelResponseSuccessFormat($response)
    {
        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'total_carbon_quantity',
                    'carbon_unit',
                    'items' => [
                        '*' => [
                            'id',
                            'methodology',
                            'origin',
                            'destination',
                            'emission_id',
                            'created_at',
                            'updated_at',
                            'emission' => [
                                'id',
                                'carbon_quantity',
                                'carbon_unit',
                                'type',
                                'methodology',
                                'distance',
                                'distance_unit',
                                'created_at',
                                'updated_at'
                            ]
                        ]
                    ]
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Emission Calculation'
            ]);

        // Assert specific data types inside the 'data' key
        $responseData = $response->json();
        $this->assertIsInt($responseData['data']['total_carbon_quantity']);
        $this->assertIsString($responseData['data']['carbon_unit']);
        $this->assertIsArray($responseData['data']['items']);

        // Assert specific data types inside the 'data' key
        $responseData = $response->json();

        // Assert specific data types inside the 'items' array
        foreach ($responseData['data']['items'] as $item) {
            $this->assertIsInt($item['id']);
            $this->assertIsString($item['methodology']);
            $this->assertIsString($item['origin']);
            $this->assertIsString($item['destination']);
            $this->assertIsInt($item['emission_id']);
            $this->assertIsString($item['created_at']);
            $this->assertIsString($item['updated_at']);

            // Assert data types inside the 'emission' key
            $this->assertIsInt($item['emission']['id']);
            $this->assertIsInt($item['emission']['carbon_quantity']);
            $this->assertIsString($item['emission']['carbon_unit']);
            $this->assertIsString($item['emission']['type']);
            $this->assertIsString($item['emission']['methodology']);
            $this->assertIsInt($item['emission']['distance']);
            $this->assertIsString($item['emission']['distance_unit']);
            $this->assertIsString($item['emission']['created_at']);
            $this->assertIsString($item['emission']['updated_at']);
        }
    }

    private function checkHotelResponseSuccessFormat($response)
    {
        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'total_carbon_quantity',
                    'carbon_unit',
                    'items' => [
                        '*' => [
                            'id',
                            'methodology',
                            'country',
                            'stars',
                            'hcmi_member',
                            'room_type',
                            'created_at',
                            'updated_at',
                            'emission' => [
                                'id',
                                'carbon_quantity',
                                'carbon_unit',
                                'type',
                                'methodology',
                                'distance',
                                'distance_unit',
                                'created_at',
                                'updated_at'
                            ]
                        ]
                    ]
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Emission Calculation'
            ]);

        // Assert specific data types inside the 'data' key
        $responseData = $response->json();
        $this->assertIsInt($responseData['data']['total_carbon_quantity']);
        $this->assertIsString($responseData['data']['carbon_unit']);
        $this->assertIsArray($responseData['data']['items']);


        $responseData = $response->json();

        // Assert specific data types inside the 'items' array
        foreach ($responseData['data']['items'] as $item) {
            $this->assertIsInt($item['id']);
            $this->assertIsString($item['methodology']);
            $this->assertIsString($item['country']);
            $this->assertIsInt($item['stars']);
            $this->assertIsInt($item['hcmi_member']);
            $this->assertIsString($item['room_type']);
            $this->assertIsInt($item['emission_id']);
            $this->assertIsString($item['created_at']);
            $this->assertIsString($item['updated_at']);

            // Assert data types inside the 'emission' key
            $this->assertIsInt($item['emission']['id']);
            $this->assertIsInt($item['emission']['carbon_quantity']);
            $this->assertIsString($item['emission']['carbon_unit']);
            $this->assertIsString($item['emission']['type']);
            $this->assertIsString($item['emission']['methodology']);
            $this->assertNull($item['emission']['distance']);
            $this->assertNull($item['emission']['distance_unit']);
            $this->assertIsString($item['emission']['created_at']);
            $this->assertIsString($item['emission']['updated_at']);
        }
    }
}
