<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\DependsExternal;
use Tests\TestCase;
use Tests\Traits\AuthTrait;
use Tests\Traits\EmissionTrait;

class HotelTest extends TestCase
{
    use RefreshDatabase, AuthTrait, EmissionTrait;
    protected $token;

    #[DependsExternal(AuthTest::class, 'test_login')]
    public function test_search_hotel(): void
    {
        $this->token = $this->getToken();
        $payload = [
            [
                "number_of_nights" => 1,
                "methodology" => "BASIC",
                "stars" => 4,
                "country" => "AU",
                "external_reference" => "test_1",
                "room_type" => "adjacent_room"
            ],
            [
                "number_of_nights" => 1,
                "methodology" => "SQUAKE",
                "stars" => 4,
                "country" => "AU",
                "external_reference" => "test_1",
                "room_type" => "twin"
            ]
        ];

        $responseSquake = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ])->postJson('api/hotel', $payload);

        $this->checkHotelResponseSuccessFormat($responseSquake);

        // $responseDB = $this->withHeaders([
        //     'Authorization' => 'Bearer ' . $this->token,
        //     'Accept' => 'application/json'
        // ])->postJson('api/hotel', $payload);

        // $this->checkResponseSuccessFormat($responseDB);
        // $this->assertJsonStringEqualsJsonString(json_encode($responseSquake->json()), json_encode($responseDB->json()));
    }

    // #[DependsExternal(AuthTest::class, 'test_login')]
    // public function test_search_train_failed(): void
    // {
    //     $this->token = $this->getToken();
    //     $payload = [
    //         [
    //             "origin" => "OR"
    //         ]
    //     ];

    //     $response = $this->withHeaders([
    //         'Authorization' => 'Bearer ' . $this->token,
    //         'Accept' => 'application/json'
    //     ])->postJson('api/train', $payload);

    //     $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
    //         ->assertJsonStructure([
    //             'success',
    //             'message',
    //             'error'
    //         ])
    //         ->assertJson([
    //             'success' => false,
    //             'message' => 'Validation Error'
    //         ]);
    // }
}
