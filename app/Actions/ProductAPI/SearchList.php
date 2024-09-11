<?php

namespace App\Actions\ProductAPI;

use App\Models\Product;
use Laravel\Scout\Builder;

class SearchList
{
    public Builder $search;

    public function __construct(protected string $product_type)
    {
        $this->search = Product::search(request('search'));

        $this->prerequisites();
        $this->sorts();
        $this->filters();
    }

    protected function prerequisites(): void
    {
        $this->search->query(function (\Illuminate\Database\Eloquent\Builder $query) {
            /** @var Product $query */
            return $query
                ->withMin('options', 'price')
                ->withMax('options', 'price');
        });
    }

    protected function sorts(): void
    {
        if (request()->exists(['sortColumn', 'sortDirection'])) {
            match (request('sortColumn')) {
                'name' => $this->search->orderBy(
                    request('sortColumn'),
                    request('sortDirection')
                ),
                default => null,
            };
        } elseif (request()->exists('sortColumn')) {
            match (request('sortColumn')) {
                'latest' => $this->search->latest(),
                'oldest' => $this->search->oldest(),
                default => null,
            };
        }
    }

    protected function filters(): void
    {
        $this->search->where('type', $this->product_type);

        if (request()->exists('manufacturer')) {
            $this->search->where('manufacturer', request('manufacturer'));
        }

        if (request()->exists('option_type')) {
            request()->merge([
                'option_type' => [
                    trans('New product', locale: 'vi'),
                    trans('Used product', locale: 'vi'),
                    trans('Refurbished product', locale: 'vi'),
                ][request('option_type')],
            ]);
        }

        foreach (['type', 'color', 'version', 'volume'] as $column) {
            if (request()->exists($column)) {
                $this->search->where(
                    "options.$column",
                    request($column)
                );
            }
        }

        if (request()->exists('category')) {
            $this->search->where(
                'categories.id',
                request('category')
            );
        }
    }
}
