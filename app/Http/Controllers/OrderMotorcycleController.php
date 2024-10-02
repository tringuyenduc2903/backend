<?php

namespace App\Http\Controllers;

use App\Actions\Fee\OrderMotorcycle;
use App\Enums\OrderStatus;
use App\Events\FrontendOrderMotorcycleCreatedEvent;
use App\Facades\OrderMotorcycleFee;
use App\Http\Requests\OrderMotorcycleRequest;
use App\Models\Option;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderMotorcycleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): array
    {
        $order_motorcycles = $request->user()->order_motorcycles();

        if ($request->exists('status')) {
            $order_motorcycles = $order_motorcycles->whereStatus(request('status'));
        }

        $paginator = $order_motorcycles->paginate(request('perPage'));

        return $this->getCustomPaginate($paginator);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(OrderMotorcycleRequest $request): JsonResponse
    {
        $fee = OrderMotorcycleFee::getFee(
            $request->validated('option_id'),
            $request->validated('motorcycle_registration_support'),
            $request->validated('registration_option'),
            $request->validated('license_plate_registration_option')
        );

        session(['order-motorcycle.fee' => $fee]);

        $order_motorcycle = $request
            ->user()
            ->order_motorcycles()
            ->create($request->validated());

        $order_motorcycle
            ->option()
            ->associate(
                Option::findOrFail($request->validated('option_id'))
            )
            ->save();

        event(app(FrontendOrderMotorcycleCreatedEvent::class, [
            'order_motorcycle' => $order_motorcycle,
            'customer' => fortify_user(),
        ]));

        return response()->json([
            'id' => $order_motorcycle->id,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $order_motorcycle_id, Request $request): OrderMotorcycle
    {
        return $request
            ->user()
            ->order_motorcycles()
            ->with(['address', 'identification', 'transactions'])
            ->findOrFail($order_motorcycle_id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $order_motorcycle_id, Request $request): JsonResponse
    {
        $order_motorcycle = $request
            ->user()
            ->order_motorcycles()
            ->findOrFail($order_motorcycle_id);

        if (! $order_motorcycle->canCancel()) {
            abort(
                403,
                trans('Orders with status :name cannot be canceled.', [
                    'name' => $order_motorcycle->status_preview,
                ])
            );
        }

        $order_motorcycle->update([
            'status' => OrderStatus::CANCELLED,
        ]);

        return response()->json('');
    }
}
