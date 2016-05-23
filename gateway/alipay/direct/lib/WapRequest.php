<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace pay\gateway\alipay\direct\lib;

/**
 * Description of WapRequest
 *
 * @author Microsoft
 */
class WapRequest extends BaseRequest
{

    public function getService()
    {
        return 'alipay.wap.create.direct.pay.by.user';
    }

    public function setNotifyUrl()
    {
        $this->notify_url = '';
    }

    public function setReturnUrl()
    {
        $this->return_url = '';
    }

}
