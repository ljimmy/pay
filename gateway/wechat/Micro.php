<?php

namespace pay\gateway\wechat;

use pay\gateway\wechat\lib\MicroPay;
use pay\gateway\wechat\lib\Api;
use pay\gateway\wechat\lib\OrderQuery;
use pay\gateway\wechat\lib\Reverse;
use pay\gateway\wechat\lib\Exception;

/**
 *
 * 刷卡支付实现类
 * 该类实现了一个刷卡支付的流程，流程如下：
 * 1、提交刷卡支付
 * 2、根据返回结果决定是否需要查询订单，如果查询之后订单还未变则需要返回查询（一般反复查10次）
 * 3、如果反复查询10订单依然不变，则发起撤销订单
 * 4、撤销订单需要循环撤销，一直撤销成功为止（注意循环次数，建议10次）
 *
 *
 * @author widy
 *
 */
class Micro extends WxPay
{

    public function createOrder($data)
    {
        if (!isset($data['auth_code']) || empty($data['auth_code'])) {
            throw new Exception('扫码支付授权码缺少');
        }
        if (!isset($data['body']) || empty($data['body'])) {
            throw new Exception('商品描述缺少');
        }
        if (!isset($data['out_trade_no']) || empty($data['out_trade_no'])) {
            throw new Exception('商户订单号缺少');
        }
        if (!isset($data['total_fee']) || empty($data['total_fee'])) {
            throw new Exception('总金额缺少');
        }
        $input = new MicroPay();
        $input->SetAuth_code($data['auth_code']);
        $input->SetBody($data['body']);
        $input->SetOut_trade_no($data['out_trade_no']);
        $input->SetTotal_fee($data['total_fee']);
        if (isset($data['attach'])) {
            $input->SetAttach($data['attach']);
        }
        if (isset($data['time_start'])) {
            $input->SetTime_start($data['time_start']);
        }
        if (isset($data['time_expire'])) {
            $input->SetTime_expire($data['time_expire']);
        }
        if (isset($data['goods_tag'])) {
            $input->SetGoods_tag($data['goods_tag']);
        }
        if (isset($data['spbill_create_ip'])) {
            $input->SetSpbill_create_ip($data['spbill_create_ip']);
        }
        return $this->pay($input);
    }

    /**
     *
     * 提交刷卡支付，并且确认结果
     * @param MicroPay $microPayInput
     * @throws Exception
     * @return 返回查询接口的结果
     */
    public function pay($microPayInput)
    {
        //①、提交被扫支付
        $result = Api::micropay($microPayInput, 5);
        //如果返回成功
        if (
            !array_key_exists("return_code", $result) ||
            !array_key_exists("result_code", $result)
        ) {
            throw new Exception("接口调用失败,请确认是否输入是否有误！");
        }
        //②、接口调用成功，明确返回调用失败
        if (
            $result["return_code"] == "SUCCESS" &&
            $result["result_code"] == "FAIL" &&
            $result["err_code"] != "USERPAYING" &&
            $result["err_code"] != "SYSTEMERROR") {
            if (isset($result['err_code_des'])) {
                throw new Exception($result['err_code_des']);
            }
            return false;
        }
        //签名验证
        $out_trade_no = $microPayInput->GetOut_trade_no();



        //③、确认支付是否成功
        $queryTimes = 10;
        while ($queryTimes > 0) {
            $queryTimes++;
            $succResult  = 0;
            $queryResult = $this->query($out_trade_no, $succResult);
            //如果需要等待1s后继续
            if ($succResult == 2) {
                sleep(2);
                continue;
            } else if ($succResult == 1) {//查询成功
                return $queryResult;
            } else {//订单交易失败
                return false;
            }
        }

        //④、次确认失败，则撤销订单
        if (!$this->cancel($out_trade_no)) {
            throw new Exception("撤销单失败！");
        }

        return false;
    }

    /**
     *
     * 查询订单情况
     * @param string $out_trade_no  商户订单号
     * @param int $succCode         查询订单结果
     * @return 0 订单不成功，1表示订单成功，2表示继续等待
     */
    public function query($out_trade_no, &$succCode)
    {
        $queryOrderInput = new OrderQuery();
        $queryOrderInput->SetOut_trade_no($out_trade_no);
        $result          = Api::orderQuery($queryOrderInput);

        if ($result["return_code"] == "SUCCESS" && $result["result_code"] == "SUCCESS") {
            //支付成功
            if ($result["trade_state"] == "SUCCESS") {
                $succCode = 1;
                return $result;
            }
            //用户支付中
            else if ($result["trade_state"] == "USERPAYING") {
                $succCode = 2;
                return false;
            }
        }

        //如果返回错误码为“此交易订单号不存在”则直接认定失败
        if ($result["err_code"] == "ORDERNOTEXIST") {
            $succCode = 0;
        } else {
            //如果是系统错误，则后续继续
            $succCode = 2;
        }
        return false;
    }

    /**
     *
     * 撤销订单，如果失败会重复调用10次
     * @param string $out_trade_no
     * @param 调用深度 $depth
     */
    public function cancel($out_trade_no, $depth = 0)
    {
        if ($depth > 10) {
            return false;
        }

        $clostOrder = new Reverse();
        $clostOrder->SetOut_trade_no($out_trade_no);
        $result     = Api::reverse($clostOrder);

        //接口调用失败
        if ($result["return_code"] != "SUCCESS") {
            return false;
        }

        //如果结果为success且不需要重新调用撤销，则表示撤销成功
        if ($result["result_code"] != "SUCCESS" && $result["recall"] == "N") {
            return true;
        } else if ($result["recall"] == "Y") {
            return $this->cancel($out_trade_no, ++$depth);
        }
        return false;
    }

}
