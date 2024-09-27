<?php

namespace App\Api\GiaoHangNhanh\V2;

use App\Models\District;
use App\Models\Province;
use App\Models\Ward;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

trait ShippingOrder
{
    public const HANG_NHE = 2;

    public const HANG_NANG = 5;

    public const NGUOI_BAN_NGUOI_GUI = 1;

    public const NGUOI_MUA_NGUOI_NHAN = 2;

    public const CHO_THU_HANG = 'CHOTHUHANG';

    public const CHO_XEM_HANG_KHONG_THU = 'CHOXEMHANGKHONGTHU';

    public const KHONG_CHO_XEM_HANG = 'KHONGCHOXEMHANG';

    /**
     * @throws ConnectionException
     */
    public function createOrder(array $data): array
    {
        $data['shop_id'] = current_store();
        $data['service_type_id'] = $this->getServiceTypeId($data['weight']);

        $must_items = $data['service_type_id'] === self::HANG_NANG && count($data['items']);

        handle_validate_failure(
            $validator = Validator::make($data, [
                'shop_id' => ['required', 'integer'],
                'from_name' => ['sometimes', 'string', 'max:1024'],
                'from_phone' => ['sometimes', 'string'],
                'from_address' => ['sometimes', 'string', 'max:1024'],
                'from_ward_code' => ['sometimes', 'integer', Rule::exists(Ward::class, 'ghn_id')],
                'from_district_code' => ['sometimes', 'integer', Rule::exists(District::class, 'ghn_id')],
                'from_province_code' => ['sometimes', 'integer', Rule::exists(Province::class, 'ghn_id')],
                'to_name' => ['required', 'string', 'max:1024'],
                'to_phone' => ['required', 'string'],
                'to_address' => ['required', 'string', 'max:1024'],
                'to_ward_code' => ['required', 'string', Rule::exists(Ward::class, 'ghn_id')],
                'to_district_code' => ['required', 'integer', Rule::exists(District::class, 'ghn_id')],
                'return_phone' => ['sometimes', 'string'],
                'return_address' => ['sometimes', 'string', 'max:1024'],
                'return_ward_code' => ['sometimes', 'integer', Rule::exists(Ward::class, 'ghn_id')],
                'return_district_code' => ['sometimes', 'integer', Rule::exists(District::class, 'ghn_id')],
                'client_order_code' => ['sometimes', 'string', 'max:50'],
                'cod_amount' => ['sometimes', 'integer', 'between:1,5000000'],
                'content' => ['sometimes', 'string', 'max:2000'],
                'weight' => ['required', 'integer', 'between:1,1600000'],
                'length' => ['required', 'integer', 'between:1,200'],
                'width' => ['required', 'integer', 'between:1,200'],
                'height' => ['required', 'integer', 'between:1,200'],
                'pick_station_id' => ['sometimes', 'integer', 'min:1'],
                'insurance_value' => ['sometimes', 'integer', 'between:1,5000000'],
                'coupon' => ['sometimes', 'string'],
                'service_type_id' => ['required', 'integer', Rule::in([self::HANG_NHE, self::HANG_NANG])],
                'payment_type_id' => ['required', 'integer', Rule::in([self::NGUOI_BAN_NGUOI_GUI, self::NGUOI_MUA_NGUOI_NHAN])],
                'note' => ['sometimes', 'string', 'max:5000'],
                'required_note' => ['required', 'string', 'max:500', Rule::in([self::CHO_THU_HANG, self::CHO_XEM_HANG_KHONG_THU, self::KHONG_CHO_XEM_HANG])],
                'pick_shift' => ['sometimes', 'array'],
                'pickup_time' => ['sometimes', 'integer'],
                'items' => ['required_if:service_type_id,'.self::HANG_NANG, 'array'],
                'items.*.name' => ['required', 'string'],
                'items.*.code' => ['sometimes', 'string'],
                'items.*.quantity' => ['required', 'integer', 'min:1'],
                'items.*.price' => ['required', 'integer'],
                'items.*.weight' => [Rule::requiredIf($must_items), 'integer', 'between:1,1600000'],
                'items.*.length' => [Rule::requiredIf($must_items), 'integer', 'between:1,200'],
                'items.*.width' => [Rule::requiredIf($must_items), 'integer', 'between:1,200'],
                'items.*.height' => [Rule::requiredIf($must_items), 'integer', 'between:1,200'],
                'items.*.category' => ['sometimes'],
                'cod_failed_amount' => ['sometimes', 'integer', 'min:1'],
            ])
        );

        return $this->handleResponse(
            $this->http->post('v2/shipping-order/create', $validator->validated())
        );
    }

    protected function getServiceTypeId(int $weight): int
    {
        return $weight < 20000 ? self::HANG_NHE : self::HANG_NANG;
    }

    /**
     * @throws ConnectionException
     */
    public function fee(array $data): array
    {
        $data['shop_id'] = current_store();
        $data['service_type_id'] = $this->getServiceTypeId($data['weight']);

        handle_validate_failure(
            $validator = Validator::make($data, [
                'shop_id' => ['required', 'integer'],
                'service_id' => ['required_without:service_type_id', 'integer'],
                'service_type_id' => ['required_without:service_id', 'integer'],
                'insurance_value' => ['sometimes', 'integer', 'between:1,5000000'],
                'coupon' => ['sometimes', 'string'],
                'cod_failed_amount' => ['sometimes', 'integer', 'min:1'],
                'from_district_id' => ['sometimes', 'integer', Rule::exists(District::class, 'ghn_id')],
                'from_ward_code' => ['sometimes', 'string', Rule::exists(Ward::class, 'ghn_id')],
                'to_district_id' => ['required', 'integer', Rule::exists(District::class, 'ghn_id')],
                'to_ward_code' => ['required', 'string', Rule::exists(Ward::class, 'ghn_id')],
                'weight' => ['required', 'integer', 'between:1,1600000'],
                'length' => ['sometimes', 'integer', 'between:1,200'],
                'width' => ['sometimes', 'integer', 'between:1,200'],
                'height' => ['sometimes', 'integer', 'between:1,200'],
                'cod_value' => ['sometimes', 'integer', 'between:1,10000000'],
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

        return $this->handleResponse(
            $this->http->post('v2/shipping-order/fee', $validator->validated())
        );
    }
}
