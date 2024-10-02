<?php

namespace App\Actions\Product;

use App\Enums\OptionType;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;

trait Search
{
    public function getSearch(
        string $search,
        string $product_type,
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
        ?array $categories,
    ): \Laravel\Scout\Builder {
        // 1: Search
        $list = Product::search($search)
            // 2: Product Type
            ->where('type_preview', $product_type)
            // 3: Published
            ->where('published', true)
            // 4: Visibility
            ->whereIn('visibility_preview', [
                trans('Search', locale: 'vi'),
                trans('Catalog and Search', locale: 'vi'),
            ])
            // 5: Option Status
            ->where('options.status_preview', trans('In stock', locale: 'vi'))
            ->query(function (Builder $query) {
                /** @var Product $query */
                return $query
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
            });

        if ($sort_column) {
            match ($sort_column) {
                // 11: Name
                'name' => $list->orderBy('name', $sort_direction),
                // 12: Price
                'price' => $list->orderBy('options_min_price.raw', $sort_direction),
                // 13: Review
                'review' => $list->orderBy('reviews_avg_rate'),
                // 14: Latest
                'latest' => $list->latest(),
                // 15: Oldest
                'oldest' => $list->oldest(),
            };
        }

        if ($manufacturer) {
            // 16: Manufacturer
            $list->where('manufacturer', $manufacturer);
        } elseif ($manufacturers) {
            // 17: Manufacturers
            $list->whereIn('manufacturer', $manufacturers);
        }

        if ($min_price) {
            // 18: Options Min Price
            $list->where('options_min_price.raw >', $min_price);
        }
        if ($max_price) {
            // 19: Options Min Price
            $list->where('options_min_price.raw <', $max_price);
        }

        if ($option_type) {
            // 20: Option Type
            $list->where('options.type_preview', OptionType::valueForKey($option_type));
        } elseif ($option_types) {
            // 21: Option Types
            $list->whereIn('options.type_preview', OptionType::valueForKey($option_types));
        }

        if ($color) {
            // 22: Color
            $list->where('options.color', $color);
        } elseif ($colors) {
            // 23: Colors
            $list->whereIn('options.color', $colors);
        }

        if ($version) {
            // 24: Version
            $list->where('options.version', $version);
        } elseif ($versions) {
            // 25: Versions
            $list->whereIn('options.version', $versions);
        }

        if ($volume) {
            // 26: Volume
            $list->where('options.volume', $volume);
        } elseif ($volumes) {
            // 27: Volumes
            $list->whereIn('options.volume', $volumes);
        }

        if ($category) {
            // 28: Category
            $list->where('categories.id', $category);
        } elseif ($categories) {
            // 29: Categories
            $list->whereIn('categories.id', $categories);
        }

        return $list;
    }
}
