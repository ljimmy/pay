<?php

namespace pay\gateway\alipay\direct\lib;

/**
 * 即时到账收款
 *
 * @author Microsoft
 */
class WebRequest extends BaseRequest
{

    public $antiphishing = false;

    public function getService()
    {
        return 'create_direct_pay_by_user';
    }

    public function setNotifyUrl()
    {
        $this->notify_url = '';
    }

    public function setReturnUrl()
    {
        $this->return_url = '';
    }

    public function antiphishingKey()
    {
        return $this->query_timestamp();
    }

}
