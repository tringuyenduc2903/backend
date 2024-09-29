<?php

namespace App\Actions\ProductAPI;

use App\Enums\OptionStatus;
use App\Enums\ProductVisibility;
use App\Models\Option;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;

class OptionList
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
        $this->query = Option::query();

        $this->active();
        $this->filters();
    }

    protected function active(): void
    {
        $this->query
            ->whereHas(
                'product',
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
            ->whereStatus(OptionStatus::IN_STOCK);
    }

    protected function filters(): void
    {
        $this->query->whereHas(
            'product',
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

        if ($this->option_type) {
            $this->query->whereType($this->option_type);
        } elseif ($this->option_types) {
            $this->query->whereIn('type', $this->option_types);
        }

        if ($this->color) {
            $this->query->whereColor($this->color);
        } elseif ($this->colors) {
            $this->query->whereIn('color', $this->colors);
        }

        if ($this->version) {
            $this->query->whereVersion($this->version);
        } elseif ($this->versions) {
            $this->query->whereIn('version', $this->versions);
        }

        if ($this->volume) {
            $this->query->whereVolume($this->volume);
        } elseif ($this->volumes) {
            $this->query->whereIn('volume', $this->volumes);
        }

        if (request()->exists('category')) {
            $this->query->whereHas(
                'product.categories',
                fn (Builder $query): Builder => $query->where(
                    'category_id',
                    request('category')
                )
            );
        }
    }
}
