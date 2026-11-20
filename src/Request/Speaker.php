<?php

namespace Muzi\Jeepay\Request;

use Muzi\Jeepay\Support\JeepayClient;
use Muzi\Jeepay\Exceptions\HttpException;
use Muzi\Jeepay\Exceptions\JeepayException;
use GuzzleHttp\Exception\GuzzleException;

final class Speaker extends JeepayClient
{
    const SPEAKER_PREFIX = self::COMMON_PREFIX . "/speaker";
    const PUSH_URL = self::SPEAKER_PREFIX . '/push';
    
    /**
     * 语音播报
     *
     * @param string $speak_content 商户系统自定义播报内容
     * @param int|null $store_id 门店ID，不传取商户的默认门店
     * @param int|null $amount 支付原价，单位分
     *
     * @return array
     * @throws HttpException
     * @throws JeepayException
     * @throws GuzzleException
     */
    public function speakerPush(string $speak_content, ?int $store_id = null, ?int $amount = null): array
    {
        $params = array_filter([
            'storeId' => $store_id,
            'speakContent' => $speak_content,
            'amount' => $amount,
        ], function ($value) {
            return !is_null($value);
        });

        return $this->postForm(self::PUSH_URL, $params)->toArray();
    }
}
