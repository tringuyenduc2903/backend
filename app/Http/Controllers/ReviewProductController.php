<?php

namespace App\Http\Controllers;

use App\Enums\OptionStatus;
use App\Enums\ProductVisibility;
use App\Models\Option;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ReviewProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(string $product_id, Request $request): array
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
            ->reviews()
            ->with([
                'reply',
                'reply.employee',
                'option',
                'option.product',
            ]);

        if ($request->exists('rate')) {
            match (request('rate')) {
                '1', '2', '3', '4', '5' => $reviews->whereRate(request('rate')),
                'negative' => $reviews->whereIn('rate', ['1', '2', '3']),
                'positive' => $reviews->whereIn('rate', ['4', '5']),
                'with_image' => $reviews->whereJsonLength('reviews.images', '>=', 1),
                default => null,
            };
        }

        $paginator = $reviews->paginate(request('perPage'));

        return $this->getCustomPaginate($paginator);
    }
}
