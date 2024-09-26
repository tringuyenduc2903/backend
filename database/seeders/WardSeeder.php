<?php

namespace Database\Seeders;

use App\Facades\Ghn;
use App\Models\District;
use Illuminate\Database\Seeder;

class WardSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        District::each(function (District $district) {
            $wards = array_map(
                fn (array $ward): array => [
                    'name' => $ward['WardName'],
                    'name_extensions' => json_encode(
                        $ward['NameExtension'] ?? [],
                        JSON_UNESCAPED_UNICODE
                    ),
                    'ghn_id' => $ward['WardCode'],
                ],
                Ghn::wardCache($district->ghn_id) ?? []
            );

            $district->wards()->upsert($wards, 'ghn_id');
        });
    }
}
