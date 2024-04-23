<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\DependsExternal;
use Tests\TestCase;
use Tests\Traits\AuthTrait;
use Tests\Traits\EmissionTrait;

class FlightTest extends TestCase
{
    use RefreshDatabase, AuthTrait, EmissionTrait;
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

        $this->checkTravelResponseSuccessFormat($responseSquake);

        $responseDB = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->postJson('api/flight', $payload);

        $this->checkTravelResponseSuccessFormat($responseDB);
        $this->assertJsonStringEqualsJsonString(json_encode($responseSquake->json()), json_encode($responseDB->json()));
    }

    #[DependsExternal(AuthTest::class, 'test_login')]
    public function test_search_flight_failed(): void
    {
        $this->token = $this->getToken();
        $payload = [
            [
                "origin" => "CG"
            ]
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->postJson('api/flight', $payload);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonStructure([
                'success',
                'message',
                'error'
            ])
            ->assertJson([
                'success' => false,
                'message' => 'Validation Error'
            ]);
    }
}
