<?php

namespace Tests\Traits;

trait AuthTrait
{
    protected function getToken(): string
    {
        $payload = [
            "name" => "test",
            "email" => "test@test.com",
            "password" => "123456",
            "c_password" => "123456"
        ];

        // Register user
        $registerResponse = $this->postJson('api/register', $payload);

        return $registerResponse->json('data.token');
    }
}
