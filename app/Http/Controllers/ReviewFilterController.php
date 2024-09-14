<?php

namespace App\Http\Controllers;

use App\Enums\OptionStatus;
use App\Enums\ProductVisibility;
use App\Models\Option;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ReviewFilterController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(string $product_id, Request $request): array
    {
        $reviews = Product::wherePublished(true)
            ->whereIn(
                'visibility', [
                    ProductVisibility::SEARCH,
                    ProductVisibility::CATALOG_AND_SEARCH,
                ])
            ->whereHas(
                'options',
                function (Builder $query) {
                    /** @var Option $query */
                    return $query->whereStatus(OptionStatus::IN_STOCK);
                }
            )
            ->findOrFail($product_id)
            ->reviews();

        return [[
            'name' => 'rate',
            'label' => trans('Rate'),
            'data' => [
                '1' => trans(
                    ':number Star (:count)', [
                        'number' => 1,
                        'count' => $reviews->clone()
                            ->whereRate(1)
                            ->count(),
                    ]),
                '2' => trans(
                    ':number Star (:count)', [
                        'number' => 2,
                        'count' => $reviews->clone()
                            ->whereRate(2)
                            ->count(),
                    ]),
                '3' => trans(
                    ':number Star (:count)', [
                        'number' => 3,
                        'count' => $reviews->clone()
                            ->whereRate(3)
                            ->count(),
                    ]),
                '4' => trans(
                    ':number Star (:count)', [
                        'number' => 4,
                        'count' => $reviews->clone()
                            ->whereRate(4)
                            ->count(),
                    ]),
                '5' => trans(
                    ':number Star (:count)', [
                        'number' => 5,
                        'count' => $reviews->clone()
                            ->whereRate(5)
                            ->count(),
                    ]),
                'negative' => trans(
                    ':type (:count)', [
                        'type' => trans('Negative'),
                        'count' => $reviews->clone()
                            ->whereIn('rate', ['1', '2', '3'])
                            ->count(),
                    ]),
                'positive' => trans(
                    ':type (:count)', [
                        'type' => trans('Positive'),
                        'count' => $reviews->clone()
                            ->whereIn('rate', ['4', '5'])
                            ->count(),
                    ]),
                'with_image' => trans(
                    ':type (:count)', [
                        'type' => trans('With image'),
                        'count' => $reviews->clone()
                            ->whereJsonLength('reviews.images', '>', 0)
                            ->count(),
                    ]),
            ],
        ]];
    }
}
