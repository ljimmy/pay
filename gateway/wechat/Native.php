<?php

namespace pay\gateway\wechat;

use pay\gateway\wechat\lib\UnifiedOrder;
use pay\gateway\wechat\lib\BizPayUrl;
use pay\gateway\wechat\lib\Api;
use pay\gateway\wechat\lib\ShortUrl;
use pay\gateway\wechat\NativeNotify;
use pay\gateway\wechat\lib\Exception;

/**
 * 扫描支付
 *
 * @author Microsoft
 */
class Native extends WxPay
{

    public $mode = 1;

    /**
     * 选择模式
     * @param int $mode 1模式一 2 模式二
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
    }

    /**
     * 生成订单
     * @param mixed $data
     * @return url
     * @throws Exception
     */
    public function createOrder($data)
    {
        if (!isset($data['product_id']) || empty($data['product_id'])) {
            throw new Exception('商品ID缺少');
        }

        if ($this->mode == 1) {
            return $this->getPrePayUrl($data);
        } else {
            return $this->getPayUrl($data);
        }
    }

    public function notify($data)
    {
        if ($this->state == false) {
            $this->notify->reply(false);
        }
        $this->notify->NotifyCallBack($data);
    }

    /**
     * 验证数据，成功返回数据，否则返回false
     * @return type
     */
    public function verify()
    {
        if ($this->notify == null) {
            if ($this->mode == 1) {
                $notify = new NativeNotify();
            } else {
                $notify = new Notify();
            }
            $this->notify = $notify;
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

    /**
     * 模式一
     * 流程：
     * 1、组装包含支付信息的url，生成二维码
     * 2、用户扫描二维码，进行支付
     * 3、确定支付之后，微信服务器会回调预先配置的回调地址，在【微信开放平台-微信支付-支付配置】中进行配置
     * 4、在接到回调通知之后，用户进行统一下单支付，并返回支付信息以完成支付（见：native_notify.php）
     * 5、支付完成之后，微信服务器会通知支付成功
     * 6、在支付成功通知中需要查单确认是否真正支付成功（见：notify.php）
     */
    private function getPrePayUrl($productId)
    {
        if (is_array($productId) && isset($productId['product_id'])) {
            $productId = $productId['product_id'];
        }
        $biz = new BizPayUrl();
        $biz->SetProduct_id($productId);

        $values = Api::bizpayurl($biz);
        $url    = "weixin://wxpay/bizpayurl?" . $this->toUrlParams($values);

        $shortUrl = new ShortUrl();
        $shortUrl->SetLong_url($url);
        $result   = Api::shorturl($shortUrl);
        return $result['short_url'];
    }

    /**
     *
     * 参数数组转换为url参数
     * @param array $urlObj
     */
    private function toUrlParams($urlObj)
    {
        $buff = "";
        foreach ($urlObj as $k => $v) {
            $buff .= $k . "=" . $v . "&";
        }

        $buff = trim($buff, "&");
        return $buff;
    }

    /**
     *
     * 生成直接支付url，支付url有效期为2小时,模式二
     * @param Array $data
     */
    private function getPayUrl(array $data)
    {
        if (!isset($data['body']) || empty($data['body'])) {
            throw new Exception('商品描述缺少');
        }
        if (!isset($data['out_trade_no']) || empty($data['out_trade_no'])) {
            throw new Exception('商户订单号缺少');
        }
        if (!isset($data['total_fee']) || empty($data['total_fee'])) {
            throw new Exception('总金额缺少');
        }

//        // 	APP和网页支付提交用户端ip，Native支付填调用微信支付API的机器IP。
//        if (!isset($data['spbill_create_ip']) || empty($data['spbill_create_ip'])) {
//            throw new Exception('终端IP缺少');
//        }

        $input = new UnifiedOrder();

        $input->SetBody($data['body']);
        if (isset($data['attach'])) {
            $input->SetAttach($data['attach']);
        }
        $input->SetOut_trade_no($data['out_trade_no']);
        $input->SetTotal_fee($data['total_fee']); //单位分
        if (isset($data['time_start'])) {
            $input->SetTime_start($data['time_start']);
        }
        if (isset($data['time_expire'])) {
            $input->SetTime_expire($data['time_expire']);
        }
        if (isset($data['goods_tag'])) {
            $input->SetGoods_tag($data['goods_tag']);
        }
        if (isset($data['notify_url'])) {
            $input->SetNotify_url($data['notify_url']);
        }
        if (isset($data['spbill_create_ip'])) {
            $input->SetSpbill_create_ip($data['spbill_create_ip']);
        }
        $input->SetTrade_type('NATIVE');
        $input->SetProduct_id($data['product_id']);

        $result = Api::unifiedOrder($input);
        return $result['code_url'];
    }

}
