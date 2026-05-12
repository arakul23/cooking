<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Vegetables',
            'Fruits',
            'Meat',
            'Dairy',
            'Bakery',
            'Spices',
        ];

        foreach ($categories as $category) {
            ProductCategory::query()->create([
                'name' => $category,
            ]);
        }
    }
}
