<?php

namespace pay\gateway\alipay\f2fpay\lib;

/**
 * 预创建订单
 *
 * @author Microsoft
 */
class TradePrecreateRequest extends BaseRequest
{

    public function getApiMethodName()
    {
        return 'alipay.trade.precreate';
    }

}
