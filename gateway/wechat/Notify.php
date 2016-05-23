<?php

namespace pay\gateway\wechat;

use pay\gateway\wechat\lib\Notify as NotifyLib;
use pay\gateway\wechat\lib\Api;
use pay\gateway\wechat\lib\OrderQuery;

/**
 * 回调处理
 *
 * @author Microsoft
 */
class Notify extends NotifyLib
{

    //查询订单
    public function Queryorder($transaction_id)
    {
        $input  = new OrderQuery();
        $input->SetTransaction_id($transaction_id);
        $result = Api::orderQuery($input);

        if (
                array_key_exists("return_code", $result) &&
                array_key_exists("result_code", $result) &&
                $result["return_code"] == "SUCCESS" &&
                $result["result_code"] == "SUCCESS"
        ) {
            return true;
        }
        return false;
    }

    //重写回调处理函数
    public function NotifyProcess($data)
    {
        if (!array_key_exists("transaction_id", $data)) {
            $this->msg = "输入参数不正确";
            return false;
        }
        //查询订单，判断订单真实性
        if (!$this->Queryorder($data["transaction_id"])) {
            $this->msg = "订单查询失败";
            return false;
        }
        return true;
    }

}
