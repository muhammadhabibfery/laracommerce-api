<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
            ['name' => 'Gadget', 'created_by' => getUserWithRole('employee')->id],
            ['name' => 'Furniture', 'created_by' => getUserWithRole('employee')->id],
            ['name' => 'Sneaker', 'created_by' => getUserWithRole('employee')->id]
        ];

        foreach ($categories as $category)
            Category::create($category);
    }
}
