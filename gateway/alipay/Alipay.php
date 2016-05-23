<?php

namespace pay\gateway\alipay;

use pay\BasePayment;
use pay\gateway\GatewayInterface;
use pay\gateway\alipay\exception\Exception;

/**
 * Description of Alipay
 *
 * @author Microsoft
 */
class Alipay extends BasePayment implements GatewayInterface
{

    private static $class = array(
        'barcode'  => 'pay\gateway\alipay\f2fpay\BarCode',
        'wavecode' => 'pay\gateway\alipay\f2fpay\WaveCode',
        'qrcode'   => 'pay\gateway\alipay\f2fpay\QrCode',
        'wap'      => 'pay\gateway\alipay\direct\Wap',
        'web'      => 'pay\gateway\alipay\direct\Web',
    );

    public function createOrder($data)
    {
        //TODO 用户基础该类之后需要重写该方法
    }

    public function notify($data)
    {
        if ($this->state == false) {
            return false;
        }
    }

    public function verify()
    {
        //TODO 用户基础该类之后需要重写该方法
    }

    public static function className()
    {
        return get_called_class();
    }

    //获得支付实例
    public static function getInstance($type)
    {
        $type = strtolower($type);

        $class = self::$class;
        //判断是否存在
        if (!isset($class[$type])) {
            throw new Exception($type . 'is invalid!');
        }

        return (new $class[$type]);
    }

}
