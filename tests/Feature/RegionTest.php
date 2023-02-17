<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Province;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class RegionTest extends TestCase
{

    public Province $province;
    public function setUp(): void
    {
        parent::setUp();
        $this->province = Province::factory()->create();
        City::factory()->create(['province_id' => $this->province->id]);
        City::factory()->create(['province_id' => $this->province->id]);
        City::factory()->create(['province_id' => $this->province->id]);
    }

    /** @test */
    public function show_all_provinces()
    {
        $res = $this->getJson(route('region.provinces'), [], $this->header);

        $res->assertOk()
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->hasAll(['code', 'message', 'data'])
                    ->count('data', 1)
            );
    }

    /** @test */
    public function show_the_cities_by_province_id()
    {
        $res = $this->getJson(route('region.cities', $this->province->id), [], $this->header);

        $res->assertOk()
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->hasAll(['code', 'message', 'data'])
                    ->count('data', 3)
            );
    }

    /** @test */
    public function show_all_couriers()
    {
        $res = $this->getJson(route('region.couriers'), [], $this->header);

        $res->assertOk()
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->hasAll(['code', 'message', 'data'])
                    ->count('data', 3)
            );
    }
}
