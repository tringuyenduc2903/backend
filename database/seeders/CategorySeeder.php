<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class CategorySeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $categories = [];

        foreach (File::files(storage_path('app/products')) as $file) {
            $product = json_decode($file->getContents());

            $categories = array_merge($categories, $product->categories);
        }

        $categories = array_unique($categories);

        sort($categories);

        foreach ($categories as $category) {
            Category::updateOrCreate([
                'name' => $category,
            ]);
        }
    }
}
