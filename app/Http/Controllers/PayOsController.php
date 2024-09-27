<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\OrderTransactionStatus;
use App\Events\OrderCreatedEvent;
use App\Facades\PayOS;
use App\Listeners\CreateGhnShip;
use App\Models\Order;
use Exception;
use Illuminate\Http\Request;

class PayOsController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @throws Exception
     */
    public function __invoke(Request $request)
    {
        $data = PayOS::verifyPaymentWebhookData($request->all());

        $order = Order::findOrFail($data['orderCode']);

        $order->transactions()->create([
            'amount' => $data['amount'],
            'status' => match ($data['status']) {
                'PAID' => OrderTransactionStatus::SUCCESSFULLY,
                'PENDING', 'PROCESSING' => OrderTransactionStatus::PENDING,
                'CANCELLED' => OrderTransactionStatus::FAILED,
                default => null,
            },
            'reference' => $data['reference'],
        ]);

        switch ($data['status']) {
            case 'PAID':
                $order->update([
                    'status' => OrderStatus::TO_SHIP,
                ]);

                app(CreateGhnShip::class, [
                    'event' => app(OrderCreatedEvent::class, [
                        'order' => $order,
                        'employee' => backpack_user(),
                    ]),
                ]);
                break;
            case 'CANCELLED':
                $order->update([
                    'status' => OrderStatus::CANCELLED,
                ]);
                break;
        }
    }
}
