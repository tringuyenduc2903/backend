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
    public Builder $query;

    public function __construct(
        protected int $product_type,
        protected ?string $search,
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
        $this->query = Category::query();

        $this->active();
        $this->filters();
    }

    protected function active(): void
    {
        $this->query
            ->whereHas(
                'products',
                function (Builder $query) {
                    /** @var Product $query */
                    return $query
                        ->wherePublished(true)
                        ->whereIn(
                            'visibility',
                            $this->search ? [
                                ProductVisibility::SEARCH,
                                ProductVisibility::CATALOG_AND_SEARCH,
                            ] : [
                                ProductVisibility::CATALOG,
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
        $this->query->whereHas(
            'products',
            function (Builder $query) {
                /** @var Product $query */
                $query->whereType($this->product_type);

                if ($this->manufacturer) {
                    $query->whereManufacturer($this->manufacturer);
                } elseif ($this->manufacturers) {
                    $query->whereIn('manufacturer', $this->manufacturers);
                }

                if ($this->minPrice || $this->maxPrice) {
                    $query->withMin('options', 'price');
                }
                if ($this->minPrice) {
                    $query->having('options_min_price', '>=', $this->minPrice);
                }
                if ($this->maxPrice) {
                    $query->having('options_min_price', '<=', $this->maxPrice);
                }
            }
        );

        $this->query->whereHas(
            'products.options',
            function (Builder $query) {
                /** @var Option $query */
                if ($this->option_type) {
                    $query->whereType($this->option_type);
                } elseif ($this->option_types) {
                    $query->whereIn('type', $this->option_types);
                }

                if ($this->color) {
                    $query->whereColor($this->color);
                } elseif ($this->colors) {
                    $query->whereIn('color', $this->colors);
                }

                if ($this->version) {
                    $query->whereVersion($this->version);
                } elseif ($this->versions) {
                    $query->whereIn('version', $this->versions);
                }

                if ($this->volume) {
                    $query->whereVolume($this->volume);
                } elseif ($this->volumes) {
                    $query->whereIn('volume', $this->volumes);
                }
            }
        );

        if ($this->category) {
            $this->query->find($this->category);
        } elseif ($this->categories) {
            $this->query->whereIn('id', $this->categories);
        }
    }
}
