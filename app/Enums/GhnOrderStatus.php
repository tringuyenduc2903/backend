<?php

namespace App\Enums;

use Rexlabs\Enum\Enum;

/**
 * The GhnOrderStatus enum.
 *
 * @method static self READY_TO_PICK()
 * @method static self PICKING()
 * @method static self MONEY_COLLECT_PICKING()
 * @method static self PICKED()
 * @method static self STORING()
 * @method static self TRANSPORTING()
 * @method static self SORTING()
 * @method static self DELIVERING()
 * @method static self DELIVERED()
 * @method static self MONEY_COLLECT_DELIVERING()
 * @method static self DELIVERY_FAIL()
 * @method static self WAITING_TO_RETURN()
 * @method static self RETURN ()
 * @method static self RETURN_TRANSPORTING ()
 * @method static self RETURN_SORTING ()
 * @method static self RETURNING ()
 * @method static self RETURN_FAIL ()
 * @method static self RETURNED ()
 * @method static self CANCEL ()
 * @method static self EXCEPTION ()
 * @method static self LOST ()
 * @method static self DAMAGE ()
 */
class GhnOrderStatus extends Enum
{
    const READY_TO_PICK = 'ready_to_pick';

    const PICKING = 'picking';

    const MONEY_COLLECT_PICKING = 'money_collect_picking';

    const PICKED = 'picked';

    const STORING = 'storing';

    const TRANSPORTING = 'transporting';

    const SORTING = 'sorting';

    const DELIVERING = 'delivering';

    const DELIVERED = 'delivered';

    const MONEY_COLLECT_DELIVERING = 'money_collect_delivering';

    const DELIVERY_FAIL = 'delivery_fail';

    const WAITING_TO_RETURN = 'waiting_to_return';

    const RETURN = 'return';

    const RETURN_TRANSPORTING = 'return_transporting';

    const RETURN_SORTING = 'return_sorting';

    const RETURNING = 'returning';

    const RETURN_FAIL = 'return_fail';

    const RETURNED = 'returned';

    const CANCEL = 'cancel';

    const EXCEPTION = 'exception';

    const LOST = 'lost';

    const DAMAGE = 'damage';

    /**
     * Retrieve a map of enum keys and values.
     */
    public static function map(): array
    {
        return [
            static::READY_TO_PICK => trans('Chờ lấy hàng'),
            static::PICKING => trans('Đang lấy hàng'),
            static::MONEY_COLLECT_PICKING => trans('Đang tương tác với người gửi'),
            static::PICKED => trans('Lấy hàng thành công'),
            static::STORING => trans('Nhập kho'),
            static::TRANSPORTING => trans('Đang trung chuyển'),
            static::SORTING => trans('Đang phân loại'),
            static::DELIVERING => trans('Đang giao hàng'),
            static::DELIVERED => trans('Giao hàng thành công'),
            static::MONEY_COLLECT_DELIVERING => trans('Đang tương tác với người nhận'),
            static::DELIVERY_FAIL => trans('Giao hàng không thành công'),
            static::WAITING_TO_RETURN => trans('Chờ xác nhận giao lại'),
            static::RETURN => trans('Chuyển hoàn'),
            static::RETURN_TRANSPORTING => trans('Đang trung chuyển hàng hoàn'),
            static::RETURN_SORTING => trans('Đang phân loại hàng hoàn'),
            static::RETURNING => trans('Đang hoàn hàng'),
            static::RETURN_FAIL => trans('Hoàn hàng không thành công'),
            static::RETURNED => trans('Hoàn hàng thành công'),
            static::CANCEL => trans('Đơn huỷ'),
            static::EXCEPTION => trans('Hàng ngoại lệ'),
            static::LOST => trans('Hàng thất lạc'),
            static::DAMAGE => trans('Hàng hư hỏng'),
        ];
    }
}
