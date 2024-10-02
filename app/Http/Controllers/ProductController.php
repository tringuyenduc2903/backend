<?php

namespace App\Http\Controllers;

use App\Enums\ProductTypeEnum;
use App\Facades\ProductList;
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
        $product = $request->exists('search')
            ? ProductList::getSearch(
                request('search'),
                $product_type->value,
                request('sortColumn'),
                request('sortDirection', 'asc'),
                request('manufacturer'),
                request('manufacturers'),
                request('minPrice'),
                request('maxPrice'),
                request('optionType'),
                request('optionTypes'),
                request('color'),
                request('colors'),
                request('version'),
                request('versions'),
                request('volume'),
                request('volumes'),
                request('category'),
                request('categories')
            )
            : ProductList::getCatalog(
                request('search'),
                $product_type->key(),
                request('sortColumn'),
                request('sortDirection', 'asc'),
                request('manufacturer'),
                request('manufacturers'),
                request('minPrice'),
                request('maxPrice'),
                request('optionType'),
                request('optionTypes'),
                request('color'),
                request('colors'),
                request('version'),
                request('versions'),
                request('volume'),
                request('volumes'),
                request('category'),
                request('categories')
            );

        $paginator = $product->paginate(request('perPage'));

        return $this->getCustomPaginate($paginator);
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductTypeEnum $product_type, string|int $product_id): Product
    {
        return Product::with([
            // 6: Options
            'options',
            'seo',
            'upsell',
            'cross_sell',
            'related_products',
        ])
            // 9: Reviews Count
            ->withCount('reviews')
            // 10: Reviews Avg Rate
            ->withAvg('reviews', 'rate')
            ->where(fn (Builder $query): Builder => $query
                ->orWhere('id', $product_id)
                ->orWhere('search_url', $product_id))
            // 2: Product Type
            ->whereType($product_type->key())
            // 3: Published
            ->wherePublished(true)
            ->firstOrFail();
    }
}
