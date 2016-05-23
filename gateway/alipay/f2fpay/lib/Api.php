<?php

namespace pay\gateway\alipay\f2fpay\lib;

use pay\gateway\alipay\f2fpay\lib\BaseRequest;
use pay\gateway\alipay\exception\Exception;

/**
 * 通用接口
 *
 * @author Microsoft
 */
class Api
{

    //应用ID
    public $appId;
    //私钥文件路径
    public $rsaPrivateKeyFilePath;
    //
    public $alipayPublicKey  = null;
    //网关
    public $gatewayUrl       = "https://openapi.alipay.com/gateway.do";
    //api版本
    public $apiVersion       = "1.0";
    // 表单提交字符集编码
    public $charset          = "UTF-8";
    //返回标识后缀
    private $RESPONSE_SUFFIX = "_response";
    //返回的错误标识
    private $ERROR_RESPONSE  = "error_response";
    //
    private $SIGN_NODE_NAME  = "sign";
    //签名类型
    protected $signType      = "RSA";
    //加密密钥和类型
    public $encryptKey;
    public $encryptType      = "AES";

    private function init(BaseRequest $request)
    {
        $request->getApiVersion() ? $this->apiVersion = $request->getApiVersion() : $this->apiVersion;
    }

    public function execute(BaseRequest $request, $appInfoAuthtoken = null)
    {
        $this->init($request);

        //公共参数
        $params    = array(
            'app_id'         => $this->appId,
            'method'         => $request->getApiMethodName(),
            'charset'        => $this->charset,
            'sign_type'      => $this->signType,
            'timestamp'      => date("Y-m-d H:i:s"),
            'version'        => $this->apiVersion,
            'notify_url'     => $request->getNotifyUrl(),
            'app_auth_token' => $appInfoAuthtoken
        );
        //获得接口请求参数
        $apiParams = $request->getApiParas();

        if ($request->getNeedEncrypt()) {
            $params['encrypt_type'] = $this->encryptType;
            if (empty($apiParams['biz_content'])) {
                throw new Exception(" api request Fail! The reason : encrypt request is not supperted!");
            }
            if (empty($this->encryptKey) || empty($this->encryptType)) {
                throw new Exception(" encryptType and encryptKey must not null! ");
            }

            if ("AES" != $this->encryptType) {
                throw new Exception("加密类型只支持AES");
            }
            // 执行加密
            $enCryptContent           = $this->encrypt($apiParams['biz_content']);
            $apiParams['biz_content'] = $enCryptContent;
        }

        //签名
        $params['sign'] = $this->generateSign(array_merge($apiParams, $params));

        //系统参数放入GET请求串
        $requestUrl = $this->gatewayUrl . "?";
        foreach ($params as $sysParamKey => $sysParamValue) {
            $requestUrl .= "$sysParamKey=" . urlencode($sysParamValue) . "&";
        }
        $requestUrl = substr($requestUrl, 0, -1);


        $response = $this->curl($requestUrl, $apiParams);

        //JSON

        $data = json_decode($response, true);

        if (!$data) {
            throw new Exception('数据格式错误');
        }
        $signData = new SignData();

        if (isset($data['sign'])) {
            $signData->sign = $data['sign'];
        }
        $signData->signSourceData = $this->parserJSONSignSource($request, $response);

        $result = $this->getResponse($request, $data);
        //是否有公钥
        if (!$this->alipayPublicKey) {
            return $result;
        }
        //验签
        if ($signData->sign == null || $signData->signSourceData == null) {
            throw new Exception(" check sign Fail! The reason : sign or signData is Empty");
        }

        if (!$this->verify($signData)) {
            if (strpos($signData->signSourceData, "\\/") > 0) {
                $signData->signSourceData = str_replace("\\/", "/", $signData->signSourceData);
                if (!$this->verify($signData)) {
                    throw new Exception("check sign Fail! [sign=" . $signData->sign . ", signSourceData=" . $signData->signSourceData . "]");
                }
            } else {
                throw new Exception("check sign Fail! [sign=" . $signData->sign . ", signSourceData=" . $signData->signSourceData . "]");
            }
        }
        //解密
        if ($request->getNeedEncrypt()) {
            $r      = $this->decryptJSONSignSource($request, $response);
            $result = json_decode($r, true);
        }

        return $result;
    }

