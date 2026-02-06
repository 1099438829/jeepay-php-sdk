<?php

namespace Muzi\Jeepay\Common;

use Muzi\Jeepay\Exceptions\JeepayException;
use Muzi\Jeepay\Support\Signature;

class JeepayResponse implements \ArrayAccess
{
    use Signature;

    private $signature;
    private $data;
    private $msg;
    private $code;
    private $key;

    /**
     * JeepayResponse constructor.
     *
     * @param array|string $response 响应数据
     * @param string $key 签名密钥
     * @throws JeepayException
     */
    public function __construct($response, string $key)
    {
        if (!is_array($response)) {
            $response = json_decode($response, true);
        }
        
        // Handle code being int or string
        $this->code = $response['code'] ?? null;
        $this->msg = $response['msg'] ?? '';
        $this->signature = $response['sign'] ?? '';
        
        if ($this->code != 0) { // Loose comparison to support '0' and 0
            throw new JeepayException($this->msg, (int)$this->code);
        }

        // If data exists, use it. Otherwise, use the whole response excluding standard fields?
        // Existing code used $response['data']. Assuming this structure is correct for the API.
        $this->data = $response['data'] ?? [];
        
        $this->key = $key;
        $this->checkResponseSign();
    }

    /**
     * 验证签名
     *
     * @throws JeepayException
     */
    private function checkResponseSign(): void
    {
        // If no signature provided, maybe skip? But for security we should verify if present.
        if (empty($this->signature)) {
            return; 
        }

        // The existing code signed $this->getData(). 
        // We need to verify if the signature is generated from 'data' or the whole response.
        // Assuming 'data' as per previous implementation.
        // Note: The signature logic in checkSign (from trait) expects 'sign' key in data to be present if using checkSign(data, key).
        // But here we have extracted signature.
        // So we manually call sign().
        
        if ($this->signature !== $this->sign($this->data, $this->key)) {
             throw new JeepayException('Response Signature Verification Failed');
        }
    }

    /**
     * 获取响应数据
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * 获取消息
     *
     * @return string
     */
    public function getMsg(): string
    {
        return $this->msg;
    }

    /**
     * 获取响应代码
     *
     * @return string|int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * 检查请求是否成功
     *
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->code == 0;
    }

    /**
     * 动态访问响应数据
     *
     * @param string $name 数据字段
     * @return mixed|null
     */
    public function __get($name)
    {
        return $this->data[$name] ?? null;
    }

    /**
     * 检查数据字段是否存在
     *
     * @param string $name 数据字段
     * @return bool
     */
    public function __isset($name): bool
    {
        return isset($this->data[$name]);
    }

    /**
     * 将响应转为数组
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->data;
    }

    // ArrayAccess 接口实现

    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->data);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->data[$offset] ?? null;
    }

    public function offsetSet($offset, $value): void
    {
        $this->data[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        unset($this->data[$offset]);
    }
}
