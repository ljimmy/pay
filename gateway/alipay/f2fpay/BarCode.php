<?php

namespace pay\gateway\alipay\f2fpay;

use pay\gateway\alipay\Alipay;
use pay\gateway\alipay\f2fpay\lib\TradePayRequest;
use pay\gateway\alipay\exception\Exception;

/**
 * Description of Barpay
 *
 * @author Microsoft
 */
class BarCode extends Alipay
{

    /**
     * 创建订单
     * @param array $data
     * @return mixed
     * @throws Exception
     */
    public function createOrder($data)
    {
        if (!is_array($data)) {
            throw new Exception('参数格式错误');
        }

        if (!isset($data['out_trade_no']) || empty($data['out_trade_no'])) {
            throw new Exception('商户订单号缺少');
        }
        if (!isset($data['auth_code']) || empty($data['auth_code'])) {
            throw new Exception('支付授权码缺少');
        }
        if (!isset($data['total_amount']) || empty($data['total_amount'])) {
            throw new Exception('订单总金额缺少');
        }
        if (!isset($data['subject']) || empty($data['subject'])) {
            throw new Exception('订单标题缺少');
        }
        $content = $this->barPay($data);

        $request = new TradePayRequest();
        $request->setBizContent($content);
        $result  = $request->request();
        if ($request['code'] == '10003') {
            if (Query::loopQuery($data['out_trade_no']) == false) {
                return false;
            }
        }
        return $result;
    }

    /**
     * 扫码支付
     * @param array $data
     * @return type
     */
    private function barPay(array $data)
    {
        $biz_content = array(
            'out_trade_no' => strval($data['out_trade_no']),
            'auth_code'    => strval($data['auth_code']),
            'total_amount' => strval($data['total_amount']),
            'subject'      => strval($data['subject']),
            'scene'        => 'bar_code',
        );
        if (isset($data['discountable_amount'])) {
            $biz_content['discountable_amount'] = strval($data['discountable_amount']);
        }
        if (isset($data['body'])) {
            $biz_content['body'] = strval($data['body']);
        }
        if (isset($data['goods_detail']) && is_array($data['goods_detail'])) {
            $biz_content['goods_detail'] = $data['goods_detail'];
        }
        if (isset($data['operator_id'])) {
            $biz_content['operator_id'] = strval($data['operator_id']);
        }
        if (isset($data['store_id'])) {
            $biz_content['store_id'] = strval($data['store_id']);
        }
        if (isset($data['terminal_id'])) {
            $biz_content['terminal_id'] = strval($data['terminal_id']);
        }
        if (isset($data['timeout_express'])) {
            $biz_content['timeout_express'] = strval($data['timeout_express']);
        }

        return json_encode($biz_content, JSON_UNESCAPED_UNICODE);
    }

    private function query()
    {

    }

}
