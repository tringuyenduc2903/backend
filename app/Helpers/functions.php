<?php

use App\Enums\OptionStatus;
use App\Enums\ProductType;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Option;
use App\Models\Order;
use App\Models\OrderMotorcycle;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\Setting;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Auth\RequestGuard;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Factory;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Application;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

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

        if (! $employee->hasPermissionTo(
            $permission,
            config('backpack.base.guard')
        )) {
            CRUD::denyAllAccess();
        }
    }
}

if (! function_exists('mb_ucwords')) {
    function mb_ucwords(?string $string): string
    {
        return mb_convert_case($string, MB_CASE_TITLE, 'UTF-8');
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
        if (fortify_auth()->check()) {
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
            ->value_preview;
    }
}

if (! function_exists('current_store')) {
    function current_store(): int
    {
        $value = Setting::where('key', 'store_ghn')
            ->firstOrFail()
            ->value;

        return json_decode($value)->shop_id;
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

if (! function_exists('get_product')) {
    function get_product(?int $option_id, $ignore_motor_cycle = true, $only_motor_cycle = false): ?Option
    {
        if (! $option_id) {
            return null;
        }

        return Option::whereId($option_id)
            ->whereStatus(OptionStatus::IN_STOCK)
            ->whereHas(
                'product',
                function (Builder $query) use ($ignore_motor_cycle, $only_motor_cycle) {
                    /** @var Product $query */
                    $query->wherePublished(true);

                    if ($ignore_motor_cycle) {
                        $query->whereNot('type', ProductType::MOTOR_CYCLE);
                    } elseif ($only_motor_cycle) {
                        $query->whereType(ProductType::MOTOR_CYCLE);
                    }
                }
            )
            ->first();
    }
}

if (! function_exists('get_options')) {
    function get_options(Order $order): array
    {
        $options = $order->options
            ->map(function (OrderProduct $order_product): string {
                return sprintf(
                    '| ![%s](%s) | %s%s | %s | %s | %s |',
                    $order_product->option->product->name,
                    Arr::first(json_decode($order_product->option->images)),
                    mb_substr($order_product->option->product->name, 0, 9),
                    mb_strlen($order_product->option->product->name) > 7 ? '...' : '',
                    price($order_product->price),
                    $order_product->amount,
                    price($order_product->price * $order_product->amount),
                );
            })
            ->toArray();

        $appends = array_map(
            fn (array $item): string => sprintf(
                '|||| **%s** | %s |',
                $item['label'],
                $item['value'],
            ),
            [[
                'label' => trans('Tax'),
                'value' => price($order->tax),
            ], [
                'label' => trans('Shipping fee'),
                'value' => price($order->shipping_fee),
            ], [
                'label' => trans('Handling fee'),
                'value' => price($order->handling_fee),
            ], [
                'label' => trans('Total amount'),
                'value' => price($order->total),
            ]]
        );

        return array_merge($options, $appends);
    }
}

if (! function_exists('get_option')) {
    function get_option(OrderMotorcycle $order_motorcycle): array
    {
        $option = [sprintf(
            '| ![%s](%s) | %s%s | %s | %s | %s |',
            $order_motorcycle->option->product->name,
            Arr::first(json_decode($order_motorcycle->option->images)),
            mb_substr($order_motorcycle->option->product->name, 0, 9),
            mb_strlen($order_motorcycle->option->product->name) > 7 ? '...' : '',
            price($order_motorcycle->price),
            $order_motorcycle->amount,
            price($order_motorcycle->price * $order_motorcycle->amount),
        )];

        $appends = array_map(
            fn (array $item): string => sprintf(
                '|||| **%s** | %s |',
                $item['label'],
                $item['value'],
            ),
            [[
                'label' => trans('Motorcycle registration support fee'),
                'value' => price($order_motorcycle->motorcycle_registration_support_fee),
            ], [
                'label' => trans('Registration fee'),
                'value' => price($order_motorcycle->registration_fee),
            ], [
                'label' => trans('License plate registration fee'),
                'value' => price($order_motorcycle->license_plate_registration_fee),
            ], [
                'label' => trans('Tax'),
                'value' => price($order_motorcycle->tax),
            ], [
                'label' => trans('Handling fee'),
                'value' => price($order_motorcycle->handling_fee),
            ], [
                'label' => trans('Total amount'),
                'value' => price($order_motorcycle->total),
            ]]
        );

        return array_merge($option, $appends);
    }
}
