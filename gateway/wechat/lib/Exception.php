<?php

namespace pay\gateway\wechat\lib;

/**
 * 微信支付API异常类
 *
 * @author Microsoft
 */
class Exception extends \Exception
{

    public function errorMessage()
    {
        return $this->getMessage();
    }

}
