<?php

namespace pay\gateway\alipay\f2fpay\lib;

/**
 * 配置文件
 *
 * @author Microsoft
 */
class Config
{

    const ALIPAY_PUBLIC_KEY_FILE    = '/key/alipay_public_key.pem';
    const MERCHANT_PRIVATE_KEY_FILE = '/key/rsa_private_key.pem';
    const MERCHANT_PUBLIC_KEY_FILE  = '/key/rsa_public_key.pem';
    const CHARSET                   = 'UTF-8';
    const GATEWAYURL                = 'https://openapi.alipay.com/gateway.do';
    const APP_ID                    = '';
    const PARTNER                   = '';

}
