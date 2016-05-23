<?php

namespace pay\gateway\alipay\direct;

use pay\gateway\alipay\Alipay;
use pay\gateway\alipay\direct\lib\Notify;
use pay\gateway\alipay\direct\lib\WebRequest;
use pay\gateway\alipay\direct\lib\Config;
use pay\gateway\alipay\exception\Exception;

/**
 * 手机网站支付
 *
 * @author Microsoft
 */
class Web extends Alipay
{

    public function createOrder($data)
    {
        if (!is_array($data)) {
            throw new Exception('参数错误');
        }
        if (!isset($data['out_trade_no']) || empty($data['out_trade_no'])) {
            throw new Exception('商户订单号缺少');
        }
        if (!isset($data['total_fee']) || empty($data['total_fee'])) {
            throw new Exception('订单交易金额缺少');
        }
        if (!isset($data['subject']) || empty($data['subject'])) {
            throw new Exception('订单标题缺少');
        }
        //其他业务参数根据在线开发文档，添加参数.文档地址:https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.kiX33I&treeId=62&articleId=103740&docType=1

        $data['partner']        = Config::PARTNER;
        $data['seller_id']      = Config::SELLER_ID;
        $data['payment_type']   = Config::PAYMENT_TYPE;
        $data['_input_charset'] = trim(strtolower(Config::INPUT_CHARSET));

        $request         = new WebRequest();
        $data['service'] = $request->getService();
        if ($request->antiphishing) {
            if (!isset($data['exter_invoke_ip']) || empty($data['exter_invoke_ip'])) {
                // 客户端的IP地址 非局域网的外网IP地址，如：221.0.0.1
                $data['exter_invoke_ip'] = $_SERVER['REMOTE_ADDR'];
            }
            // 防钓鱼时间戳
            $data['anti_phishing_key'] = $request->antiphishingKey();
        }

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
