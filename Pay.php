<?php

namespace pay;

use pay\gateway\GatewayInterface;

/**
 * 支付
 *
 * @author Microsoft
 */
class Pay
{

    public $instance;

    public function __construct(GatewayInterface $instance)
    {
        $this->instance = $instance;
    }

    public function createOrder($data)
    {
        return $this->instance->createOrder($data);
    }

    public function notify($data)
    {
        return $this->instance->notify($data);
    }

    public function verify()
    {
        return $this->instance->verify();
    }

}
