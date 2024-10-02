<?php

namespace App\Actions\Product;

use App\Enums\OptionStatus;
use App\Enums\ProductVisibility;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;

class ProductList
{
    use Catalog;
    use Category;
    use Option;
    use Search;

    protected function productRelation(
        Builder $query,
        ?string $search,
        int $product_type,
        ?string $manufacturer,
        ?array $manufacturers,
        ?float $min_price,
        ?float $max_price,
    ): Builder {
        /** @var Product $query */

        // 2: Product Type
        $query->whereType($product_type)
            // 3: Published
            ->wherePublished(true)
            // 4: Visibility
            ->whereIn('visibility', isset($search) ? [
                ProductVisibility::SEARCH,
                ProductVisibility::CATALOG_AND_SEARCH,
            ] : [
                ProductVisibility::CATALOG,
                ProductVisibility::CATALOG_AND_SEARCH,
            ]);

        if ($manufacturer) {
            // 16: Manufacturer
            $query->whereManufacturer($manufacturer);
        } elseif ($manufacturers) {
            // 17: Manufacturers
            $query->whereIn('manufacturer', $manufacturers);
        }

        if ($min_price || $max_price) {
            // 7: Options Min Price
            $query->withMin('options', 'price');
        }
        if ($min_price) {
            // 18: Options Min Price
            $query->having('options_min_price', '>=', $min_price);
        }
        if ($max_price) {
            // 19: Options Min Price
            $query->having('options_min_price', '<=', $max_price);
        }

        return $query;
    }

    protected function optionRelation(
        Builder|\App\Models\Option $query,
        ?int $option_type,
        ?array $option_types,
        ?string $color,
        ?array $colors,
        ?string $version,
        ?array $versions,
        ?string $volume,
        ?array $volumes,
    ): Builder {
        /** @var \App\Models\Option $query */

        // 5: Options Status
        $query->whereStatus(OptionStatus::IN_STOCK);

        if ($option_type) {
            // 20: Option Type
            $query->whereType($option_type);
        } elseif ($option_types) {
            // 21: Option Types
            $query->whereIn('type', $option_types);
        }
        if ($color) {
            // 22: Color
            $query->whereColor($color);
        } elseif ($colors) {
            // 23: Colors
            $query->whereIn('color', $colors);
        }

        if ($version) {
            // 24: Version
            $query->whereVersion($version);
        } elseif ($versions) {
            // 25: Versions
            $query->whereIn('version', $versions);
        }

        if ($volume) {
            // 26: Volume
            $query->whereVolume($volume);
        } elseif ($volumes) {
            // 27: Volumes
            $query->whereIn('volume', $volumes);
        }

        return $query;
    }
}
