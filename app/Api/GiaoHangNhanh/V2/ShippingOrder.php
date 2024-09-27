<?php

namespace App\Api\GiaoHangNhanh\V2;

use Illuminate\Http\Client\ConnectionException;

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

        return $this->handleResponse(
            $this->http->post('v2/shipping-order/create', $data)
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

        return $this->handleResponse(
            $this->http->post('v2/shipping-order/fee', $data)
        );
    }
}
