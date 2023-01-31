<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * create a user instance
     *
     * @param  array $data
     * @return User
     */
    public function createUser(?array $data = []): User
    {
        return User::factory()->create($data);
    }
}
