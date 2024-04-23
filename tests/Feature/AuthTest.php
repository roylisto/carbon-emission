<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_register(): void
    {
        $payload = [
            "name" => "test",
            "email" => "test@test.com",
            "password" => "123456",
            "c_password" => "123456"
        ];

        $response = $this->postJson('api/register', $payload);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token'
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'User registered successfully',
            ]);
        // Check if the token is not empty
        $token = $response->json('data.token');
        $this->assertNotEmpty($token);
    }

    public function test_register_with_same_data(): void
    {
        $this->test_register();

        $payload = [
            "name" => "test",
            "email" => "test@test.com",
            "password" => "123456",
            "c_password" => "123456"
        ];

        $response = $this->postJson('api/register', $payload);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonStructure([
                'success',
                'message',
                'error' => [
                    'email'
                ]
            ])
            ->assertJson([
                'success' => false,
                'message' => 'Validation Error',
                'error' => [
                    'email' => [
                        'The email has already been taken.'
                    ]
                ]
            ]);
    }

    public function test_login(): void
    {
        $this->test_register();
        $payload = [
            "email" => "test@test.com",
            "password" => "123456",
        ];

        $response = $this->postJson('api/login', $payload);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token'
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'User login successfully',
            ]);
        // Check if the token is not empty
        $token = $response->json('data.token');
        $this->assertNotEmpty($token);
    }

    public function test_login_failed(): void
    {
        $this->test_register();
        $payload = [
            "email" => "test2@test.com",
            "password" => "123456",
        ];

        $response = $this->postJson('api/login', $payload);

        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJsonStructure([
                'success',
                'message',
                'error'
            ])
            ->assertJson([
                'success' => false,
                'message' => 'Auth Error',
                'error' => 'Email or Password is wrong'
            ]);
    }
}
