<?php

namespace App\Enums;

use Rexlabs\Enum\Enum;

/**
 * The GhnOrderReasonEnum enum.
 *
 * @method static self PFA1A0()
 * @method static self PFA2A2()
 * @method static self PFA2A1()
 * @method static self PFA2A3()
 * @method static self PFA1A1()
 * @method static self PCB0B2()
 * @method static self PFA4A1()
 * @method static self PCB0B1()
 * @method static self PFA4A2()
 * @method static self PFA3A2()
 * @method static self DFC1A0()
 * @method static self DFC1A2()
 * @method static self DFC1A4()
 * @method static self DCD0A1()
 * @method static self DFC1A1()
 * @method static self DFC1A7()
 * @method static self DCD0A6()
 * @method static self DCD0A7()
 * @method static self DCD0A5()
 * @method static self DCD1A5()
 * @method static self DCD0A8()
 * @method static self DCD1A1()
 * @method static self DFC1A6()
 * @method static self DCD1A3()
 * @method static self RFE0A0()
 * @method static self RFE0A1()
 * @method static self RFE0A6()
 * @method static self RFE0A3()
 * @method static self RFE0A4()
 * @method static self RFE0A5()
 */
class GhnOrderReasonEnum extends Enum
{
    const PFA1A0 = 'GHN-PFA1A0';

    const PFA2A2 = 'GHN-PFA2A2';

    const PFA2A1 = 'GHN-PFA2A1';

    const PFA2A3 = 'GHN-PFA2A3';

    const PFA1A1 = 'GHN-PFA1A1';

    const PCB0B2 = 'GHN-PCB0B2';

    const PFA4A1 = 'GHN-PFA4A1';

    const PCB0B1 = 'GHN-PCB0B1';

    const PFA4A2 = 'GHN-PFA4A2';

    const PFA3A2 = 'GHN-PFA3A2';

    const DFC1A0 = 'GHN-DFC1A0';

    const DFC1A2 = 'GHN-DFC1A2';

    const DFC1A4 = 'GHN-DFC1A4';

    const DCD0A1 = 'GHN-DCD0A1';

    const DFC1A1 = 'GHN-DFC1A1';

    const DFC1A7 = 'GHN-DFC1A7';

    const DCD0A6 = 'GHN-DCD0A6';

    const DCD0A7 = 'GHN-DCD0A7';

    const DCD0A5 = 'GHN-DCD0A5';

    const DCD1A5 = 'GHN-DCD1A5';

    const DCD0A8 = 'GHN-DCD0A8';

    const DCD1A1 = 'GHN-DCD1A1';

    const DFC1A6 = 'GHN-DFC1A6';

    const DCD1A3 = 'GHN-DCD1A3';

    const RFE0A0 = 'GHN-RFE0A0';

    const RFE0A1 = 'GHN-RFE0A1';

    const RFE0A6 = 'GHN-RFE0A6';

    const RFE0A3 = 'GHN-RFE0A3';

    const RFE0A4 = 'GHN-RFE0A4';

    const RFE0A5 = 'GHN-RFE0A5';

    /**
     * Retrieve a map of enum keys and values.
     */
    public static function map(): array
    {
        return [
            // Lấy thất bại
            static::PFA1A0 => trans('Lấy không thành công: Người gửi hẹn lại ngày lấy hàng'),
            static::PFA2A2 => trans('Lấy không thành công: Thông tin lấy hàng sai (địa chỉ / SĐT)'),
            static::PFA2A1 => trans('Lấy không thành công: Thuê bao không liên lạc được / Máy bận'),
            static::PFA2A3 => trans('Lấy không thành công: Người gửi không nghe máy'),
            static::PFA1A1 => trans('Lấy không thành công: Người gửi muốn gửi hàng tại bưu cục'),
            static::PCB0B2 => trans('Lấy không thành công: Hàng vi phạm quy định khối lượng, kích thước'),
            static::PFA4A1 => trans('Lấy không thành công: Hàng vi phạm quy cách đóng gói'),
            static::PCB0B1 => trans('Lấy không thành công: Người gửi không muốn gửi hàng nữa'),
            static::PFA4A2 => trans('Lấy không thành công: Hàng hóa GHN không vận chuyển'),
            static::PFA3A2 => trans('Lấy không thành công: Nhân viên gặp sự cố'),
            // Giao thất bại
            static::DFC1A0 => trans('Giao không thành công: Người nhận hẹn lại ngày giao'),
            static::DFC1A2 => trans('Giao không thành công: Không liên lạc được người nhận / Chặn số'),
            static::DFC1A4 => trans('Giao không thành công: Người nhận không nghe máy'),
            static::DCD0A1 => trans('Giao không thành công: Sai thông tin người nhận (địa chỉ / SĐT)'),
            static::DFC1A1 => trans('Giao không thành công: Người nhận đổi địa chỉ giao hàng'),
            static::DFC1A7 => trans('Giao không thành công: Người nhận từ chối nhận do không cho xem / thử hàng'),
            static::DCD0A6 => trans('Giao không thành công: Người nhận từ chối nhận do sai sản phẩm'),
            static::DCD0A7 => trans('Giao không thành công: Người nhận từ chối nhận do sai COD'),
            static::DCD0A5 => trans('Giao không thành công: Người nhận từ chối nhận do hàng hư hỏng'),
            static::DCD1A5 => trans('Giao không thành công: Người nhận từ chối nhận do không có tiền'),
            static::DCD0A8 => trans('Giao không thành công: Người nhận đổi ý không mua nữa'),
            static::DCD1A1 => trans('Giao không thành công: Người nhận báo không đặt hàng'),
            static::DFC1A6 => trans('Giao không thành công: Nhân viên gặp sự cố'),
            static::DCD1A3 => trans('Giao không thành công: Hàng suy suyển, bể vỡ trong quá trình vận chuyển'),
            // Trả thất bại
            static::RFE0A0 => trans('Trả không thành công: Người gửi hẹn lại ngày trả hàng'),
            static::RFE0A1 => trans('Trả không thành công: Người gửi đổi địa chỉ trả hàng'),
            static::RFE0A6 => trans('Trả không thành công: Người gửi không nghe máy'),
            static::RFE0A3 => trans('Trả không thành công: Người gửi từ chối nhận do sai sản phẩm'),
            static::RFE0A4 => trans('Trả không thành công: Người gửi từ chối nhận do hàng hư hỏng.'),
            static::RFE0A5 => trans('Trả không thành công: Nhân viên gặp sự cố'),
        ];
    }
}
