<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace pay\gateway\alipay\f2fpay;

use pay\gateway\alipay\Alipay;
use pay\gateway\alipay\f2fpay\lib\Notify;
use pay\gateway\alipay\f2fpay\lib\TradePrecreateRequest;
use pay\gateway\alipay\exception\Exception;

/**
 * Description of Qrpay
 *
 * @author Microsoft
 */
class QrCode extends Alipay
{

    public function createOrder($data)
    {
        if (!is_array($data)) {
            throw new Exception('参数格式错误');
        }
        if (!isset($data['out_trade_no']) || empty($data['out_trade_no'])) {
            throw new Exception('商户订单号缺少');
        }
        if (!isset($data['total_amount']) || empty($data['total_amount'])) {
            throw new Exception('订单总金额缺少');
        }
        if (!isset($data['subject']) || empty($data['subject'])) {
            throw new Exception('订单标题缺少');
        }

        $biz_content = array(
            'out_trade_no' => strval($data['out_trade_no']),
            'total_amount' => strval($data['total_amount']),
            'subject'      => strval($data['subject'])
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

        $request = new TradePrecreateRequest();
        if (isset($data['notify_url'])) {
            $request->setNotifyUrl($data['notify_url']);
        }
        $request->setBizContent(json_encode($biz_content, JSON_UNESCAPED_UNICODE));

        $result = $request->request();

        return $result;
    }

    public function notify($data)
    {
        $this->notify->reply($this->state);
    }

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
