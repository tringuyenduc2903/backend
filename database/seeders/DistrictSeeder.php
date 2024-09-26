<?php

namespace Database\Seeders;

use App\Facades\Ghn;
use App\Models\Province;
use Illuminate\Database\Seeder;

class DistrictSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Province::each(function (Province $province) {
            $districts = array_map(
                fn (array $district): array => [
                    'name' => $district['DistrictName'],
                    'name_extensions' => json_encode(
                        $district['NameExtension'] ?? [],
                        JSON_UNESCAPED_UNICODE
                    ),
                    'ghn_id' => $district['DistrictID'],
                ],
                Ghn::districtCache($province->ghn_id)
            );

            $province->districts()->upsert($districts, 'ghn_id');
        });
    }
}
