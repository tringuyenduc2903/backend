<?php

namespace App\Actions\ProductAPI;

use App\Enums\OptionStatus;
use App\Enums\ProductVisibility;
use App\Models\Option;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;

class OptionList
{
    public Builder $option;

    public function __construct(protected int $product_type)
    {
        $this->option = Option::query();

        $this->active();
        $this->filters();
    }

    protected function active(): void
    {
        $this->option
            ->whereHas(
                'product',
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
            ->whereStatus(OptionStatus::IN_STOCK);
    }

    protected function filters(): void
    {
        $this->option->whereHas(
            'product',
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
                $this->option->where(
                    $column,
                    request($column)
                );
            }
        }

        if (request()->exists('category')) {
            $this->option->whereHas(
                'product.categories',
                fn (Builder $query): Builder => $query->where(
                    'category_id',
                    request('category')
                )
            );
        }
    }
}
