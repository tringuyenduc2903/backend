<?php

namespace Database\Seeders;

use App\Facades\GHNv2Cache;
use App\Models\Province;
use Illuminate\Database\Seeder;

class ProvinceSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $provinces = array_map(
            fn (array $province): array => [
                'name' => $province['ProvinceName'],
                'name_extensions' => json_encode(
                    $province['NameExtension'] ?? [],
                    JSON_UNESCAPED_UNICODE
                ),
                'ghn_id' => $province['ProvinceID'],
            ],
            GHNv2Cache::province()
        );

        Province::upsert($provinces, 'ghn_id');
    }
}
