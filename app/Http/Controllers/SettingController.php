<?php

namespace App\Http\Controllers;

use App\Enums\SettingTypeEnum;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Collection;

class SettingController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(SettingTypeEnum $setting_type): Collection
    {
        return Setting::where('active', true)
            ->where('key', 'like', "{$setting_type->value}_%")
            ->get();
    }
}
