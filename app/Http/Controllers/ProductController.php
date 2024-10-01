<?php

namespace App\Http\Controllers;

use App\Actions\ProductAPI\CatalogList;
use App\Actions\ProductAPI\SearchList;
use App\Enums\ProductTypeEnum;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(ProductTypeEnum $product_type, Request $request): array
    {
        $data = [
            'search' => request('search'),
            'sortColumn' => request('sortColumn'),
            'sortDirection' => request('sortDirection', 'asc'),
            'manufacturer' => request('manufacturer'),
            'manufacturers' => request('manufacturers'),
            'option_type' => request('option_type'),
            'option_types' => request('option_types'),
            'color' => request('color'),
            'colors' => request('colors'),
            'version' => request('version'),
            'versions' => request('versions'),
            'volume' => request('volume'),
            'volumes' => request('volumes'),
            'category' => request('category'),
            'categories' => request('categories'),
        ];

        $product = $request->exists('search')
            ? app(SearchList::class, array_merge(
                $data, [
                    'product_type' => $product_type->value,
                ]))
            : app(CatalogList::class, array_merge(
                $data, [
                    'product_type' => $product_type->key(),
                    'minPrice' => request('minPrice'),
                    'maxPrice' => request('maxPrice'),
                ]));

        $paginator = $product->query->paginate(request('perPage'));

        return $this->getCustomPaginate($paginator);
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductTypeEnum $product_type, string|int $product_id): Product
    {
        return Product::with([
            'options',
            'seo',
            'upsell',
            'cross_sell',
            'related_products',
        ])
            ->withCount('reviews')
            ->withAvg('reviews', 'rate')
            ->where(fn (Builder $query): Builder => $query
                ->orWhere('id', $product_id)
                ->orWhere('search_url', $product_id))
            ->wherePublished(true)
            ->whereType($product_type->key())
            ->firstOrFail();
    }
}
