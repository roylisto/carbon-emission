<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DependsExternal;
use Tests\TestCase;
use Tests\Traits\AuthTrait;

class FlightTest extends TestCase
{
    use RefreshDatabase, AuthTrait;
    protected $token;

    #[DependsExternal(AuthTest::class, 'test_login')]
    public function test_search_flight(): void
    {
        $this->token = $this->getToken();
        $payload = [
            [
                "origin" => "CGK",
                "destination" => "PLM",
                "external_reference" => "test",
                "number_of_travelers" => 1,
                "methodology" => "ICAO"
            ],
            [
                "origin" => "PLM",
                "destination" => "CGK",
                "external_reference" => "test",
                "number_of_travelers" => 1,
                "methodology" => "ICAO"
            ]
        ];


        $responseSquake = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->postJson('api/flight', $payload);

        $this->checkResponseFormat($responseSquake);

        $responseDB = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->postJson('api/flight', $payload);

        $this->checkResponseFormat($responseDB);
        $this->assertJsonStringEqualsJsonString(json_encode($responseSquake->json()), json_encode($responseDB->json()));
    }

    private function checkResponseFormat($response)
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
}
