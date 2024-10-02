<?php

namespace App\Actions\Product;

use App\Enums\ProductVisibility;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;

trait Catalog
{
    public function getCatalog(
        int $product_type,
        ?string $sort_column,
        ?string $sort_direction,
        ?string $manufacturer,
        ?array $manufacturers,
        ?float $min_price,
        ?float $max_price,
        ?int $option_type,
        ?array $option_types,
        ?string $color,
        ?array $colors,
        ?string $version,
        ?array $versions,
        ?string $volume,
        ?array $volumes,
        ?string $category,
        ?array $categories
    ): Builder {
        // 2: Product Type
        $list = Product::whereType($product_type)
            // 3: Published
            ->wherePublished(true)
            // 4: Visibility
            ->whereIn('visibility', [
                ProductVisibility::CATALOG,
                ProductVisibility::CATALOG_AND_SEARCH,
            ])
            ->whereHas(
                'options',
                fn (Builder $query) => $this->optionRelation(
                    $query,
                    $option_type,
                    $option_types,
                    $color,
                    $colors,
                    $version,
                    $versions,
                    $volume,
                    $volumes
                )
            )
            // 6: Options
            ->with('options')
            // 7: Options Min Price
            ->withMin('options', 'price')
            // 8: Options Max Price
            ->withMax('options', 'price')
            // 9: Reviews Count
            ->withCount('reviews')
            // 10: Reviews Avg Rate
            ->withAvg('reviews', 'rate');

        if ($sort_column) {
            match ($sort_column) {
                // 11: Name
                'name' => $list->orderBy('name', $sort_direction),
                // 12: Price
                'price' => $list->orderBy('options_min_price', $sort_direction),
                // 13: Review
                'review' => $list->orderByDesc('reviews_avg_rate'),
                // 14: Latest
                'latest' => $list->latest(),
                // 15: Oldest
                'oldest' => $list->oldest(),
            };
        }

        if ($manufacturer) {
            // 16: Manufacturer
            $list->whereManufacturer($manufacturer);
        } elseif ($manufacturers) {
            // 17: Manufacturers
            $list->whereIn('manufacturer', $manufacturers);
        }

        if ($min_price) {
            // 18: Options Min Price
            $list->having('options_min_price', '>=', $min_price);
        }
        if ($max_price) {
            // 19: Options Min Price
            $list->having('options_min_price', '<=', $max_price);
        }

        if ($category) {
            // 28: Category
            $list->whereHas(
                'categories',
                fn (Builder $query): Builder => $query->where('category_id', $category)
            );
        } elseif ($categories) {
            // 29: Categories
            $list->whereHas(
                'categories',
                fn (Builder $query): Builder => $query->whereIn('category_id', $categories)
            );
        }

        return $list;
    }

    public function getCatalogClone(
        int $product_type,
        ?string $manufacturer,
        ?array $manufacturers,
        ?float $min_price,
        ?float $max_price,
        ?int $option_type,
        ?array $option_types,
        ?string $color,
        ?array $colors,
        ?string $version,
        ?array $versions,
        ?string $volume,
        ?array $volumes,
        ?string $category,
        ?array $categories
    ): Builder {
        // 2: Product Type
        $list = Product::whereType($product_type)
            // 3: Published
            ->wherePublished(true)
            // 4: Visibility
            ->whereIn('visibility', [
                ProductVisibility::CATALOG,
                ProductVisibility::CATALOG_AND_SEARCH,
            ])
            ->whereHas(
                'options',
                fn (Builder $query) => $this->optionRelation(
                    $query,
                    $option_type,
                    $option_types,
                    $color,
                    $colors,
                    $version,
                    $versions,
                    $volume,
                    $volumes
                )
            )
            // 6: Options
            ->with('options')
            // 7: Options Min Price
            ->withMin('options', 'price')
            // 8: Options Max Price
            ->withMax('options', 'price')
            // 9: Reviews Count
            ->withCount('reviews')
            // 10: Reviews Avg Rate
            ->withAvg('reviews', 'rate');

        if ($manufacturer) {
            // 16: Manufacturer
            $list->whereManufacturer($manufacturer);
        } elseif ($manufacturers) {
            // 17: Manufacturers
            $list->whereIn('manufacturer', $manufacturers);
        }

        if ($min_price) {
            // 18: Options Min Price
            $list->whereRaw(
                '(select min(`options`.`price`) from `options` where `products`.`id` = `options`.`product_id` and `options`.`deleted_at` is null) >= ?', [
                    $min_price,
                ]);
        }
        if ($max_price) {
            // 19: Options Min Price
            $list->whereRaw(
                '(select min(`options`.`price`) from `options` where `products`.`id` = `options`.`product_id` and `options`.`deleted_at` is null) <= ?', [
                    $max_price,
                ]);
        }

        if ($category) {
            // 28: Category
            $list->whereHas(
                'categories',
                fn (Builder $query): Builder => $query->where('category_id', $category)
            );
        } elseif ($categories) {
            // 29: Categories
            $list->whereHas(
                'categories',
                fn (Builder $query): Builder => $query->whereIn('category_id', $categories)
            );
        }

        return $list;
    }
}
