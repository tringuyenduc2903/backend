<?php

namespace App\Actions\GiaoHangNhanh;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Arr;

class ShippingOrderApi extends Api
{
    protected string $fee_path = 'shiip/public-api/v2/shipping-order/fee';

    protected string $available_services_path = 'shiip/public-api/v2/shipping-order/available-services';

    public function fee(
        int $to_district_id,
        string $to_ward_code,
        int $weight,
        string $name,
        int $quantity,
        ?int $service_id = null,
        ?int $service_type_id = null,
        ?float $insurance_value = null,
        ?string $coupon = null,
        ?int $cod_failed_amount = null,
        ?int $from_district_id = null,
        ?string $from_ward_code = null,
        ?int $length = null,
        ?int $width = null,
        ?int $height = null,
        ?float $cod_value = null,
        ?array $items = null,
        ?string $code = null,
    ): array {
        $data = [
            'shop_id' => current_store(),
            'to_district_id' => $to_district_id,
            'to_ward_code' => $to_ward_code,
            'weight' => $weight,
            'name' => $name,
            'quantity' => $quantity,
        ];

        if ($service_id) {
            $data['service_id'] = $service_id;
        } elseif ($service_type_id) {
            $data['service_type_id'] = $service_type_id;
        } else {
            $services = $this->availableServices($to_district_id);

            $service = $weight / 1000 < 20 ? Arr::first($services) : Arr::last($services);

            $data['service_id'] = $service['service_id'];
        }

        if ($insurance_value) {
            $data['insurance_value'] = $insurance_value;
        }

        if ($coupon) {
            $data['coupon'] = $coupon;
        }

        if ($cod_failed_amount) {
            $data['cod_failed_amount'] = $cod_failed_amount;
        }

        if ($from_district_id) {
            $data['from_district_id'] = $from_district_id;
        }

        if ($from_ward_code) {
            $data['from_ward_code'] = $from_ward_code;
        }

        if ($length) {
            $data['length'] = $length;
        }

        if ($width) {
            $data['width'] = $width;
        }

        if ($height) {
            $data['height'] = $height;
        }

        if ($cod_value) {
            $data['cod_value'] = $cod_value;
        }

        if ($items) {
            $data['items'] = $items;
        }

        if ($code) {
            $data['code'] = $code;
        }

        try {
            $response = $this->http_client->post(
                $this->fee_path,
                $data
            );

            handle_api_call_failure($response, __CLASS__, __FUNCTION__);

            return $response->json('data');
        } catch (ConnectionException $exception) {
            handle_exception($exception, __CLASS__, __FUNCTION__);

            return [];
        }
    }

    public function availableServices(int $to_district): array
    {
        $data = [
            'shop_id' => current_store(),
            'from_district' => current_store_district(),
            'to_district' => $to_district,
        ];

        try {
            $response = $this->http_client->post(
                $this->available_services_path,
                $data
            );

            handle_api_call_failure($response, __CLASS__, __FUNCTION__);

            return $response->json('data');
        } catch (ConnectionException $exception) {
            handle_exception($exception, __CLASS__, __FUNCTION__);

            return [];
        }
    }
}
