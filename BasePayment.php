<?php

namespace pay;

abstract class BasePayment
{

    public static function className()
    {

    }

    /**
     * 返回当前支付的实例
     */
    public static function getInstance($type)
    {

    }

    //回调类
    protected $notify;
    //验证状态
    protected $state;

}
