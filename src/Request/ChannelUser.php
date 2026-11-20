<?php

namespace Muzi\Jeepay\Request;

use Muzi\Jeepay\Support\JeepayClient;
use Muzi\Jeepay\Exceptions\HttpException;
use Muzi\Jeepay\Exceptions\JeepayException;
use GuzzleHttp\Exception\GuzzleException;

final class ChannelUser extends JeepayClient
{
    const CHANNEL_USER_PREFIX = self::COMMON_PREFIX . "/channelUserId";
    const JUMP_URL = self::CHANNEL_USER_PREFIX . '/jumpUrl';

    /**
     * 获取渠道用户ID跳转地址
     *
     * @param string $if_code 接口代码 (如: WX_JSAPI, ALIPAY_WAP)
     * @param string $redirect_url 回调地址
     * @param string|null $ext_param 扩展参数
     * @return string 跳转URL
     * @throws GuzzleException
     * @throws HttpException
     * @throws JeepayException
     */
    public function jumpUrl(
        string $if_code,
        string $redirect_url,
        ?string $ext_param = null
    ): string {
        $params = [
            'ifCode' => $if_code,
            'redirectUrl' => $redirect_url,
            'extParam' => $ext_param,
        ];
        
        // This endpoint usually redirects or returns a URL. 
        // In Jeepay, it returns a JSON with jumpUrl.
        $response = $this->postForm(self::JUMP_URL, $params);
        return $response['jumpUrl'];
    }
}
