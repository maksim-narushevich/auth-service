<?php

declare(strict_types=1);

namespace Tests\Integration\Auth;

use App\Enums\ResponseStatus;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RefreshTokenEndpointTest extends BaseWebTestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        User::factory()->create(['email' => 'test@test.com', 'password' => Hash::make('pass')]);
    }

    public function testUnauthorizedResponse(): void
    {
        $response = $this->post($this->getUrl(self::REFRESH_TOKEN_ROUTE_NAME));

        $response->assertJson(['status' => ResponseStatus::UNAUTHORIZED->value, 'message' => 'Unauthenticated.']);
        $response->assertStatus(ResponseStatus::UNAUTHORIZED->value);
    }

    public function testSuccessResponse(): void
    {
        $response = $this->post($this->getUrl(self::LOGIN_ROUTE_NAME), ['email' => 'test@test.com', 'password' => 'pass']);
        $response = $this->post(
            $this->getUrl(self::REFRESH_TOKEN_ROUTE_NAME),
            headers: ['Authorization' => sprintf('Bearer %s', $this->getResponseData($response)['access_token'])]
        );

        $response->assertOk();
        $responseContent = $this->getResponseData($response);
        $this->assertArrayHasKey('access_token', $responseContent);
        $this->assertEquals('bearer', $responseContent['token_type']);
        $this->assertEquals(3600, $responseContent['expires_in']);
    }
}
