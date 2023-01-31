<?php

namespace Tests\Feature;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{

    /** @test */
    public function auth_test()
    {
        $response = $this->getJson('/api/tested');

        $response->assertCreated()
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->hasAll(['code', 'message', 'data'])
            )
            ->assertJsonPath('data', ['name' => 'Furniture', 'slug' => 'furniture']);
    }

    /** @test */
    public function vld_test()
    {
        $response = $this->getJson('/api/test-vld');

        $response->assertStatus(422)
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->hasAll(['code', 'message', 'errors'])
            )
            ->assertInvalid(['name'])
            ->assertJsonCount(2, 'errors');
    }
}
