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
        $product = $request->exists('search')
            ? app(SearchList::class, [
                'product_type' => $product_type->value,
            ])->search
            : app(CatalogList::class, [
                'product_type' => $product_type->getKey(),
            ])->catalog;

        $paginator = $product->paginate(request('perPage'));

        return $this->getCustomPaginate($paginator);
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductTypeEnum $product_type, string|int $product_id): Product
    {
        return Product::with([
            'seo',
            'upsell',
            'cross_sell',
            'related_products',
        ])
            ->where(fn (Builder $query): Builder => $query
                ->orWhere('id', $product_id)
                ->orWhere('search_url', $product_id))
            ->wherePublished(true)
            ->whereType($product_type->getKey())
            ->firstOrFail();
    }
}
