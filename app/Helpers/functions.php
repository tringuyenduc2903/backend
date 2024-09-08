<?php

use App\Models\Employee;
use App\Models\Setting;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

if (! function_exists('revoke_token')) {
    function revoke_token($token_name): mixed
    {
        return request()->user()->tokens()->whereName($token_name)->delete();
    }
}

if (! function_exists('regenerate_token')) {
    function regenerate_token($token_name): string
    {
        revoke_token($token_name);

        return request()->user()->createToken($token_name)->plainTextToken;
    }
}

if (! function_exists('deny_access')) {
    function deny_access(string $permission): void
    {
        /** @var Employee $employee */
        $employee = backpack_user();

        if ($employee->hasPermissionTo(
            $permission,
            config('backpack.base.guard')
        )) {
            return;
        }

        CRUD::denyAllAccess();
    }
}

if (! function_exists('mb_ucwords')) {
    function mb_ucwords(?string $string): string
    {
        return mb_convert_case($string, MB_CASE_TITLE, 'UTF-8');
    }
}

if (! function_exists('handle_exception')) {
    function handle_exception(Exception $exception, string $class, string $function): void
    {
        Log::debug($exception->getMessage(), [
            'class' => $class,
            'function' => $function,
        ]);
    }
}

if (! function_exists('handle_api_call_failure')) {
    function handle_api_call_failure(Response $response, string $class, string $function): void
    {
        if ($response->failed()) {
            Log::debug('Call to API failed!', [
                'class' => $class,
                'function' => $function,
                'information' => $response->json(),
            ]);
        }
    }
}

if (! function_exists('handle_cache')) {
    function handle_cache(callable $callback, string $cache_key, int $cache_time): mixed
    {
        return Cache::get(
            $cache_key,
            function () use ($callback, $cache_key, $cache_time) {
                Cache::set($cache_key, $data = $callback(), $cache_time);

                return $data;
            }
        );
    }
}

if (! function_exists('currency_symbol')) {
    /**
     * @throws Exception
     */
    function currency_symbol(): string
    {
        $currency = Setting::get('store_currency');

        if (is_null($currency)) {
            throw new Exception('Currency symbol not configured.');
        }

        return json_decode($currency)->symbol;
    }
}

if (! function_exists('currency_code')) {
    /**
     * @throws Exception
     */
    function currency_code(): string
    {
        $currency = Setting::get('store_currency');

        if (is_null($currency)) {
            throw new Exception('Currency code not configured.');
        }

        return json_decode($currency)->code;
    }
}
