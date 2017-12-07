<?php

namespace Keviswang\Pay\Contracts;

interface GatewayInterface
{
    public function pay($config_biz);

    public function refund($config_biz);

    public function close($config_biz);

    public function find($out_trade_no);

    public function verify($data, $sign = null, $sync = false);
}
