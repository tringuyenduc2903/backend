<?php

namespace App\Actions\ProductAPI;

use App\Enums\OptionStatus;
use App\Enums\ProductVisibility;
use App\Models\Category;
use App\Models\Option;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;

class CategoryList
{
    public Builder $category;

    public function __construct(
        protected int $product_type
    ) {
        $this->category = Category::query();

        $this->active();
        $this->filters();
    }

    protected function active(): void
    {
        $this->category
            ->whereHas(
                'products',
                function (Builder $query) {
                    /** @var Product $query */
                    return $query
                        ->whereEnabled(true)
                        ->whereIn(
                            'visibility',
                            request()->exists('search') ? [
                                ProductVisibility::CATALOG,
                                ProductVisibility::CATALOG_AND_SEARCH,
                            ] : [
                                ProductVisibility::SEARCH,
                                ProductVisibility::CATALOG_AND_SEARCH,
                            ]);
                }
            )
            ->whereHas(
                'products.options',
                function (Builder $query) {
                    /** @var Option $query */
                    return $query->whereStatus(OptionStatus::IN_STOCK);
                }
            );
    }

    protected function filters(): void
    {
        $this->category->whereHas(
            'products',
            function (Builder $query) {
                /** @var Product $query */
                $query->whereType($this->product_type);

                if (request()->exists('manufacturer')) {
                    $query->whereManufacturer(request('manufacturer'));
                }

                foreach (['minPrice' => '>=', 'maxPrice' => '<='] as $option => $operator) {
                    if (request()->exists($option)) {
                        $query
                            ->withMin('options', 'price')
                            ->having('options_min_price', $operator, request($option));
                    }
                }
            }
        );

        foreach (['type', 'color', 'version', 'volume'] as $column) {
            if (request()->exists($column)) {
                $this->category->whereHas(
                    'products.options',
                    fn (Builder $query): Builder => $query->where(
                        $column,
                        request($column)
                    )
                );
            }
        }

        if (request()->exists('category')) {
            $this->category->find(request('category'));
        }
    }
}
