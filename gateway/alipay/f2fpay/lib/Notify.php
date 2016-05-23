<?php

namespace pay\gateway\alipay\f2fpay\lib;

/**
 * 回调处理
 *
 * @author Microsoft
 */
class Notify
{

    public function verify()
    {
        $data = $_POST;

//        $notify_id = isset($data['notify_id ']) ? $data['notify_id '] : '';
//        if (!$notify_id) {
//            return false;
//        }

        $sign      = isset($data['sign']) ? $data['sign'] : '';
        $sign_type = isset($data['sign_type']) ? $data['sign_type'] : '';

        if (!$sign || !$sign_type) {
            return false;
        }
        unset($data['sign'], $data['sign_type']);

        $signData = new SignData();

        $signData->sign = $sign;

        $params = array_map('urldecode', $data);

        $api = new Api();

        $signData->signSourceData = $api->getSignContent($params);

        if (!$api->verify($signData)) {
            return false;
        }
        return $params;
    }

    public function reply($status)
    {
        $content = $status ? 'success' : 'fail';
        echo $content;
    }

}
