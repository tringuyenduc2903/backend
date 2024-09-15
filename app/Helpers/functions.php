<?php

use App\Models\Customer;
use App\Models\Employee;
use App\Models\Setting;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Auth\RequestGuard;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Factory;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Foundation\Application;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

if (! function_exists('revoke_token')) {
    function revoke_token($token_name): mixed
    {
        return fortify_user()->tokens()->whereName($token_name)->delete();
    }
}

if (! function_exists('regenerate_token')) {
    function regenerate_token($token_name): string
    {
        revoke_token($token_name);

        return fortify_user()->createToken($token_name)->plainTextToken;
    }
}

if (! function_exists('fortify_auth')) {
    function fortify_auth(): Factory|StatefulGuard|Application|RequestGuard
    {
        return auth(config('fortify.guard_auth'));
    }
}

if (! function_exists('fortify_user')) {
    function fortify_user(): Customer|Authenticatable
    {
        return fortify_auth()->user();
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

if (! function_exists('set_title')) {
    function set_title(string $column = 'name'): void
    {
        if (! $entry = CRUD::getCurrentEntry()) {
            return;
        }

        $value = $entry->getAttribute($column);

        CRUD::setTitle($value);
        CRUD::setHeading($value);
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

if (! function_exists('current_currency')) {
    function current_currency(): string
    {
        return Setting::where('key', 'store_currency')
            ->firstOrFail()
            ->getAttribute('value');
    }
}

if (! function_exists('store_image')) {
    function store_image(string $storage_path, string $url): bool
    {
        if (File::exists("$storage_path/$url")) {
            return false;
        }

        $directories = explode('/', $url);

        $temp_storage_path = $storage_path;

        for ($i = 0; $i < count($directories) - 1; $i++) {
            $temp_storage_path .= "/$directories[$i]";

            if (! File::exists($temp_storage_path)) {
                File::makeDirectory($temp_storage_path);
            }
        }

        return File::put(
            "$storage_path/$url",
            Http::get($url)->body()
        );
    }
}

if (! function_exists('product_image_url')) {
    function product_image_url(string $path): string
    {
        return asset(sprintf(
            '%s/%s',
            config('filesystems.disks.product.url'),
            $path
        ));
    }
}

if (! function_exists('category_image_url')) {
    function category_image_url(string $path): string
    {
        return asset(sprintf(
            '%s/%s',
            config('filesystems.disks.category.url'),
            $path
        ));
    }
}

if (! function_exists('branch_image_url')) {
    function branch_image_url(string $path): string
    {
        return asset(sprintf(
            '%s/%s',
            config('filesystems.disks.branch.url'),
            $path
        ));
    }
}

if (! function_exists('review_image_url')) {
    function review_image_url(string $path): string
    {
        return asset(sprintf(
            '%s/%s',
            config('filesystems.disks.review.url'),
            $path
        ));
    }
}

if (! function_exists('image_preview')) {
    function image_preview(string $image, string $alt = ''): array
    {
        return [
            'url' => $image,
            'alt' => $alt,
        ];
    }
}

if (! function_exists('price_preview')) {
    function price_preview(float $price): array
    {
        return [
            'raw' => $price,
            'preview' => Number::currency($price, current_currency(), app()->currentLocale()),
        ];
    }
}

if (! function_exists('percent_preview')) {
    function percent_preview(float $percent): array
    {
        return [
            'raw' => $percent,
            'preview' => Number::percentage($percent),
        ];
    }
}
