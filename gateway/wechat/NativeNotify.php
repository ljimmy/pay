<?php

namespace pay\gateway\wechat;

use pay\gateway\wechat\lib\Notify;
use pay\gateway\wechat\lib\Api;
use pay\gateway\wechat\lib\UnifiedOrder;

/**
 * 扫码支付回调
 *
 * @author Microsoft
 */
class NativeNotify extends Notify
{

    public function unifiedorder($openId, $product_id, $data)
    {
        //统一下单
        $input = new UnifiedOrder();
        $input->SetBody($data['body']);
        if (isset($data['attach'])) {
            $input->SetAttach($data['attach']);
        }
        $input->SetOut_trade_no($data['out_trade_no']);
        $input->SetTotal_fee($data['total_fee']);
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

        $input->SetOpenid($openId);
        $input->SetProduct_id($product_id);
        $result = Api::unifiedOrder($input);
        return $result;
    }

    public function NotifyProcess($data)
    {
        if (
                !array_key_exists("openid", $data) ||
                !array_key_exists("product_id", $data)
        ) {
            $this->msg = "回调数据异常";
            return false;
        }

        $openid     = $data["openid"];
        $product_id = $data["product_id"];

        //统一下单
        $result = $this->unifiedorder($openid, $product_id, $data);
        if (
                !array_key_exists("appid", $result) ||
                !array_key_exists("mch_id", $result) ||
                !array_key_exists("prepay_id", $result)
        ) {
            $this->msg = "统一下单失败";
            return false;
        }

        $this->SetData("appid", $result["appid"]);
        $this->SetData("mch_id", $result["mch_id"]);
        $this->SetData("nonce_str", WxPayApi::getNonceStr());
        $this->SetData("prepay_id", $result["prepay_id"]);
        $this->SetData("result_code", "SUCCESS");
        $this->SetData("err_code_des", "OK");
        return true;
    }

}
