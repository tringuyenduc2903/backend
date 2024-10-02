<?php

namespace App\Actions\Product;

use Illuminate\Database\Eloquent\Builder;

trait Option
{
    public function getOption(
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
    ): Builder|\App\Models\Option {
        $list = \App\Models\Option::whereHas(
            'product',
            fn (Builder $query): Builder => $this->productRelation(
                $query,
                $search,
                $product_type,
                $manufacturer,
                $manufacturers,
                $min_price,
                $max_price
            )
        );

        $this->optionRelation($list, $option_type, $option_types, $color, $colors, $version, $versions, $volume, $volumes);

        if ($category) {
            // 28: Category
            $list->whereHas(
                'product.categories',
                fn (Builder $query): Builder => $query->where('category_id', $category)
            );
        } elseif ($categories) {
            // 29: Categories
            $list->whereHas(
                'product.categories',
                fn (Builder $query): Builder => $query->whereIn('category_id', $categories)
            );
        }

        return $list;
    }
}
