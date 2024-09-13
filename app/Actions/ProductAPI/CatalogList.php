<?php

namespace App\Actions\ProductAPI;

use App\Enums\OptionStatus;
use App\Enums\ProductVisibility;
use App\Models\Option;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;

class CatalogList
{
    public Builder $catalog;

    public function __construct(protected int $product_type)
    {
        $this->catalog = Product::query();

        $this->prerequisites($this->catalog);
        $this->active();
        $this->sorts();
        $this->filters();
    }

    protected function prerequisites(Builder $catalog): void
    {
        $catalog
            ->withMin('options', 'price')
            ->withMax('options', 'price');
    }

    protected function active(): void
    {
        $this->catalog
            ->wherePublished(true)
            ->whereIn(
                'visibility',
                request()->exists('search') ? [
                    ProductVisibility::CATALOG,
                    ProductVisibility::CATALOG_AND_SEARCH,
                ] : [
                    ProductVisibility::SEARCH,
                    ProductVisibility::CATALOG_AND_SEARCH,
                ])
            ->whereHas(
                'options',
                function (Builder $query) {
                    /** @var Option $query */
                    return $query->whereStatus(OptionStatus::IN_STOCK);
                }
            );
    }

    protected function sorts(): void
    {
        if (request()->exists(['sortColumn', 'sortDirection'])) {
            match (request('sortColumn')) {
                'name' => $this->catalog->orderBy(
                    request('sortColumn'),
                    request('sortDirection')
                ),
                'price' => $this->catalog->orderBy(
                    'options_min_price',
                    request('sortDirection')
                ),
                default => null,
            };
        } elseif (request()->exists('sortColumn')) {
            match (request('sortColumn')) {
                'latest' => $this->catalog->latest(),
                'oldest' => $this->catalog->oldest(),
                default => null,
            };
        }
    }

    protected function filters(): void
    {
        $this->catalog->whereType($this->product_type);

        if (request()->exists('manufacturer')) {
            $this->catalog->whereManufacturer(request('manufacturer'));
        }

        foreach (['minPrice' => '>=', 'maxPrice' => '<='] as $option => $operator) {
            if (request()->exists($option)) {
                $this->catalog->having(
                    'options_min_price',
                    $operator,
                    request($option)
                );
            }
        }

        foreach (['type', 'color', 'version', 'volume'] as $column) {
            if (request()->exists($column)) {
                $this->catalog->whereHas(
                    'options',
                    fn (Builder $query): Builder => $query->where(
                        $column,
                        request($column)
                    )
                );
            }
        }

        if (request()->exists('category')) {
            $this->catalog->whereHas(
                'categories',
                fn (Builder $query): Builder => $query->where(
                    'category_id',
                    request('category')
                )
            );
        }
    }
}
