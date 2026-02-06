<?php

namespace Muzi\Jeepay;

use Muzi\Jeepay\Request\Pay;
use Muzi\Jeepay\Request\Refund;
use Muzi\Jeepay\Request\Transfer;
use Muzi\Jeepay\Request\Division;

/**
 * Class Jeepay
 * @package Muzi\Jeepay
 *
 * @property Pay $pay
 * @property Refund $refund
 * @property Transfer $transfer
 * @property Division $division
 */
class Jeepay
{
    private $config;
    private $services = [];

    /**
     * @param array $config [baseURL, mchNo, appId, key]
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function __get($name)
    {
        if (isset($this->services[$name])) {
            return $this->services[$name];
        }

        $class = "Muzi\\Jeepay\\Request\\" . ucfirst($name);
        if (class_exists($class)) {
            $this->services[$name] = new $class(
                $this->config['baseURL'],
                $this->config['mchNo'],
                $this->config['appId'],
                $this->config['key']
            );
            return $this->services[$name];
        }

        throw new \InvalidArgumentException("Service {$name} not found");
    }
}
