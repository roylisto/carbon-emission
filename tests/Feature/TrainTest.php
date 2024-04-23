<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\DependsExternal;
use Tests\TestCase;
use Tests\Traits\AuthTrait;
use Tests\Traits\EmissionTrait;

class TrainTest extends TestCase
{
    use RefreshDatabase, AuthTrait, EmissionTrait;
    protected $token;

    #[DependsExternal(AuthTest::class, 'test_login')]
    public function test_search_train(): void
    {
        $this->token = $this->getToken();
        $payload = [
            [
                "origin" => "ORY",
                "destination" => "NICE",
                "number_of_travelers" => 1,
                "train_type" => "high_speed",
                "methodology" => "ADEME"
            ],
            [
                "origin" => "fra",
                "destination" => "ber",
                "number_of_travelers" => 2,
                "train_type" => "high_speed",
                "methodology" => "ADEME"
            ]
        ];

        $responseSquake = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->postJson('api/train', $payload);

        $this->checkTravelResponseSuccessFormat($responseSquake);

        $responseDB = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->postJson('api/train', $payload);

        $this->checkTravelResponseSuccessFormat($responseDB);
        $this->assertJsonStringEqualsJsonString(json_encode($responseSquake->json()), json_encode($responseDB->json()));
    }

    #[DependsExternal(AuthTest::class, 'test_login')]
    public function test_search_train_failed(): void
    {
        $this->token = $this->getToken();
        $payload = [
            [
                "origin" => "OR"
            ]
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->postJson('api/train', $payload);

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
