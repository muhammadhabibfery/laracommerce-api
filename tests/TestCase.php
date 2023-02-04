<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Sanctum\Sanctum;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, RefreshDatabase;

    public const RESET_PASSWORD_TOKEN = 'abc123';

    public array $header = [
        'Content-Type' => 'application/json',
        'Accept' => 'application/json'
    ];

    /**
     * Create a user instance.
     *
     * @param  array $data
     * @return User
     */
    public function createUser(?array $data = []): User
    {
        return User::factory()->create($data);
    }

    /**
     * Create authenticated user.
     *
     * @param  array $data
     * @return User
     */
    public function authenticatedUser(?array $data = []): User
    {
        $user = $this->createUser($data);
        Sanctum::actingAs($user, ['*']);
        return $user;
    }
}
