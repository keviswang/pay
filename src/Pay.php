<?php

namespace Keviswang\Pay;

use Keviswang\Pay\Exceptions\InvalidArgumentException;
use Keviswang\Pay\Support\Config;

class Pay
{
    private $config;
    private $drivers;
    private $gateways;


    public function __construct($config = [])
    {
        $this->config = new Config($config);
    }

    public function driver($driver)
    {
        if (is_null($this->config->get($driver))) {
            throw new InvalidArgumentException("Driver [$driver]'s Config is not defined.");
        }
        $this->drivers = $driver;
        return $this;
    }

    public function gateway($gateway = 'web')
    {
        if (!isset($this->drivers)) {
            throw new InvalidArgumentException('Driver is not defined.');
        }
        $this->gateways = $this->createGateway($gateway);
        return $this->gateways;
    }

    protected function createGateway($gateway)
    {
        if (!file_exists(__DIR__ . '/Gateways/' . ucfirst($this->drivers) . '/' . ucfirst($gateway) . 'Gateway.php')) {
            throw new InvalidArgumentException("Gateway [$gateway] is not supported.");
        }
        $gateway = __NAMESPACE__ . '\\Gateways\\' . ucfirst($this->drivers) . '\\' . ucfirst($gateway) . 'Gateway';
        return $this->build($gateway);
    }

    protected function build($gateway)
    {
        return new $gateway($this->config->get($this->drivers));
    }
}
