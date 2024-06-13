<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run()
    {
        Category::create([
            'name' => 'پروگرمر و بردهای آموزشی',
            'slug' => 'programmer',
            'parent_id' => null,
            'image' => ''
        ]);

        Category::create([
            'name' => 'قطعات الکترونیکی',
            'slug' => 'electronic_components',
            'parent_id' => null,
            'image' => ''
        ]);

        Category::create([
            'name' => 'مینی کامپیوتر Mini PC',
            'slug' => 'mini_pc',
            'parent_id' => 1,
            'image' => ''
        ]);

        Category::create([
            'name' => 'مقاومت',
            'slug' => 'resistance',
            'parent_id' => 2,
            'image' => ''
        ]);
    }
}
