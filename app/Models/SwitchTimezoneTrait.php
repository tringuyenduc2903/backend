<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Support\Facades\Date;

trait SwitchTimezoneTrait
{
    protected function serializeDate(DateTimeInterface $date): string
    {
        if (backpack_auth()->check()) {
            return $date;
        }

        $timezone = fortify_auth()->check()
            ? fortify_user()->timezone_preview
            : config('app.timezone');

        // If this value is already a Carbon instance, we shall just return it as is.
        // This prevents us having to re-instantiate a Carbon instance when we know
        // it already is one, which wouldn't be fulfilled by the DateTime check.
        return Date::instance($date)
            ->timezone($timezone)
            ->format('H:m:s d/m/Y');
    }
}
