<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PayOsOrderTypeEnum;
use App\Enums\PayOsStatus;
use App\Facades\PayOsApi;
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

            $webhook_data = PayOsApi::verifyPaymentWebhookData($order_type, $data);

            $order = $order_type === PayOsOrderTypeEnum::ORDER
                ? Order::findOrFail($webhook_data['orderCode'])
                : OrderMotorcycle::findOrFail($webhook_data['orderCode']);

            $payment_link_information = PayOsApi::getPaymentLinkInformation($order);

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
