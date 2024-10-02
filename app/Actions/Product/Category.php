<?php

namespace App\Actions\Product;

use Illuminate\Database\Eloquent\Builder;

trait Category
{
    public function getCategory(
        ?string $search,
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
    ): Builder|\App\Models\Category {
        $list = \App\Models\Category::whereHas(
            'products',
            fn (Builder $query): Builder => $this->productRelation(
                $query,
                $search,
                $product_type,
                $manufacturer,
                $manufacturers,
                $min_price,
                $max_price
            )
        )->whereHas(
            'products.options',
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
        );

        if ($category) {
            // 28: Category
            $list->whereId($category);
        } elseif ($categories) {
            // 29: Categories
            $list->whereIn('id', $category);
        }

        return $list;
    }
}
