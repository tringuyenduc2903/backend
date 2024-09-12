<?php

namespace App\Http\Controllers;

use App\Actions\ProductAPI\CatalogList;
use App\Actions\ProductAPI\CategoryList;
use App\Actions\ProductAPI\OptionList;
use App\Enums\OptionType;
use App\Enums\ProductType;
use App\Enums\ProductTypeEnum;
use App\Models\Category;
use App\Models\Option;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductFilterController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(ProductTypeEnum $product_type, Request $request): array
    {
        $type = $product_type->getKey();

        /** @var Product $product */
        $product = app(CatalogList::class, [
            'product_type' => $type,
        ])->catalog;

        /** @var Option $option */
        $option = app(OptionList::class, [
            'product_type' => $type,
        ])->option;

        /** @var Category $category */
        $category = app(CategoryList::class, [
            'product_type' => $type,
        ])->category;

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
                ->orderBy('id')
                ->pluck('name', 'id'),
        ], [
            'name' => 'manufacturer',
            'label' => trans('Manufacturer'),
            'data' => $product->clone()
                ->whereNotNull('manufacturer')
                ->select('manufacturer')
                ->groupBy('manufacturer')
                ->orderBy('manufacturer')
                ->pluck('manufacturer', 'manufacturer'),
        ], [
            'name' => 'version',
            'label' => trans('Version'),
            'data' => $option->clone()
                ->whereNotNull('version')
                ->select('version')
                ->groupBy('version')
                ->orderBy('version')
                ->pluck('version', 'version'),
        ]];

        if ($type == ProductType::MOTOR_CYCLE) {
            $items[] = [
                'name' => 'color',
                'label' => trans('Color'),
                'data' => $option->clone()
                    ->whereNotNull('color')
                    ->select('color')
                    ->groupBy('color')
                    ->orderBy('color')
                    ->pluck('color', 'color'),
            ];
        } else {
            $items[] = [
                'name' => 'volume',
                'label' => trans('Volume'),
                'data' => $option->clone()
                    ->whereNotNull('volume')
                    ->select('volume')
                    ->groupBy('volume')
                    ->orderBy('volume')
                    ->pluck('volume', 'volume'),
            ];
        }

        return $items;
    }
}
