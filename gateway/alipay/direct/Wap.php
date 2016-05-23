<?php

namespace pay\gateway\alipay\direct;

use pay\gateway\alipay\Alipay;
use pay\gateway\alipay\direct\lib\Notify;
use pay\gateway\alipay\direct\lib\WapRequest;
use pay\gateway\alipay\direct\lib\Config;
use pay\gateway\alipay\exception\Exception;

/**
 * 手机网站支付
 *
 * @author Microsoft
 */
class Wap extends Alipay
{

    public function createOrder($data)
    {
        if (!is_array($data)) {
            throw new Exception('参数错误');
        }
        if (!isset($data['out_trade_no']) || empty($data['out_trade_no'])) {
            throw new Exception('商户订单号缺少');
        }
        if (!isset($data['show_url']) || empty($data['show_url'])) {
            throw new Exception('商品展示的超链接缺少');
        }
        if (!isset($data['total_fee']) || empty($data['total_fee'])) {
            throw new Exception('订单交易金额缺少');
        }
        if (!isset($data['subject']) || empty($data['subject'])) {
            throw new Exception('订单标题缺少');
        }
        //其他业务参数根据在线开发文档，添加参数.文档地址:https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.2Z6TSk&treeId=60&articleId=103693&docType=1

        $data['partner']        = Config::PARTNER;
        $data['seller_id']      = Config::SELLER_ID;
        $data['payment_type']   = Config::PAYMENT_TYPE;
        $data['_input_charset'] = trim(strtolower(Config::INPUT_CHARSET));

        $request         = new WapRequest();
        $data['service'] = $request->getService();

        if (!isset($data['notify_url']) || empty($data['notify_url'])) {
            $data['notify_url'] = $request->getNotifyUrl();
        }
        if (!isset($data['return_url']) || empty($data['return_url'])) {
            $data['return_url'] = $request->getReturnUrl();
        }
        return $request->buildRequestForm($data, "get", "确认");
    }

    public function notify($data)
    {
        if ($data) {
            echo 'success';
        } else {
            echo "fail";
        }
    }

    public function verify()
    {
        if ($this->notify == null) {
            $notify       = new Notify();
            $this->notify = $notify;
        }
        $result = $this->notify->verifyNotify();
        if ($result == false) {
            $this->state = false;
            return false;
        } else {
            $this->state = true;
            return $result;
        }
    }

    public function verifyReturn()
    {
        if ($this->notify == null) {
            $notify       = new Notify();
            $this->notify = $notify;
        }
        $result = $this->notify->verifyReturn();
        if ($result == false) {
            $this->state = false;
            return false;
        } else {
            $this->state = true;
            return $result;
        }
    }

}
