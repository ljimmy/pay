<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace pay;

//引入自动加载
$loader   = require(realpath(__DIR__ . "/vendor/autoload.php"));
$loader->setUseIncludePath(true);
/**
 * ++++++++++++++++++++++++++++++++
 * |         微信支付               |
 * ++++++++++++++++++++++++++++++++
 */
//微信扫码测试
//模式一
$instance = Gateway::getPayment(['wechat' => 'native']);
$object   = new Pay($instance);
$data     = array(
    'product_id'   => '123',
    'body'         => '测试交易',
    'out_trade_no' => date('Ymd') . mt_rand(10000, 99999),
    'total_fee'    => 100
);
try {
    $result = $object->createOrder($data);
    var_dump('wechat_native_1：' . $result);
} catch (\Exception $e) {
    var_dump('wechat_native_1：' . $e->getMessage());
}
//微信扫码测试
//模式二
$instance = Gateway::getPayment(['wechat' => 'native']);
$instance->setMode(2);
$object   = new Pay($instance);
$data     = array(
    'product_id'   => '123',
    'body'         => '测试交易',
    'out_trade_no' => date('Ymd') . mt_rand(10000, 99999),
    'total_fee'    => 100
);
try {
    $result = $object->createOrder($data);
    var_dump('wechat_native_2：' . $result);
} catch (\Exception $e) {
    var_dump('wechat_native_2：' . $e->getMessage());
}
//微信刷卡测试
$instance = Gateway::getPayment(['wechat' => 'micro']);
$object   = new Pay($instance);
$data     = array(
    'auth_code'    => '123',
    'body'         => '测试交易',
    'out_trade_no' => date('Ymd') . mt_rand(10000, 99999),
    'total_fee'    => 100
);
try {
    $result = $object->createOrder($data);

    var_dump('wechat_micro：' . $result);
} catch (\Exception $e) {
    var_dump('wechat_micro：' . $e->getMessage());
}
//微信JsApi 测试
$instance = Gateway::getPayment(['wechat' => 'jsapi']);
$object   = new Pay($instance);
$data     = array(
    'body'         => '测试交易',
    'out_trade_no' => date('Ymd') . mt_rand(10000, 99999),
    'total_fee'    => 100,
    'openid'       => 'oK14vt-2cevUlgcPvuzZJOZqp7_k'
);
try {
    $result = $object->createOrder($data);
    var_dump('wechat_jsapi：' . $result);
} catch (\Exception $e) {
    var_dump('wechat_jsapi：' . $e->getMessage());
}

/**
 * ++++++++++++++++++++++++++++++++
 * |         支付宝                |
 * ++++++++++++++++++++++++++++++++
 */
//支付宝条码测试
$instance = Gateway::getPayment(['alipay' => 'barcode']);
$object   = new Pay($instance);
$data     = array(
    'auth_code'    => '123',
    'subject'      => 'test',
    'out_trade_no' => date('Ymd') . mt_rand(10000, 99999),
    'total_amount' => 100
);
try {
    $result = $object->createOrder($data);
    var_dump('alipay_barcode：' . $result);
} catch (\Exception $e) {
    var_dump('alipay_barcode：' . $e->getMessage());
}
//支付宝声波支付测试
$instance = Gateway::getPayment(['alipay' => 'wavecode']);
$object   = new Pay($instance);
$data     = array(
    'auth_code'    => '123',
    'subject'      => 'test',
    'out_trade_no' => date('Ymd') . mt_rand(10000, 99999),
    'total_amount' => 100
);
try {
    $result = $object->createOrder($data);

    var_dump('alipay_wavecode：' . $result);
} catch (\Exception $e) {
    var_dump('alipay_wavecode：' . $e->getMessage());
}
//支付宝扫码测试
$instance = Gateway::getPayment(['alipay' => 'qrcode']);
$object   = new Pay($instance);
$data     = array(
    'subject'      => 'test',
    'out_trade_no' => date('Ymd') . mt_rand(10000, 99999),
    'total_amount' => 0.01
);
try {
    $result = $object->createOrder($data);
    var_dump('alipay_qrcode：');
    var_dump($result);
} catch (\Exception $e) {
    var_dump('alipay_qrcode：' . $e->getMessage());
}
//手机网站即时到账支付
$instance = Gateway::getPayment(['alipay' => 'wap']);
$object   = new Pay($instance);
$data     = array(
    'subject'      => 'test',
    'out_trade_no' => date('Ymd') . mt_rand(10000, 99999),
    'total_fee'    => 0.01,
    'show_url'     => 'http://www.lohas100.com'
);
try {
    $result = $object->createOrder($data);
    var_dump('alipay_wap：');
    var_dump($result);
} catch (\Exception $e) {
    var_dump('alipay_wap：' . $e->getMessage());
}
//即时到账支付
$instance = Gateway::getPayment(['alipay' => 'wap']);
$object   = new Pay($instance);
$data     = array(
    'subject'      => 'test',
    'out_trade_no' => date('Ymd') . mt_rand(10000, 99999),
    'total_fee'    => 0.01,
);
try {
    $result = $object->createOrder($data);
    var_dump('alipay_web：');
    var_dump($result);
} catch (\Exception $e) {
    var_dump('alipay_web：' . $e->getMessage());
}

