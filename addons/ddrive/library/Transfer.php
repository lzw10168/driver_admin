<?php

namespace addons\ddrive\library;

class Transfer
{
    /**
     * 关于微信企业付款的说明
     * 1.微信企业付款要求必传证书，需要到https://pay.weixin.qq.com 账户中心->账户设置->API安全->下载证书，证书路径在第207行和210行修改
     * 2.错误码参照 ：https://pay.weixin.qq.com/wiki/doc/api/tools/mch_pay.php?chapter=14_2
     */

    /**
     * 企业付款
     * @param string $platform  openid所属平台，media_platform：公众号，mini_program：小程序
     * @param string $openid 调用【网页授权获取用户信息】接口获取到用户在该公众号下的Openid
     * @param float $totalFee 收款总费用 单位元
     * @param string $outTradeNo 唯一的订单号
     * @param  string  $desc 企业付款操作说明信息
     * @param string $trueName 用户真实姓名，不传递则不校验
     * @return boolean
     */
    public static function pay($platform, $openid, $totalFee, $outTradeNo, $desc = "付款", $trueName = "")
    {
        $epay = get_addon_info('epay');
        if (!$epay) {
            throw new \Exception("请安装[微信支付宝整合-epay]插件");
        }

        $config = get_addon_config('epay')['wechat'];
        if ($platform == 'media_platform') {
            $appid = $config['app_id'];
        }
        if ($platform == 'mini_program') {
            $appid = $config['miniapp_id'];
        }

        $unified = array(
            'mch_appid'        => $appid,
            'mchid'            => $config['mch_id'],
            'nonce_str'        => self::createNonceStr(),
            'openid'           => $openid,
            'check_name'       => 'NO_CHECK', //校验用户姓名选项。NO_CHECK：不校验真实姓名，FORCE_CHECK：强校验真实姓名
            'partner_trade_no' => $outTradeNo,
            'spbill_create_ip' => '127.0.0.1',
            'amount'           => intval($totalFee * 100), //单位 转为分
            'desc'             => $desc,
        );
        if ($trueName) {
            $unified['check_name']   = 'FORCE_CHECK';
            $unified['re_user_name'] = 'trueName';
        }
        $unified['sign'] = self::getSign($unified, $config['key']);
        $responseXml     = \fast\Http::post('https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers', self::arrayToXml($unified), [
            CURLOPT_SSLCERTTYPE => 'PEM',
            CURLOPT_SSLCERT     => ADDON_PATH . $config['cert_client'],
            CURLOPT_SSLKEYTYPE  => 'PEM',
            CURLOPT_SSLKEY      => ADDON_PATH . $config['cert_key'],
        ]);
        $unifiedOrder = simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($unifiedOrder === false) {
            throw new \Exception("parse xml error");
        }

        if ($unifiedOrder->return_code != 'SUCCESS') {
            throw new \Exception($unifiedOrder->return_msg);
        }
        if ($unifiedOrder->result_code != 'SUCCESS') {
            throw new \Exception($unifiedOrder->err_code_des);
        }
        return true;
    }

    public static function createNonceStr($length = 16)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $str   = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
    public static function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }

        }
        $xml .= "</xml>";
        return $xml;
    }

    public static function getSign($params, $key)
    {
        ksort($params, SORT_STRING);
        $unSignParaString = self::formatQueryParaMap($params, false);
        $signStr          = strtoupper(md5($unSignParaString . "&key=" . $key));
        return $signStr;
    }
    protected static function formatQueryParaMap($paraMap, $urlEncode = false)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if (null != $v && "null" != $v) {
                if ($urlEncode) {
                    $v = urlencode($v);
                }
                $buff .= $k . "=" . $v . "&";
            }
        }
        $reqPar = '';
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }
}
