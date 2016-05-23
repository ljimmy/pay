<?php

namespace pay\gateway\wechat;

use pay\BasePayment;
use pay\gateway\GatewayInterface;
use pay\gateway\wechat\lib\Exception;

/**
 * 微信支付
 *
 * @author Microsoft
 */
class WxPay extends BasePayment implements GatewayInterface
{

    private static $class = array(
        'jsapi'  => 'pay\gateway\wechat\JsApi',
        'native' => 'pay\gateway\wechat\Native',
        'micro'  => 'pay\gateway\wechat\Micro',
    );

    public static function className()
    {
        return get_called_class();
    }

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

    public function createOrder($data)
    {
        //TODO 用户基础该类之后需要重写该方法
    }

    /**
     * 通知服务器
     * @param mixed $data
     */
    public function notify($data)
    {
        if ($this->state == false) {
            $this->notify->reply(false);
        }
        $this->notify->NotifyCallBack($data);
    }

    /**
     * 验证签名。通过返回获取的数据，否则返回false
     * @return boolean
     */
    public function verify()
    {
        if ($this->notify == null) {
            $this->notify = new Notify();
        }
        $result = $this->notify->verify();
        if ($result == false) {
            $this->state = false;
            return false;
        } else {
            $this->state = true;
            return $result;
        }
    }

}
