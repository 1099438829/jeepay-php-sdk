<?php

namespace Muzi\Jeepay\Support;

trait Signature
{
    /**
     * 验证签名
     *
     * @param array $data
     * @param string $key
     * @return bool
     */
    public function checkSign(array $data, string $key): bool
    {
        if (!isset($data['sign'])) {
            return false;
        }
        $sign = $data['sign'];
        unset($data['sign']);
        return $sign === $this->sign($data, $key);
    }

    /**
     * 生成签名
     *
     * @param array $data
     * @param string $key
     * @return string
     */
    public function sign(array $data, string $key): string
    {
        ksort($data);
        $md5str = "";
        foreach ($data as $k => $val) {
            if ($val === null) {
                continue;
            }
            $strVal = (string)$val;
            if ($strVal === '') {
                continue;
            }
            $md5str .= $k . "=" . $strVal . "&";
        }
        return strtoupper(md5($md5str . "key=" . $key));
    }
}
