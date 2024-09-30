<?php

namespace App\Actions\ProductAPI;

use App\Models\Product;
use Laravel\Scout\Builder;

class SearchList
{
    public Builder $query;

    public function __construct(
        protected string $search,
        protected string $product_type,
        protected ?string $sortColumn,
        protected ?string $sortDirection,
        protected ?string $manufacturer,
        protected ?array $manufacturers,
        protected ?string $option_type,
        protected ?array $option_types,
        protected ?string $color,
        protected ?array $colors,
        protected ?string $version,
        protected ?array $versions,
        protected ?string $volume,
        protected ?array $volumes,
        protected ?string $category,
        protected ?array $categories,
    ) {
        $this->query = Product::search($this->search);

        $this->prerequisites();
        $this->sorts();
        $this->filters();
    }

    protected function prerequisites(): void
    {
        $this->query->query(function (\Illuminate\Database\Eloquent\Builder $query) {
            /** @var Product $query */
            return $query
                ->withMin('options', 'price')
                ->withMax('options', 'price')
                ->withCount('reviews')
                ->withAvg('reviews', 'rate');
        });
    }

    protected function sorts(): void
    {
        if ($this->sortColumn) {
            match ($this->sortColumn) {
                'name' => $this->query->orderBy('name', $this->sortDirection),
                'latest' => $this->query->latest(),
                'oldest' => $this->query->oldest(),
                default => null,
            };
        }
    }

    protected function filters(): void
    {
        // Product
        $this->query->where('type_preview', $this->product_type);

        if ($this->manufacturer) {
            $this->query->where('manufacturer', $this->manufacturer);
        } elseif ($this->manufacturers) {
            $this->query->whereIn('manufacturer', $this->manufacturers);
        }

        // Options
        if ($this->option_type) {
            $this->query->where('options.type_preview', $this->option_type);
        } elseif ($this->option_types) {
            $this->query->whereIn('options.type_preview', $this->option_types);
        }

        if ($this->color) {
            $this->query->where('options.color', $this->color);
        } elseif ($this->colors) {
            $this->query->whereIn('options.color', $this->colors);
        }

        if ($this->version) {
            $this->query->where('options.version', $this->version);
        } elseif ($this->versions) {
            $this->query->whereIn('options.version', $this->versions);
        }

        if ($this->volume) {
            $this->query->where('options.volume', $this->volume);
        } elseif ($this->volumes) {
            $this->query->whereIn('options.volume', $this->volumes);
        }

        // Category
        if ($this->category) {
            $this->query->where('categories.id', $this->category);
        } elseif ($this->categories) {
            $this->query->whereIn('categories.id', $this->categories);
        }
    }
}
