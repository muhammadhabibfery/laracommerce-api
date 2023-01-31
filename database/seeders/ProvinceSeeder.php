<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Province;
use Illuminate\Database\Seeder;
use Kavist\RajaOngkir\Facades\RajaOngkir;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProvinceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $provinces = RajaOngkir::provinsi()->all();

        foreach ($provinces as $province) {
            $provinceResult = Province::create(['name' => $province['province']]);

            $cities = RajaOngkir::kota()->dariProvinsi($province['province_id'])->get();
            foreach ($cities as $city) {
                City::create([
                    'province_id' => $provinceResult['id'],
                    'name' => $city['city_name'],
                    'type' => $city['type'],
                    'postal_code' => $city['postal_code']
                ]);
            }
        }
    }
}
