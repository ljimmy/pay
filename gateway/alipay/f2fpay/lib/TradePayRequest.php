<?php

namespace pay\gateway\alipay\f2fpay\lib;

/**
 * 统一收单支付业务请求
 *
 * @author Microsoft
 */
class TradePayRequest extends BaseRequest
{

    public function getApiMethodName()
    {
        return "alipay.trade.pay";
    }

}
