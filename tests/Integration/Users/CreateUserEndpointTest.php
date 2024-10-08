<?php

declare(strict_types=1);

namespace Tests\Integration\Auth;

use App\Enums\ResponseStatus;
use App\Enums\UserType;
use Symfony\Component\HttpFoundation\Response;
use tests\Integration\BaseWebTestCase;

describe('POST /users', function () {
    it('rejects with invalid payload data', function (array $payload, string $errorMessage) {
        $this->postJson(
            getUrl(BaseWebTestCase::CREATE_USER_ROUTE_NAME), $payload,
        )
            ->assertStatus(ResponseStatus::HTTP_BAD_REQUEST->value)
            ->assertJson(
                [
                    'status' => ResponseStatus::HTTP_BAD_REQUEST->value,
                    'message' => $errorMessage,
                ]
            );
    })->with(
        [
            [
                [
                    'email' => 'Test',
                    'first_name' => fake()->firstName,
                    'password' => fake()->password,
                ],
                'The email field must be a valid email address.',
            ],
            [
                [
                    'email' => fake()->email,
                    'password' => fake()->password,
                ],
                'The first name field is required.',
            ],
            [
                [
                    'email' => fake()->email,
                    'first_name' => fake()->firstName,
                ],
                'The password field is required.',
            ],
            [
                [],
                'The email field is required. (and 2 more errors)',
            ],
            [
                [
                    'email' => 'test@test.com',
                    'first_name' => fake()->firstName,
                    'password' => fake()->password,
                ],
                'The email has already been taken.',
            ],
        ]);

    it('creates new user with valid payload', function () {
        $mockEmail = fake()->email;
        $mockFirstName = fake()->firstName;
        $mockLastName = fake()->lastName;
        $this->postJson(
            getUrl(BaseWebTestCase::CREATE_USER_ROUTE_NAME),
            [
                'email' => $mockEmail,
                'first_name' => $mockFirstName,
                'last_name' => $mockLastName,
                'password' => fake()->password,
                'type' => UserType::USER->value,
            ],
        )
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJsonPath('data.first_name', $mockFirstName)
            ->assertJsonPath('data.last_name', $mockLastName)
            ->assertJsonPath('data.type', UserType::USER->value)
            ->assertJsonPath('data.email', $mockEmail);
    });
})->group('users');
