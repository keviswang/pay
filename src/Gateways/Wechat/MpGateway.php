<?php

namespace Keviswang\Pay\Gateways\Wechat;

use Keviswang\Pay\Exceptions\InvalidArgumentException;

class MpGateway extends Wechat
{

    protected function getTradeType()
    {
        return 'JSAPI';
    }

    public function pay($config_biz = [])
    {
        if (is_null($this->user_config->get('app_id'))) {
            throw new InvalidArgumentException('Missing Config -- [app_id]');
        }

        $payRequest = [
            'appId' => $this->user_config->get('app_id'),
            'timeStamp' => time(),
            'nonceStr' => $this->createNonceStr(),
            'package' => 'prepay_id=' . $this->preOrder($config_biz)['prepay_id'],
            'signType' => 'MD5',
        ];
        $payRequest['paySign'] = $this->getSign($payRequest);

        return $payRequest;
    }
}
