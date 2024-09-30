<?php

namespace App\Actions\ProductAPI;

use App\Enums\OptionStatus;
use App\Enums\ProductVisibility;
use App\Models\Option;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;

class CatalogList
{
    public Builder $query;

    public function __construct(
        protected int $product_type,
        protected ?string $search,
        protected ?string $sortColumn,
        protected ?string $sortDirection,
        protected ?string $manufacturer,
        protected ?array $manufacturers,
        protected ?float $minPrice,
        protected ?float $maxPrice,
        protected ?int $option_type,
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
        $this->query = Product::query();

        $this->prerequisites();
        $this->active();
        $this->sorts();
        $this->filters();
    }

    protected function prerequisites(): void
    {
        $this->query
            ->withMin('options', 'price')
            ->withMax('options', 'price')
            ->withCount('reviews')
            ->withAvg('reviews', 'rate');
    }

    protected function active(): void
    {
        $this->query
            ->wherePublished(true)
            ->whereIn(
                'visibility',
                $this->search ? [
                    ProductVisibility::SEARCH,
                    ProductVisibility::CATALOG_AND_SEARCH,
                ] : [
                    ProductVisibility::CATALOG,
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
        if ($this->sortColumn) {
            match ($this->sortColumn) {
                'name' => $this->query->orderBy('name', $this->sortDirection),
                'price' => $this->query->orderBy('options_min_price', $this->sortDirection),
                'review' => $this->query->orderByDesc('reviews_avg_rate'),
                'latest' => $this->query->latest(),
                'oldest' => $this->query->oldest(),
            };
        }
    }

    protected function filters(): void
    {
        $this->query->whereType($this->product_type);

        if ($this->manufacturer) {
            $this->query->whereManufacturer($this->manufacturer);
        } elseif ($this->manufacturers) {
            $this->query->whereIn('manufacturer', $this->manufacturers);
        }

        if ($this->minPrice) {
            $this->query->having('options_min_price', '>=', $this->minPrice);
        }
        if ($this->maxPrice) {
            $this->query->having('options_min_price', '<=', $this->maxPrice);
        }

        if ($this->option_type) {
            $this->query->whereHas(
                'options',
                function (Builder $query) {
                    /** @var Option $query */
                    return $query->whereType($this->option_type);
                }
            );
        } elseif ($this->option_types) {
            $this->query->whereHas(
                'options',
                fn (Builder $query): Builder => $query->whereIn('type', $this->option_types)
            );
        }

        if ($this->color) {
            $this->query->whereHas(
                'options',
                function (Builder $query) {
                    /** @var Option $query */
                    return $query->whereColor($this->color);
                }
            );
        } elseif ($this->colors) {
            $this->query->whereHas(
                'options',
                fn (Builder $query): Builder => $query->whereIn('color', $this->colors)
            );
        }

        if ($this->version) {
            $this->query->whereHas(
                'options',
                function (Builder $query) {
                    /** @var Option $query */
                    return $query->whereVersion($this->version);
                }
            );
        } elseif ($this->versions) {
            $this->query->whereHas(
                'options',
                fn (Builder $query): Builder => $query->whereIn('version', $this->versions)
            );
        }

        if ($this->volume) {
            $this->query->whereHas(
                'options',
                function (Builder $query) {
                    /** @var Option $query */
                    return $query->whereVolume($this->volume);
                }
            );
        } elseif ($this->volumes) {
            $this->query->whereHas(
                'options',
                fn (Builder $query): Builder => $query->whereIn('volume', $this->volumes)
            );
        }

        if ($this->category) {
            $this->query->whereHas(
                'categories',
                fn (Builder $query): Builder => $query->where('category_id', $this->category)
            );
        } elseif ($this->categories) {
            $this->query->whereHas(
                'categories',
                fn (Builder $query): Builder => $query->whereIn('category_id', $this->categories)
            );
        }
    }
}
