<?php

namespace App\Actions\Product;

use App\Enums\OptionStatus;
use App\Enums\ProductVisibility;
use App\Models\Option;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;

trait Catalog
{
    public function getCatalog(
        ?string $search,
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
            ->whereIn('visibility', isset($search) ? [
                ProductVisibility::SEARCH,
                ProductVisibility::CATALOG_AND_SEARCH,
            ] : [
                ProductVisibility::CATALOG,
                ProductVisibility::CATALOG_AND_SEARCH,
            ])
            // 5: Options Status
            ->whereHas('options', function (Builder $query): Builder {
                /** @var Option $query */
                return $query->whereStatus(OptionStatus::IN_STOCK);
            })
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

        if ($option_type) {
            // 20: Option Type
            $list->whereHas(
                'options',
                function (Builder $query) use ($option_type): Builder {
                    /** @var Option $query */
                    return $query->whereType($option_type);
                }
            );
        } elseif ($option_types) {
            // 21: Option Types
            $list->whereHas(
                'options',
                fn (Builder $query): Builder => $query->whereIn('type', $option_types)
            );
        }

        if ($color) {
            // 22: Color
            $list->whereHas(
                'options',
                function (Builder $query) use ($color): Builder {
                    /** @var Option $query */
                    return $query->whereColor($color);
                }
            );
        } elseif ($colors) {
            // 23: Colors
            $list->whereHas(
                'options',
                fn (Builder $query): Builder => $query->whereIn('color', $colors)
            );
        }

        if ($version) {
            // 24: Version
            $list->whereHas(
                'options',
                function (Builder $query) use ($version): Builder {
                    /** @var Option $query */
                    return $query->whereVersion($version);
                }
            );
        } elseif ($versions) {
            // 25: Versions
            $list->whereHas(
                'options',
                fn (Builder $query): Builder => $query->whereIn('version', $versions)
            );
        }

        if ($volume) {
            // 26: Volume
            $list->whereHas(
                'options',
                function (Builder $query) use ($volume): Builder {
                    /** @var Option $query */
                    return $query->whereVolume($volume);
                }
            );
        } elseif ($volumes) {
            // 27: Volumes
            $list->whereHas(
                'options',
                fn (Builder $query): Builder => $query->whereIn('volume', $volumes)
            );
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
