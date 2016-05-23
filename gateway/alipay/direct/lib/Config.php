<?php

namespace pay\gateway\alipay\direct\lib;

/**
 * 手机网站支付配置文件
 *
 * @author Microsoft
 */
class Config
{

    //↓↓↓↓↓↓↓↓↓↓请在这里配置您的基本信息↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
    //合作身份者ID，签约账号，以2088开头由16位纯数字组成的字符串，查看地址：https://b.alipay.com/order/pidAndKey.htm
    const PARTNER             = '';
    //收款支付宝账号，以2088开头由16位纯数字组成的字符串，一般情况下收款账号就是签约账号
    const SELLER_ID           = '';
    //商户的私钥,此处填写原始私钥，RSA公私钥生成：https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.nBDxfy&treeId=58&articleId=103242&docType=1
    const PRIVATE_KEY_PATH    = '/key/rsa_private_key.pem';
    //支付宝的公钥，查看地址：https://b.alipay.com/order/pidAndKey.htm
    const ALI_PUBLIC_KEY_PATH = '/key/alipay_public_key.pem';
//    // 服务器异步通知页面路径  需http://格式的完整路径，不能加?id=123这类自定义参数，必须外网可以正常访问
//    const NOTIFY_URL          = '';
//    // 页面跳转同步通知页面路径 需http://格式的完整路径，不能加?id=123这类自定义参数，必须外网可以正常访问
//    const RETURN_URL          = '';
    //签名方式
    const SIGN_TYPE           = 'RSA';
    //字符编码格式 目前支持utf-8
    const INPUT_CHARSET       = 'utf-8';
    //ca证书路径地址，用于curl中ssl校验
    const CACERT              = '/key/cacert.pem';
    //访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
    const TRANSPORT           = 'http';
    // 支付类型 ，无需修改
    const PAYMENT_TYPE        = '1';

    //↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
}
