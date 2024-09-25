<?php

use App\Enums\OptionStatus;
use App\Enums\ProductType;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Option;
use App\Models\Product;
use App\Models\Setting;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Auth\RequestGuard;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Factory;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Application;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Validator;

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

if (! function_exists('handle_validate_failure')) {
    /**
     * @throws Exception
     */
    function handle_validate_failure(Validator $validator): void
    {
        if ($validator->fails()) {
            throw new Exception($validator->errors()->first());
        }
    }
}

if (! function_exists('handle_ghn_api')) {
    /**
     * @throws Exception
     */
    function handle_ghn_api(Response $response): void
    {
        if ($response->failed()) {
            Log::debug($response->json('message'), $response->json() ?? []);

            throw new Exception($response->json('message'));
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

if (! function_exists('current_user')) {
    function current_user(int $customer_id): Customer|Employee
    {
        if (backpack_auth()->check()) {
            $customer = backpack_user();
        } elseif (fortify_auth()->check()) {
            $customer = fortify_user();
        } else {
            $customer = Customer::findOrFail($customer_id);
        }

        return $customer;
    }
}

if (! function_exists('current_currency')) {
    function current_currency(): string
    {
        return Setting::where('key', 'store_currency')
            ->firstOrFail()
            ->getAttribute('value_preview');
    }
}

if (! function_exists('current_store')) {
    function current_store(): int
    {
        $value = json_decode(
            Setting::where('key', 'store_ghn')
                ->firstOrFail()
                ->getAttribute('value')
        )->value;

        return json_decode($value)->shop_id;
    }
}

if (! function_exists('current_store_district')) {
    function current_store_district(): int
    {
        $value = json_decode(
            Setting::where('key', 'store_ghn')
                ->firstOrFail()
                ->getAttribute('value')
        )->value;

        return json_decode($value)->district_id;
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

if (! function_exists('image_url')) {
    function image_url(string $base_url, string $path): string
    {
        return asset(
            sprintf('%s/%s', $base_url, $path)
        );
    }
}

if (! function_exists('product_image_url')) {
    function product_image_url(string $path): string
    {
        return image_url(
            config('filesystems.disks.product.url'),
            $path
        );
    }
}

if (! function_exists('category_image_url')) {
    function category_image_url(string $path): string
    {
        return image_url(
            config('filesystems.disks.category.url'),
            $path
        );
    }
}

if (! function_exists('branch_image_url')) {
    function branch_image_url(string $path): string
    {
        return image_url(
            config('filesystems.disks.branch.url'),
            $path
        );
    }
}

if (! function_exists('review_image_url')) {
    function review_image_url(string $path): string
    {
        return image_url(
            config('filesystems.disks.review.url'),
            $path
        );
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

if (! function_exists('price')) {
    function price(float $price): string
    {
        return Number::currency(
            $price,
            current_currency(),
            app()->currentLocale()
        );
    }
}

if (! function_exists('price_preview')) {
    function price_preview(float $price): array
    {
        return [
            'raw' => $price,
            'preview' => price($price),
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

if (! function_exists('phone_number_search_logic')) {
    function phone_number_search_logic(Builder $query, string $search_term): Builder
    {
        return $query->orWhereHas(
            'customer',
            fn (Builder $query): Builder => $query->whereLike(
                'phone_number',
                "%$search_term%"
            )
        );
    }
}

if (! function_exists('customer_phone_number_search_logic')) {
    function customer_phone_number_search_logic(Builder $query, string $search_term): Builder
    {
        return $query->orWhereHas(
            'address',
            fn (Builder $query): Builder => $query->whereLike(
                'customer_phone_number',
                "%$search_term%"
            )
        );
    }
}

if (! function_exists('get_product')) {
    function get_product(?int $option_id, $ignore_motor_cycle = true): ?Option
    {
        if (! $option_id) {
            return null;
        }

        return Option::whereId($option_id)
            ->whereStatus(OptionStatus::IN_STOCK)
            ->whereHas(
                'product',
                function (Builder $query) use ($ignore_motor_cycle) {
                    /** @var Product $query */
                    $query->wherePublished(true);

                    if ($ignore_motor_cycle) {
                        $query->whereNot('type', ProductType::MOTOR_CYCLE);
                    }
                }
            )
            ->first();
    }
}
