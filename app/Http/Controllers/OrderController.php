<?php

namespace App\Http\Controllers;

use App\Actions\OrderPrice;
use App\Enums\OrderShippingMethod;
use App\Enums\OrderStatus;
use App\Events\FrontendOrderCreatedEvent;
use App\Http\Requests\OrderRequest;
use App\Models\Order;
use App\Models\OrderProduct;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): array
    {
        $orders = $request->user()->orders();

        if ($request->exists('status')) {
            $orders = $orders->whereStatus(request('status'));
        }

        $paginator = $orders
            ->with('option')
            ->paginate(request('perPage'));

        return $this->getCustomPaginate($paginator);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(OrderRequest $request): JsonResponse
    {
        try {
            $price_quote = app(OrderPrice::class, [
                'options' => $request->validated('options'),
                'shipping_method' => $request->validated('shipping_method'),
                'address_id' => $request->validated('address_id'),
            ])->getPriceQuote();

            session(['order.price_quote' => $price_quote]);
        } catch (Exception) {
            throw ValidationException::withMessages([
                'shipping_method' => trans('Shipping method :name is not available for this order', [
                    'name' => OrderShippingMethod::valueForKey(request('shipping_method')),
                ]),
            ]);
        }

        $order = $request
            ->user()
            ->orders()
            ->create($request->validated());

        $order->options()->saveMany(array_map(
            fn (array $item): OrderProduct => OrderProduct::make([
                'option_id' => $item['option_id'],
                'amount' => $item['amount'],
            ]),
            $request->validated('options')
        ));

        event(app(FrontendOrderCreatedEvent::class, [
            'order' => $order,
            'customer' => fortify_user(),
        ]));

        return response()->json('', 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $order_id, Request $request): JsonResponse
    {
        $order = $request
            ->user()
            ->orders()
            ->findOrFail($order_id);

        if (! $order->canCancel()) {
            abort(
                403,
                trans('Orders with status :name cannot be canceled.', [
                    'name' => $order->status_preview,
                ])
            );
        }

        $order->update([
            'status' => OrderStatus::CANCELLED,
        ]);

        return response()->json('');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $order_id, Request $request): Order
    {
        return $request
            ->user()
            ->orders()
            ->with(['options', 'address', 'shipments', 'transactions'])
            ->findOrFail($order_id);
    }
}
