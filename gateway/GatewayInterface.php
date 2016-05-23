<?php

namespace pay\gateway;

/**
 * 支付渠道接口
 *
 * @author Microsoft
 */
interface GatewayInterface
{

    /**
     *
     * 创建订单信息
     * @param type $data
     */
    public function createOrder($data);

    /**
     * 回调通知
     */
    public function notify($data);

    /**
     * 验证来源
     */
    public function verify();
}
