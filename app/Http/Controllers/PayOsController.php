<?php

namespace App\Http\Controllers;

use App\Enums\PayOsOrderTypeEnum;
use App\Enums\PayOsStatus;
use Exception;
use Illuminate\Http\Request;
use PayOS\Exceptions\ErrorCode;

class PayOsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, PayOsOrderTypeEnum $order_type): array
    {
        try {
            $webhook_data = $order_type->verifyPaymentWebhookData($request->all());
            $payment_link_information = $order_type->getPaymentLinkInformation($webhook_data['orderCode']);
            $order = $order_type->getOrder($webhook_data['orderCode']);

            $order->transactions()->create([
                'amount' => $webhook_data['amount'],
                'status' => PayOsStatus::valueForKey($payment_link_information['status']),
                'reference' => $webhook_data['reference'],
            ]);

            switch ($payment_link_information['status']) {
                case PayOsStatus::PAID:
                    $order_type->eventPaid($order);
                    break;
                case PayOsStatus::CANCELLED:
                    $order_type->eventCancelled($order);
                    break;
            }
        } catch (Exception $exception) {
            return [
                'message' => $exception->getCode() == ErrorCode::NO_DATA ? 'test' : 'failed',
                'result' => $exception->getMessage(),
            ];
        }

        return [
            'message' => 'success',
            'data' => trans('Successfully'),
        ];
    }
}
