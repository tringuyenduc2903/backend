<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PayOsStatus;
use App\Events\AdminOrderCreatedEvent;
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
        $webhook_data = PayOS::verifyPaymentWebhookData($request->all());

        $payment_link_information = PayOS::getPaymentLinkInformation($webhook_data['orderCode']);

        $order = Order::findOrFail($webhook_data['orderCode']);

        $order->transactions()->create([
            'amount' => $webhook_data['amount'],
            'status' => PayOsStatus::valueForKey($payment_link_information['status']),
            'reference' => $webhook_data['reference'],
        ]);

        switch ($payment_link_information['status']) {
            case PayOsStatus::PAID:
                $order->update([
                    'status' => OrderStatus::TO_SHIP,
                ]);

                app(CreateGhnShip::class, [
                    'event' => app(AdminOrderCreatedEvent::class, [
                        'order' => $order,
                        'employee' => backpack_user(),
                    ]),
                ]);
                break;
            case PayOsStatus::CANCELLED:
                $order->update([
                    'status' => OrderStatus::CANCELLED,
                ]);
                break;
        }
    }
}
