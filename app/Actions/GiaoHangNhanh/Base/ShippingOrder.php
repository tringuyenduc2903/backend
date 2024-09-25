<?php

namespace App\Actions\GiaoHangNhanh\Base;

use App\Models\District;
use App\Models\Ward;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

trait ShippingOrder
{
    /**
     * @throws ConnectionException
     * @throws Exception
     */
    public function fee(array $data): array
    {
        $data['shop_id'] = current_store();
        $data['service_id'] = $this->getServiceId($data['to_district_id'], $data['weight']);

        handle_validate_failure(
            $validator = Validator::make($data, [
                'shop_id' => ['required', 'integer'],
                'service_id' => ['required_without:service_type_id', 'integer'],
                'service_type_id' => ['required_without:service_id', 'integer'],
                'insurance_value' => ['sometimes', 'integer', 'between:0,5000000'],
                'coupon' => ['sometimes', 'string'],
                'cod_failed_amount' => ['sometimes', 'integer', 'between:0,3'],
                'from_district_id' => ['sometimes', 'integer', Rule::exists(District::class, 'ghn_id')],
                'from_ward_code' => ['sometimes', 'string', Rule::exists(Ward::class, 'ghn_id')],
                'to_district_id' => ['required', 'integer', Rule::exists(District::class, 'ghn_id')],
                'to_ward_code' => ['required', 'string', Rule::exists(Ward::class, 'ghn_id')],
                'weight' => ['required', 'integer', 'between:1,1600000'],
                'length' => ['sometimes', 'integer', 'between:1,200'],
                'width' => ['sometimes', 'integer', 'between:1,200'],
                'height' => ['sometimes', 'integer', 'between:1,200'],
                'cod_value' => ['sometimes', 'integer', 'between:0,10000000'],
                'items' => ['sometimes', 'array'],
                'items.*.name' => ['required', 'string'],
                'items.*.code' => ['sometimes', 'string'],
                'items.*.quantity' => ['required', 'integer', 'min:1'],
                'items.*.weight' => ['required', 'integer', 'between:1,1600000'],
                'items.*.length' => ['required', 'integer', 'between:1,200'],
                'items.*.width' => ['required', 'integer', 'between:1,200'],
                'items.*.height' => ['required', 'integer', 'between:1,200'],
            ])
        );

        handle_ghn_api(
            $response = $this->http->post('shipping-order/fee', $validator->validated())
        );

        return $response->json('data');
    }

    protected function getServiceId(int $to_district_id, int $weight): int
    {
        $services = \App\Facades\GhnCache::availableServices([
            'to_district' => $to_district_id,
        ]);
        $service = $weight < 20000 ? Arr::first($services) : Arr::last($services);

        return $service['service_id'];
    }

    /**
     * @throws ConnectionException
     * @throws Exception
     */
    public function availableServices(array $data): array
    {
        $data['shop_id'] = current_store();
        $data['from_district'] = current_store_district();

        handle_validate_failure(
            $validator = Validator::make($data, [
                'shop_id' => ['required', 'integer'],
                'from_district' => ['sometimes', 'integer', Rule::exists(District::class, 'ghn_id')],
                'to_district' => ['required', 'integer', Rule::exists(District::class, 'ghn_id')],
            ])
        );

        handle_ghn_api(
            $response = $this->http->post('shipping-order/available-services', $validator->validated())
        );

        return $response->json('data');
    }
}