    /**
     * 验证签名
     * @param \pay\gateway\alipay\f2fpay\lib\SignData $signData
     * @return type
     * @throws Exception
     */
    public function verify(SignData $signData)
    {
        $data   = $signData->signSourceData;
        $sign   = $signData->sign;
        //读取公钥
        $pubKey = file_get_contents($this->alipayPublicKey);
        //转换为openssl格式密钥
        $res    = openssl_get_publickey($pubKey);
        if (!$res) {
            throw new Exception('支付宝RSA公钥错误。请检查公钥文件格式是否正确');
        }
        //调用openssl内置方法验签，返回bool值
        if ("RSA2" == $this->signType) {
            $result = openssl_verify($data, base64_decode($sign), $res, OPENSSL_ALGO_SHA256);
        } else {
            $result = openssl_verify($data, base64_decode($sign), $res);
        }
        //释放资源
        openssl_free_key($res);
        return $result == 1 ? true : false;
    }

    /**
     * 解析数据
     * @param BaseRequest $request
     * @param type $source
     * @return type
     */
    protected function parserJSONSignSource(BaseRequest $request, $source)
    {
        $nodeName = str_replace('.', '_', $request->getApiMethodName()) . $this->RESPONSE_SUFFIX;

        $index      = strpos($source, $nodeName);
        $errorIndex = strpos($source, $this->ERROR_RESPONSE);

        if ($index > 0) {
            //
        } else if ($errorIndex > 0) {
            $nodeName = $this->ERROR_RESPONSE;
            $index    = $errorIndex;
        } else {
            return null;
        }
        $signIndex = strpos($source, "\"" . $this->SIGN_NODE_NAME . "\"");

        if ($signIndex === false) {
            return null;
        }
        $signDataStartIndex = $index + strlen($nodeName) + 2;
        // 签名前-逗号
        $signDataEndIndex   = $signIndex - 1;
        //计算长度
        $indexLen           = $signDataEndIndex - $signDataStartIndex;
        if ($indexLen < 0) {
            return null;
        }
        return substr($source, $signDataStartIndex, $indexLen);
    }

    /**
     * 获得返回结果
     * @param BaseRequest $request
     * @param array $data
     * @return boolean|array
     */
    public function getResponse(BaseRequest $request, array $data)
    {
        $nodeName = str_replace('.', '_', $request->getApiMethodName()) . $this->RESPONSE_SUFFIX;
        if (isset($data[$nodeName])) {
            return $data[$nodeName];
        } else if (isset($data[$this->ERROR_RESPONSE])) {
            return $data[$this->ERROR_RESPONSE];
        } else {
            return false;
        }
    }

    /**
     * 解密结果
     * @param BaseRequest $request
     * @param type $source
     * @return type
     */
    public function decryptJSONSignSource(BaseRequest $request, $source)
    {
        $nodeName = str_replace('.', '_', $request->getApiMethodName()) . $this->RESPONSE_SUFFIX;

        $index      = strpos($source, $nodeName);
        $errorIndex = strpos($source, $this->ERROR_RESPONSE);

        if ($index > 0) {
            //
        } else if ($errorIndex > 0) {
            $nodeName = $this->ERROR_RESPONSE;
            $index    = $errorIndex;
        } else {
            return null;
        }
        $signIndex = strpos($source, "\"" . $this->SIGN_NODE_NAME . "\"");

        if ($signIndex === false) {
            return null;
        }
        $signDataStartIndex = $index + strlen($nodeName) + 2;
        // 签名前-逗号
        $signDataEndIndex   = $signIndex - 1;
        //计算长度
        $indexLen           = $signDataEndIndex - $signDataStartIndex;
        if ($indexLen < 0) {
            return null;
        }
        //加密内容
        $encContent       = substr($source, $signDataStartIndex + 1, $indexLen - 2);
        //
        $bodyIndexContent = substr($source, 0, $signDataStartIndex);
        $bodyEndContent   = substr($source, $signDataEndIndex, strlen($source) + 1 - $signDataEndIndex);
        //
        $bizContent       = $this->decrypt($encContent);
        return $bodyIndexContent . $bizContent . $bodyEndContent;
    }

