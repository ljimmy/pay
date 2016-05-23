<?php

namespace pay\gateway\alipay\direct\lib;

/**
 * 提交订单
 *
 * @author Microsoft
 */
abstract class BaseRequest
{

    public $alipay_config;

    /**
     * 支付宝网关地址（新）
     */
    public $alipay_gateway_new = 'https://mapi.alipay.com/gateway.do?';
    protected $notify_url;
    protected $return_url;

    //设置异步通知URL
    abstract public function setNotifyUrl();

    //设置同步返回URL
    abstract public function setReturnUrl();

    abstract public function getService();

    public function getNotifyUrl()
    {
        return $this->notify_url;
    }

    public function getReturnUrl()
    {
        return $this->return_url;
    }

    /**
     * 生成签名结果
     * @param $para_sort 已排序要签名的数组
     * return 签名结果字符串
     */
    function buildRequestMysign($para_sort)
    {
        $api    = new Api();
        //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
        $prestr = $api->createLinkstring($para_sort);

        $mysign = "";
        switch (strtoupper(trim(Config::SIGN_TYPE))) {
            case "RSA" :
                $mysign = $api->rsaSign($prestr, dirname(dirname(__FILE__)) . Config::PRIVATE_KEY_PATH);
                break;
            default :
                $mysign = "";
        }

        return $mysign;
    }

    /**
     * 生成要请求给支付宝的参数数组
     * @param $para_temp 请求前的参数数组
     * @return 要请求的参数数组
     */
    function buildRequestPara($para_temp)
    {
        $api = new Api();

        //除去待签名参数数组中的空值和签名参数
        $para_filter = $api->paraFilter($para_temp);

        //对待签名参数数组排序
        $para_sort = $api->argSort($para_filter);

        //生成签名结果
        $mysign = $this->buildRequestMysign($para_sort);

        //签名结果与签名方式加入请求提交参数组中
        $para_sort['sign']      = $mysign;
        $para_sort['sign_type'] = strtoupper(trim(Config::SIGN_TYPE));

        return $para_sort;
    }

    /**
     * 生成要请求给支付宝的参数数组
     * @param $para_temp 请求前的参数数组
     * @return 要请求的参数数组字符串
     */
    function buildRequestParaToString($para_temp)
    {
        //待请求参数数组
        $para = $this->buildRequestPara($para_temp);

        $api = new Api();

        //把参数组中所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串，并对字符串做urlencode编码
        $request_data = $api->createLinkstringUrlencode($para);

        return $request_data;
    }

    /**
     * 建立请求，以表单HTML形式构造（默认）
     * @param $para_temp 请求参数数组
     * @param $method 提交方式。两个值可选：post、get
     * @param $button_name 确认按钮显示文字
     * @return 提交表单HTML文本
     */
    function buildRequestForm($para_temp, $method, $button_name)
    {
        //待请求参数数组
        $para = $this->buildRequestPara($para_temp);

        $sHtml = "<form id='alipaysubmit' name='alipaysubmit' action='" . $this->alipay_gateway_new . "_input_charset=" . trim(strtolower(Config::INPUT_CHARSET)) . "' method='" . $method . "'>";
        while (list ($key, $val) = each($para)) {
            $sHtml.= "<input type='hidden' name='" . $key . "' value='" . $val . "'/>";
        }

        //submit按钮控件请不要含有name属性
        $sHtml = $sHtml . "<input type='submit'  value='" . $button_name . "' style='display:none;'></form>";

        #$sHtml = $sHtml . "<script>document.forms['alipaysubmit'].submit();</script>";

        return $sHtml;
    }

    /**
     * 用于防钓鱼，调用接口query_timestamp来获取时间戳的处理函数
     * 注意：该功能PHP5环境及以上支持，因此必须服务器、本地电脑中装有支持DOMDocument、SSL的PHP配置环境。建议本地调试时使用PHP开发软件
     * return 时间戳字符串
     */
    function query_timestamp()
    {
        $url         = $this->alipay_gateway_new . "service=query_timestamp&partner=" . trim(strtolower($this->alipay_config['partner'])) . "&_input_charset=" . trim(strtolower(Config::INPUT_CHARSET));
        $encrypt_key = "";

        $doc             = new \DOMDocument();
        $doc->load($url);
        $itemEncrypt_key = $doc->getElementsByTagName("encrypt_key");
        $encrypt_key     = $itemEncrypt_key->item(0)->nodeValue;

        return $encrypt_key;
    }

}
