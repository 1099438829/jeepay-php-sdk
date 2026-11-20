<?php

namespace Muzi\Jeepay\Tests\Unit;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use Muzi\Jeepay\Jeepay;
use Muzi\Jeepay\Tests\TestCase;
use Mockery;

class PayTest extends TestCase
{
    public function testUnifiedOrder()
    {
        $config = [
            'baseURL' => 'https://api.jeepay.com',
            'mchNo'   => 'M12345678',
            'appId'   => 'APPID123',
            'key'     => 'KEY123'
        ];

        $jeepay = new Jeepay($config);
        $pay = $jeepay->pay;

        // Mock HTTP Client
        $mockClient = Mockery::mock(ClientInterface::class);
        $responseData = [
            'code' => 0,
            'msg' => 'SUCCESS',
            'data' => [
                'payOrderId' => 'P123456',
                'mchOrderNo' => 'O123456'
            ],
            'sign' => 'MOCK_SIGN' // We are not verifying response signature in this unit test strictly unless we mock sign generation
        ];
        
        // We need to bypass signature check in response or mock it correctly.
        // For simplicity, let's just mock the behavior.
        // But JeepayResponse checks signature in constructor.
        // So we need to provide a valid signature for the response data.
        
        // Let's manually generate a signature for the response mock
        $signer = new class { use \Muzi\Jeepay\Support\Signature; };
        $responseData['sign'] = $signer->sign($responseData['data'], $config['key']);

        $mockResponse = new Response(200, [], json_encode($responseData));

        $mockClient->shouldReceive('request')
            ->once()
            ->with('POST', 'https://api.jeepay.com/api/pay/unifiedOrder', Mockery::on(function ($options) {
                // Verify request params
                $params = $options['form_params'];
                return $params['mchNo'] === 'M12345678' && $params['amount'] === 100;
            }))
            ->andReturn($mockResponse);

        $pay->setHttpClient($mockClient);

        $result = $pay->unifiedOrder(
            'O123456',
            'WX_BAR',
            100,
            'Test Subject',
            'Test Body'
        );

        $this->assertEquals('P123456', $result['payOrderId']);
    }
}
