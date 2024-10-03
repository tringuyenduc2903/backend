<?php

namespace App\Http\Controllers;

use App\Enums\ProductTypeEnum;
use App\Facades\ProductListApi;
use App\Models\Category;
use App\Models\Option;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductFilterController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(ProductTypeEnum $product_type, Request $request): array
    {
        if ($request->exists('search')) {
            $product_min_price = ProductListApi::getSearch(
                request('search'),
                $product_type->value,
                request('sortColumn'),
                request('sortDirection', 'asc'),
                request('manufacturer'),
                request('manufacturers'),
                request('minPrice'),
                request('maxPrice'),
                request('type'),
                request('types'),
                request('color'),
                request('colors'),
                request('version'),
                request('versions'),
                request('volume'),
                request('volumes'),
                request('category'),
                request('categories')
            )
                ->orderBy('options_min_price.raw')
                ->first()
                ?->options_min_price;

            $product_max_price = ProductListApi::getSearch(
                request('search'),
                $product_type->value,
                request('sortColumn'),
                request('sortDirection', 'asc'),
                request('manufacturer'),
                request('manufacturers'),
                request('minPrice'),
                request('maxPrice'),
                request('type'),
                request('types'),
                request('color'),
                request('colors'),
                request('version'),
                request('versions'),
                request('volume'),
                request('volumes'),
                request('category'),
                request('categories')
            )
                ->orderBy('options_min_price.raw', 'desc')
                ->first()
                ?->options_min_price;

            $manufacturer_handle = array_count_values(
                ProductListApi::getSearch(
                    request('search'),
                    $product_type->value,
                    request('sortColumn'),
                    request('sortDirection', 'asc'),
                    request('manufacturer'),
                    request('manufacturers'),
                    request('minPrice'),
                    request('maxPrice'),
                    request('type'),
                    request('types'),
                    request('color'),
                    request('colors'),
                    request('version'),
                    request('versions'),
                    request('volume'),
                    request('volumes'),
                    request('category'),
                    request('categories')
                )
                    ->get()
                    ->pluck('manufacturer')
                    ->toArray()
            );

            $manufacturer = array_map(
                fn (string $manufacturer, int $number): array => [
                    $manufacturer => sprintf(
                        '%s (%d)',
                        $manufacturer,
                        $number
                    )],
                array_keys($manufacturer_handle), $manufacturer_handle
            );
        } else {
            $product = ProductListApi::getCatalog(
                $product_type->key(),
                request('sortColumn'),
                request('sortDirection', 'asc'),
                request('manufacturer'),
                request('manufacturers'),
                request('minPrice'),
                request('maxPrice'),
                request('type'),
                request('types'),
                request('color'),
                request('colors'),
                request('version'),
                request('versions'),
                request('volume'),
                request('volumes'),
                request('category'),
                request('categories')
            );

            $product_min_price = $product->clone()
                ->orderBy('options_min_price')
                ->first()
                ?->options_min_price;

            $product_max_price = $product->clone()
                ->orderByDesc('options_min_price')
                ->first()
                ?->options_min_price;

            $manufacturer = ProductListApi::getCatalogClone(
                $product_type->key(),
                request('manufacturer'),
                request('manufacturers'),
                request('minPrice'),
                request('maxPrice'),
                request('type'),
                request('types'),
                request('color'),
                request('colors'),
                request('version'),
                request('versions'),
                request('volume'),
                request('volumes'),
                request('category'),
                request('categories')
            )
                ->select([
                    'manufacturer',
                    DB::raw('COUNT(manufacturer) AS manufacturer_count'),
                ])
                ->orderBy('manufacturer')
                ->groupBy('manufacturer')
                ->get(['manufacturer', 'manufacturer_count'])
                ->map(fn (Product $product): array => [
                    $product->manufacturer => sprintf(
                        '%s (%d)',
                        $product->manufacturer,
                        $product->getAttribute('manufacturer_count')
                    ),
                ]);
        }

        $option = ProductListApi::getOption(
            request('search'),
            $product_type->key(),
            request('manufacturer'),
            request('manufacturers'),
            request('minPrice'),
            request('maxPrice'),
            request('type'),
            request('types'),
            request('color'),
            request('colors'),
            request('version'),
            request('versions'),
            request('volume'),
            request('volumes'),
            request('category'),
            request('categories')
        );

        $category = ProductListApi::getCategory(
            request('search'),
            $product_type->key(),
            request('manufacturer'),
            request('manufacturers'),
            request('minPrice'),
            request('maxPrice'),
            request('type'),
            request('types'),
            request('color'),
            request('colors'),
            request('version'),
            request('versions'),
            request('volume'),
            request('volumes'),
            request('category'),
            request('categories')
        );

        $items = [[
            'name' => 'type',
            'label' => trans('Type'),
            'data' => $option->clone()
                ->addSelect('type')
                ->addSelect(DB::raw('COUNT(type) AS type_count'))
                ->orderBy('type')
                ->groupBy('type')
                ->get(['type', 'type_count'])
                ->map(fn (Option $option): array => [
                    $option->type => sprintf(
                        '%s (%d)',
                        $option->type_preview,
                        $option->getAttribute('type_count')
                    ),
                ]),
        ], [
            'name' => 'minPrice',
            'label' => trans('Min price'),
            'data' => $product_min_price,
        ], [
            'name' => 'maxPrice',
            'label' => trans('Max price'),
            'data' => $product_max_price,
        ], [
            'name' => 'category',
            'label' => trans('Category'),
            'data' => $category->clone()
                ->withCount('products')
                ->orderBy('name')
                ->get()
                ->map(fn (Category $category): array => [
                    $category->id => sprintf(
                        '%s (%d)',
                        $category->name,
                        $category->products_count
                    ),
                ]),
        ], [
            'name' => 'manufacturer',
            'label' => trans('Manufacturer'),
            'data' => $manufacturer,
        ], [
            'name' => 'version',
            'label' => trans('Version'),
            'data' => $option->clone()
                ->addSelect([
                    'version',
                    DB::raw('COUNT(version) AS version_count'),
                ])
                ->orderBy('version')
                ->groupBy('version')
                ->get(['version', 'version_count'])
                ->map(fn (Option $option): array => [
                    $option->version => sprintf(
                        '%s (%d)',
                        $option->version,
                        $option->getAttribute('version_count')
                    ),
                ]),
        ]];

        if ($product_type === ProductTypeEnum::MOTOR_CYCLE) {
            $items[] = [
                'name' => 'color',
                'label' => trans('Color'),
                'data' => $option->clone()
                    ->addSelect([
                        'color',
                        DB::raw('COUNT(color) AS color_count'),
                    ])
                    ->orderBy('color')
                    ->groupBy('color')
                    ->get(['color', 'color_count'])
                    ->map(fn (Option $option): array => [
                        $option->color => sprintf(
                            '%s (%d)',
                            $option->color,
                            $option->getAttribute('color_count')
                        ),
                    ]),
            ];
        } else {
            $items[] = [
                'name' => 'volume',
                'label' => trans('Volume'),
                'data' => $option->clone()
                    ->whereNotNull('volume')
                    ->addSelect([
                        'volume',
                        DB::raw('COUNT(volume) AS volume_count'),
                    ])
                    ->orderBy('volume')
                    ->groupBy('volume')
                    ->get(['volume', 'volume_count'])
                    ->map(fn (Option $option): array => [
                        $option->volume => sprintf(
                            '%s (%d)',
                            $option->volume,
                            $option->getAttribute('volume_count')
                        ),
                    ]),
            ];
        }

        return $items;
    }
}
