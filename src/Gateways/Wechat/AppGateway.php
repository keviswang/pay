<?php

namespace Keviswang\Pay\Gateways\Wechat;

use Keviswang\Pay\Exceptions\InvalidArgumentException;

class AppGateway extends Wechat
{

    protected function getTradeType()
    {
        return 'APP';
    }


    public function pay($config_biz = [])
    {
        if (is_null($this->user_config->get('appid'))) {
            throw new InvalidArgumentException('Missing Config -- [appid]');
        }

        $this->config['appid'] = $this->user_config->get('appid');

        $payRequest = [
            'appid' => $this->user_config->get('appid'),
            'partnerid' => $this->user_config->get('mch_id'),
            'prepayid' => $this->preOrder($config_biz)['prepay_id'],
            'timestamp' => time(),
            'noncestr' => $this->createNonceStr(),
            'package' => 'Sign=WXPay',
        ];
        $payRequest['sign'] = $this->getSign($payRequest);
        return $payRequest;
    }
}
