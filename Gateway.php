<?php

namespace pay;

use pay\gateway\wechat\WxPay;
use pay\gateway\alipay\Alipay;

/**
 *
 * 获取支付方式
 * @author Microsoft
 */
class Gateway
{

    public static function getPayment(array $payment)
    {
        $gateway = strtolower(key($payment));

        $type = current($payment);

        if ($gateway == 'wechat') {
            return WxPay::getInstance($type);
        }
        if ($gateway == 'alipay') {
            return Alipay::getInstance($type);
        }

        return false;
    }

}
