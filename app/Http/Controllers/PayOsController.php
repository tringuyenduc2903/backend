<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PayOsOrderTypeEnum;
use App\Enums\PayOsStatus;
use App\Facades\Ghn;
use App\Facades\PayOsOrder;
use App\Facades\PayOsOrderMotorcycle;
use App\Models\Order;
use App\Models\OrderMotorcycle;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PayOS\Exceptions\ErrorCode;

class PayOsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(PayOsOrderTypeEnum $order_type, Request $request): Response|JsonResponse
    {
        try {
            $data = $request->all();

            if ($order_type === PayOsOrderTypeEnum::ORDER) {
                $webhook_data = PayOsOrder::verifyPaymentWebhookData($data);
                $order = Order::findOrFail($webhook_data['orderCode']);
                $payment_link_information = PayOsOrder::getPaymentLinkInformation($order);
            } else {
                $webhook_data = PayOsOrderMotorcycle::verifyPaymentWebhookData($data);
                $order = OrderMotorcycle::findOrFail($webhook_data['orderCode']);
                $payment_link_information = PayOsOrderMotorcycle::getPaymentLinkInformation($order);
            }

            $order->transactions()->create([
                'amount' => $webhook_data['amount'],
                'status' => PayOsStatus::valueForKey($payment_link_information['status']),
                'reference' => $webhook_data['reference'],
            ]);

            switch ($payment_link_information['status']) {
                case PayOsStatus::PAID:
                    if ($order_type === PayOsOrderTypeEnum::ORDER) {
                        $order->update([
                            'status' => OrderStatus::TO_SHIP,
                        ]);

                        $response = Ghn::createOrder($order);

                        $order
                            ->forceFill([
                                'shipping_code' => $response['order_code'],
                            ])
                            ->save();
                    } else {
                        $order->update([
                            'status' => OrderStatus::TO_RECEIVE,
                        ]);
                    }
                    break;
                case PayOsStatus::CANCELLED:
                    $order->update([
                        'status' => OrderStatus::CANCELLED,
                    ]);
                    break;
            }
        } catch (Exception $exception) {
            return $exception->getCode() == ErrorCode::NO_DATA
                ? response()->noContent()
                : response()->json([
                    'message' => $exception->getMessage(),
                ], 500);
        }

        return response()->json([
            'message' => trans('Successfully'),
        ]);
    }
}
