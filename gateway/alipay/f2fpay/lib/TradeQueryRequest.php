<?php

namespace pay\gateway\alipay\f2fpay\lib;

/**
 * 查询
 *
 * @author Microsoft
 */
class TradeQueryRequest extends BaseRequest
{

    public function getApiMethodName()
    {
        return 'alipay.trade.query';
    }

}
