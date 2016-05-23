<?php

namespace pay\gateway\alipay\f2fpay;

use pay\gateway\alipay\f2fpay\lib\TradeQueryRequest;

/**
 * 查询
 *
 * @author Microsoft
 */
class Query
{

    /**
     * 查询结果
     * @param string $out_trade_no
     * @return array
     */
    public static function orderQuery($out_trade_no)
    {
        $bizContent = "{\"out_trade_no\":\"" . $out_trade_no . "\"}";
        $request    = new TradeQueryRequest();
        $request->setBizContent($bizContent);
        return $request->request();
    }

    /**
     *  循环查询订单状态
     * @param string $out_trade_no 订单号
     * @param int $times 次数
     * @param int $seconds 间隔时间
     * @return boolean
     */
    public static function loopQuery($out_trade_no, $times = 5, $seconds = 5)
    {
        $times   = intval($times);
        $seconds = intval($seconds);

        if ($times < 1 || $seconds < 1) {
            return false;
        }

        while ($times--) {
            sleep($seconds);
            $result = self::orderQuery($out_trade_no);
            if ($result['code'] == '10000') {
                return $result;
            }
        }
        return false;
    }

}
