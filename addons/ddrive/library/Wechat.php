<?php

namespace addons\ddrive\library;

use think\Cache;

class Wechat
{
    /**
     * 内容安全
     *
     * @return void
     */
    public static function msgSecCheck($content, $isRef = false)
    {
        $accessToken = self::getAccessToken();
        if (!$accessToken) {
            return true;
        }
        $headers = ['Content-type: application/json'];
        $options = [
            CURLOPT_HTTPHEADER => $headers,
        ];
        $api = "https://api.weixin.qq.com/wxa/msg_sec_check?access_token=" . $accessToken;
        $res = \fast\Http::post($api, '{ "content":"' . $content . '" }', $options);
        $res = json_decode($res, true);
        // getAccessToken失效
        if ($res['errcode'] == 40001) {
            $accessToken = self::getAccessToken(true);
            if (!$isRef) {
                return self::msgSecCheck($content, true);
            } else {
                return true;
            }

        }
        return $res['errcode'] == '0';
    }

    /**
     * 获取access_token
     *
     * @return void
     */
    public static function getAccessToken($isRef = false)
    {
        if (!$isRef && Cache::get('access_token')) {
            return Cache::get('access_token');
        }
        $config = get_addon_config('ddrive');
        $appid  = $config['wx_appid'];
        $secret = $config['wx_secret'];

        $api = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appid}&secret={$secret}";
        $res = \fast\Http::get($api);
        $res = json_decode($res, true);
        if ($res['access_token']) {
            Cache::set('access_token', $res['access_token'], $res['expires_in']);
            return $res['access_token'];
        }
        return false;
    }
}
