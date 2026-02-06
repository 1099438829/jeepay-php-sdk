<?php

namespace Muzi\Jeepay\Support;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Muzi\Jeepay\Common\JeepayResponse;
use Muzi\Jeepay\Exceptions\HttpException;
use Muzi\Jeepay\Exceptions\JeepayException;

class JeepayClient
{
    use Signature;

    const COMMON_PREFIX = "/api";

    protected $baseURL;
    protected $mchNo;
    protected $appId;
    protected $key;

    /**
     * @var ClientInterface
     */
    protected $httpClient;
    
    protected $headers = [];
    protected $fixedParams = [
        'signType' => 'MD5',
        'version' => '1.0',
    ];
    
    public function __construct(string $baseURL, string $mchNo, string $appId, string $key)
    {
        $this->baseURL = rtrim($baseURL, '/');
        $this->mchNo = $mchNo;
        $this->appId = $appId;
        $this->key = $key;
        $this->httpClient = new Client([
            'base_uri' => $this->baseURL,
            'timeout'  => 30.0,
        ]);
    }

    public function setHttpClient(ClientInterface $client)
    {
        $this->httpClient = $client;
    }

    public function getHttpClient(): ClientInterface
    {
        return $this->httpClient;
    }

    /**
     * 发送GET请求并获取内容
     *
     * @throws HttpException
     * @throws GuzzleException
     * @throws JeepayException
     */
    public function getContent(string $url, array $params): JeepayResponse
    {
        $data = array_merge($params, $this->fixedParams, $this->addRequiredParams());
        return $this->sendRequest('GET', $url, $data);
    }

    /**
     * 发送POST请求
     *
     * @throws HttpException
     * @throws GuzzleException
     * @throws JeepayException
     */
    public function postForm(string $url, array $data): JeepayResponse
    {
        $data = array_merge($data, $this->fixedParams, $this->addRequiredParams());
        return $this->sendRequest('POST', $url, $data);
    }

    /**
     * 发送HTTP请求
     *
     * @throws HttpException
     * @throws GuzzleException
     */
    private function sendRequest(string $method, string $url, array $data = []): JeepayResponse
    {
        // 签名
        $data['sign'] = $this->sign($data, $this->key);
        
        $options = [
            'headers' => $this->headers,
        ];

        if (strtoupper($method) === 'GET') {
            $options['query'] = $data;
        } else {
            $options['form_params'] = $data;
        }

        try {
            $response = $this->httpClient->request($method, $url, $options);
        } catch (\Exception $e) {
            throw new HttpException($e->getMessage(), $e->getCode(), $e);
        }

        if ($response->getStatusCode() !== 200) {
            throw new HttpException("Request failed with status code " . $response->getStatusCode());
        }

        return new JeepayResponse($response->getBody()->getContents(), $this->key);
    }

    /**
     * 添加必要的请求参数
     */
    private function addRequiredParams(): array
    {
        return [
            'mchNo' => $this->mchNo,
            'appId' => $this->appId,
            'reqTime' => (string) round(microtime(true) * 1000),
        ];
    }
}
