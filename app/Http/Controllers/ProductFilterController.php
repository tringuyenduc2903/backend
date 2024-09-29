<?php

namespace App\Http\Controllers;

use App\Actions\ProductAPI\CatalogList;
use App\Actions\ProductAPI\CategoryList;
use App\Actions\ProductAPI\OptionList;
use App\Enums\OptionType;
use App\Enums\ProductTypeEnum;
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
        $data = [
            'search' => request('search'),
            'product_type' => $product_type->key(),
            'sortColumn' => request('sortColumn'),
            'sortDirection' => request('sortDirection', 'asc'),
            'manufacturer' => request('manufacturer'),
            'manufacturers' => request('manufacturers'),
            'minPrice' => request('minPrice'),
            'maxPrice' => request('maxPrice'),
            'option_type' => request('option_type'),
            'option_types' => request('option_types'),
            'color' => request('color'),
            'colors' => request('colors'),
            'version' => request('version'),
            'versions' => request('versions'),
            'volume' => request('volume'),
            'volumes' => request('volumes'),
            'category' => request('category'),
            'categories' => request('categories'),
        ];

        /** @var Product $product */
        $product = app(CatalogList::class, $data)->query;

        /** @var Option $option */
        $option = app(OptionList::class, $data)->query;

        /** @var Category $category */
        $category = app(CategoryList::class, $data)->query;

        $items = [[
            'name' => 'type',
            'label' => trans('Type'),
            'data' => OptionType::values(),
        ], [
            'name' => 'minPrice',
            'label' => trans('Min price'),
            'data' => $product->clone()
                ->orderBy('options_min_price')
                ->first()
                ?->options_min_price,
        ], [
            'name' => 'maxPrice',
            'label' => trans('Max price'),
            'data' => $product->clone()
                ->orderByDesc('options_min_price')
                ->first()
                ?->options_min_price,
        ], [
            'name' => 'category',
            'label' => trans('Category'),
            'data' => $category->clone()
                ->withCount('products')
                ->orderBy('id')
                ->get()
                ->map(fn (Category $category): array => [
                    $category->id => "$category->name ($category->products_count)",
                ]),
        ], [
            'name' => 'manufacturer',
            'label' => trans('Manufacturer'),
            'data' => $product->clone()
                ->whereNotNull('manufacturer')
                ->select('manufacturer', DB::raw('COUNT(manufacturer) as manufacturer_count'))
                ->groupBy('manufacturer')
                ->orderBy('manufacturer')
                ->get()
                ->map(fn (Product $product): array => [
                    $product->manufacturer => sprintf(
                        '%s (%d)',
                        $product->manufacturer,
                        $product->getAttribute('manufacturer_count')
                    ),
                ]),
        ], [
            'name' => 'version',
            'label' => trans('Version'),
            'data' => $option->clone()
                ->whereNotNull('version')
                ->select('version', DB::raw('COUNT(version) as version_count'))
                ->groupBy('version')
                ->orderBy('version')
                ->get()
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
                    ->whereNotNull('color')
                    ->select('color', DB::raw('COUNT(color) as color_count'))
                    ->groupBy('color')
                    ->orderBy('color')
                    ->get()
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
                    ->select('volume', DB::raw('COUNT(volume) as volume_count'))
                    ->groupBy('volume')
                    ->orderBy('volume')
                    ->get()
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
