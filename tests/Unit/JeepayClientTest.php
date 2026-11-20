<?php

namespace Muzi\Jeepay\Tests\Unit;

use Muzi\Jeepay\Support\JeepayClient;
use Muzi\Jeepay\Tests\TestCase;
use Muzi\Jeepay\Support\Signature;

class JeepayClientTest extends TestCase
{
    use Signature;

    public function testSign()
    {
        $key = '284957415846666792';
        $params = [
            'mchNo' => 'M1682391685',
            'appId' => '6447428682ca7458118af79f',
            'mchOrderNo' => 'mho1694051705945',
            'wayCode' => 'ALI_BAR',
            'amount' => 1,
            'currency' => 'CNY',
            'clientIp' => '192.166.1.132',
            'subject' => '商品标题',
            'body' => '商品描述',
            'notifyUrl' => 'https://www.jeequan.com',
            'reqTime' => '1694051706',
            'version' => '1.0',
            'signType' => 'MD5',
            'channelExtra' => '{"authCode":"284957415846666792"}',
        ];

        // The expected sign from documentation
        $expectedSign = '924065BA077FA461A9B06D2E76E9ED3C';

        $sign = $this->sign($params, $key);

        $this->assertEquals($expectedSign, $sign);
    }

    public function testSignWithEmptyValues()
    {
        $key = 'test_key';
        $params = [
            'a' => '1',
            'b' => '', // Should be ignored
            'c' => null, // Should be ignored
            'd' => '0', // Should be included
        ];

        // Expected: a=1&d=0&key=test_key
        $expectedString = "a=1&d=0&key={$key}";
        $expectedSign = strtoupper(md5($expectedString));

        $sign = $this->sign($params, $key);

        $this->assertEquals($expectedSign, $sign);
    }
}
