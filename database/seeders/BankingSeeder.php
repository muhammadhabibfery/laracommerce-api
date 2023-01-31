<?php

namespace Database\Seeders;

use App\Models\Banking;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BankingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $bankings = [
            ['name' => 'PT. BANK CENTRAL ASIA TBK.', 'alias' => 'bca', 'created_by' => getUserWithRole('employee')->id],
            ['name' => 'PT. BANK NEGARA INDONESIA (PERSERO)', 'alias' => 'bni', 'created_by' => getUserWithRole('employee')->id],
            ['name' => 'PT. BANK RAKYAT INDONESIA (PERSERO)', 'alias' => 'bri', 'created_by' => getUserWithRole('employee')->id],
            ['name' => 'PT. BANK MANDIRI (PERSERO) TBK.', 'alias' => 'mandiri', 'created_by' => getUserWithRole('employee')->id]
        ];

        foreach ($bankings as $banking)
            Banking::create($banking);
    }
}