    protected function curl($url, $postFields = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

//        $postMultipart = false;
        $data = array();
        if (is_array($postFields) && !empty($postFields)) {

            foreach ($postFields as $k => $v) {
                if ("@" == substr($v, 0, 1)) {
                    $data[$k] = new \CURLFile(substr($v, 1));
                } else {
                    $data[$k] = $v;
                }
            }
            unset($k, $v);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }
//        curl_setopt($ch, CURLOPT_HTTPHEADER,
//            array('Content-Type: application/x-www-form-urlencoded; CHARSET=' . $this->charset));
//        if ($postMultipart) {
//
//            $headers = array('content-type: multipart/form-data;charset=' . $this->postCharset . ';boundary=' . $this->getMillisecond());
//        } else {
//
//            $headers = array('content-type: application/x-www-form-urlencoded;charset=' . $this->postCharset);
//        }
        $reponse = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception(curl_error($ch));
        }
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($statusCode != 200) {
            throw new Exception('HTTP STATUS CODE:' . $statusCode);
        }
        curl_close($ch);
        return $reponse;
    }

    /**
     * 生存签名
     * @param type $params
     * @return type
     */
    public function generateSign($params)
    {
        return $this->sign($this->getSignContent($params));
    }

    /**
     * 获得签名内容
     * @param type $params
     * @return string
     */
    public function getSignContent($params)
    {
        ksort($params);
        $stringToBeSigned = '';
        $i                = 0;
        foreach ($params as $k => $v) {
            if (!empty($v) && "@" != substr($v, 0, 1)) {
                if ($i == 0) {
                    $stringToBeSigned .= "$k" . "=" . "$v";
                } else {
                    $stringToBeSigned .= "&" . "$k" . "=" . "$v";
                }
                $i++;
            }
        }
        unset($k, $v);
        return $stringToBeSigned;
    }

    /**
     * 签名
     * @param type $data
     * @return type
     * @throws Exception
     */
    private function sign($data)
    {
        $priKey = file_get_contents($this->rsaPrivateKeyFilePath);
        $res    = openssl_get_privatekey($priKey);
        if (!$res) {
            throw new Exception('您使用的私钥格式错误，请检查RSA私钥配置');
        }
        if ($this->signType == 'RSA2') {
            openssl_sign($data, $sign, $res, OPENSSL_ALGO_SHA256);
        } else {
            openssl_sign($data, $sign, $res);
        }
        openssl_free_key($res);
        $sign = base64_encode($sign);
        return $sign;
    }

    /**
     * 加密方法
     * @param string $data
     * @return string
     */
    private function encrypt($data)
    {
        $screct_key  = base64_decode($this->encryptKey);
        $str         = $this->addPKCS7Padding($data);
        $iv          = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC), 1);
        $encrypt_str = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $screct_key, $str, MCRYPT_MODE_CBC, $iv);
        return base64_encode($encrypt_str);
    }

    /**
     * 解密方法
     * @param string $data
     * @return string
     */
    private function decrypt($data)
    {
        //AES, 128 模式加密数据 CBC
        $str         = base64_decode($data);
        $screct_key  = base64_decode($this->encryptKey);
        $iv          = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC), 1);
        $encrypt_str = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $screct_key, $str, MCRYPT_MODE_CBC, $iv);

        $encrypt_str = $this->stripPKSC7Padding($encrypt_str);
        return $encrypt_str;
    }

    /**
     * 填充算法
     * @param string $source
     * @return string
     */
    private function addPKCS7Padding($source)
    {
        $source = trim($source);
        $block  = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);

        $pad = $block - (strlen($source) % $block);
        if ($pad <= $block) {
            $char = chr($pad);
            $source .= str_repeat($char, $pad);
        }
        return $source;
    }

    /**
     * 移去填充算法
     * @param string $source
     * @return string
     */
    private function stripPKSC7Padding($source)
    {
        $source = trim($source);
        $char   = substr($source, -1);
        $num    = ord($char);
        if ($num == 62) {
            return $source;
        }
        $source = substr($source, 0, -$num);
        return $source;
    }

}
