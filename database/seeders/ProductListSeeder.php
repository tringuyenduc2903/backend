<?php

namespace Database\Seeders;

use App\Enums\ProductType;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Seeder;

class ProductListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::whereType(ProductType::MOTOR_CYCLE)
            ->each(function (Product $product) {
                $product->cross_sell()->saveMany(
                    Product::whereNot('type', $product->getRawOriginal('type'))
                        ->whereManufacturer($product->manufacturer)
                        ->inRandomOrder()
                        ->limit(5)
                        ->get()
                );
            });

        Product::each(function (Product $product) {
            $relation = Product::whereNot('id', $product->id)
                ->whereType($product->getRawOriginal('type'))
                ->whereManufacturer($product->manufacturer)
                ->whereHas(
                    'categories',
                    fn (Builder $query): Builder => $query->whereIn(
                        'category_id',
                        $product->categories()->pluck('category_id')
                    )
                )
                ->inRandomOrder()
                ->limit(5);

            $upsell = $relation->clone()
                ->withMax('options', 'price')
                ->having(
                    'options_max_price',
                    '>',
                    $product->loadMax('options', 'price')
                )
                ->get();

            $product->upsell()->saveMany(
                $upsell
            );

            $product->related_products()->saveMany(
                $relation->clone()
                    ->whereNotIn('id', $upsell->pluck('id'))
                    ->get()
            );
        });
    }
}
