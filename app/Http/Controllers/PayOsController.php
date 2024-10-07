<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PayOsOrderTypeEnum;
use App\Enums\PayOsStatus;
use App\Facades\PayOsOrderApi;
use App\Facades\PayOsOrderMotorcycleApi;
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
        $webhook_data['orderCode'] = $request->order_code;
        $webhook_data['reference'] = $request->reference;
        $payment_link_information['status'] = PayOsStatus::PAID;

        try {
            $data = $request->all();

            if ($order_type === PayOsOrderTypeEnum::ORDER) {
                $webhook_data = PayOsOrderApi::verifyPaymentWebhookData($data);
                $order = Order::findOrFail($webhook_data['orderCode']);
                $payment_link_information = PayOsOrderApi::getPaymentLinkInformation($order);
            } else {
                $webhook_data = PayOsOrderMotorcycleApi::verifyPaymentWebhookData($data);
                $order = OrderMotorcycle::findOrFail($webhook_data['orderCode']);
                $payment_link_information = PayOsOrderMotorcycleApi::getPaymentLinkInformation($order);
            }

            $order
                ->transactions()
                ->whereReference($webhook_data['paymentLinkId'])
                ->first()
                ->update([
                    'status' => PayOsStatus::valueForKey($payment_link_information['status']),
                    'reference' => $webhook_data['reference'],
                ]);

            if ($payment_link_information['status'] == PayOsStatus::CANCELLED) {
                $order->update([
                    'status' => OrderStatus::CANCELLED,
                ]);
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
