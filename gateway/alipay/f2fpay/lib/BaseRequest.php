<?php

namespace pay\gateway\alipay\f2fpay\lib;

use pay\gateway\alipay\exception\Exception;

abstract class BaseRequest
{

    protected $apiParas   = array();
    protected $terminalType;
    protected $terminalInfo;
    protected $prodCode;
    protected $apiVersion = "1.0";
    protected $notifyUrl;
    protected $bizContent;
    //是否需要加密
    protected $needEncrypt;

    public abstract function getApiMethodName();

    public function setBizContent($bizContent)
    {
        $this->bizContent              = $bizContent;
        $this->apiParas["biz_content"] = $bizContent;
    }

    public function getBizContent()
    {
        return $this->bizContent;
    }

    public function setNotifyUrl($notifyUrl)
    {
        $this->notifyUrl = $notifyUrl;
    }

    public function getNotifyUrl()
    {
        return $this->notifyUrl;
    }

    public function getApiParas()
    {
        return $this->apiParas;
    }

    public function getTerminalType()
    {
        return $this->terminalType;
    }

    public function setTerminalType($terminalType)
    {
        $this->terminalType = $terminalType;
    }

    public function getTerminalInfo()
    {
        return $this->terminalInfo;
    }

    public function setTerminalInfo($terminalInfo)
    {
        $this->terminalInfo = $terminalInfo;
    }

    public function getProdCode()
    {
        return $this->prodCode;
    }

    public function setProdCode($prodCode)
    {
        $this->prodCode = $prodCode;
    }

    public function setApiVersion($apiVersion)
    {
        $this->apiVersion = $apiVersion;
    }

    public function getApiVersion()
    {
        return $this->apiVersion;
    }

    public function setNeedEncrypt($needEncrypt)
    {

        $this->needEncrypt = $needEncrypt;
    }

    public function getNeedEncrypt()
    {
        return $this->needEncrypt;
    }

    public function request($token = null)
    {
        $Api = new Api();

        $Api->appId                 = Config::APP_ID;
        $Api->gatewayUrl            = Config::GATEWAYURL;
        $Api->rsaPrivateKeyFilePath = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . Config::MERCHANT_PRIVATE_KEY_FILE;
        $Api->apiVersion            = $this->apiVersion;
        #$Api->alipayPublicKey       = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . Config::ALIPAY_PUBLIC_KEY_FILE;

        $result = $Api->execute($this, $token);

        if ($result['code'] == '10000' || $result['code'] == '10003') {
            return $result;
        }

        if (isset($result['sub_msg'])) {
            throw new Exception($result['sub_msg']);
        }
        throw new Exception($result['msg']);
    }

}
