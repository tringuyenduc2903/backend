<?php

namespace Database\Seeders;

use App\Models\Review;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Review::factory(25)->review()
            ->has(Review::factory()->reply(), 'reply')
            ->create();
    }
}
