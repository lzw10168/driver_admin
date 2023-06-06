<?php

namespace addons\epay\library;

use addons\third\model\Third;
use app\common\library\Auth;
use Exception;
use think\Env;
use think\Session;
use Yansongda\Pay\Pay;

/**
 * 订单服务类
 *
 * @package addons\epay\library
 */
class Service
{

    /**
     * 提交订单
     * @param array|float $amount    订单金额
     * @param string      $orderid   订单号
     * @param string      $type      支付类型,可选alipay或wechat
     * @param string      $title     订单标题
     * @param string      $notifyurl 通知回调URL
     * @param string      $returnurl 跳转返回URL
     * @param string      $method    支付方法
     * @return Response|RedirectResponse|Collection
     * @throws Exception
     */
    public static function submitOrder($amount, $orderid = null, $type = null, $title = null, $notifyurl = null, $returnurl = null, $method = null, $openid = '')
    {
        if (!is_array($amount)) {
            $params = [
                'amount'    => 0.01,
                'orderid'   => $orderid,
                'type'      => $type,
                'title'     => $title,
                'notifyurl' => $notifyurl,
                'returnurl' => $returnurl,
                'method'    => $method,
                'openid'    => $openid,
            ];
        } else {
            $params = $amount;
        }
        $type = isset($params['type']) && in_array($params['type'], ['alipay', 'driver_wechat', 'user_wechat', 'mini_wechat']) ? $params['type'] : 'user_wechat';
        $method = isset($params['method']) ? $params['method'] : 'web';
        $orderid = isset($params['orderid']) ? $params['orderid'] : date("YmdHis") . mt_rand(100000, 999999);
        $amount = isset($params['amount']) ? $params['amount'] : 1;
        $title = isset($params['title']) ? $params['title'] : "支付";
        $auth_code = isset($params['auth_code']) ? $params['auth_code'] : '';
        $openid = isset($params['openid']) ? $params['openid'] : '';

        $request = request();
        $notifyurl = isset($params['notifyurl']) ? $params['notifyurl'] : $request->root(true) . '/addons/epay/index/' . $type . 'notify';
        $returnurl = isset($params['returnurl']) ? $params['returnurl'] : $request->root(true) . '/addons/epay/index/' . $type . 'return/out_trade_no/' . $orderid;
        $html = '';
        $config = Service::getConfig($type);
        $config['notify_url'] = $notifyurl;
        $config['return_url'] = $returnurl;
        $isWechat = strpos($request->server('HTTP_USER_AGENT'), 'MicroMessenger') !== false;

        $result = null;
        if ($type == 'alipay') {
            //如果是PC支付,判断当前环境,进行跳转
            if ($method == 'web') {
                //如果是微信环境或后台配置PC使用扫码支付
                if ($isWechat || $config['scanpay']) {
                    Session::set("alipayorderdata", $params);
                    $url = addon_url('epay/api/alipay', [], true, true);
                    return RedirectResponse::create($url);
                } elseif ($request->isMobile()) {
                    $method = 'wap';
                }
            }
            //创建支付对象
            $pay = Pay::alipay($config);
            $params = [
                'out_trade_no' => $orderid,//你的订单号
                'total_amount' => $amount,//单位元
                'subject'      => $title,
            ];

            switch ($method) {
                case 'web':
                    //电脑支付
                    $result = $pay->web($params);
                    break;
                case 'wap':
                    //手机网页支付
                    $result = $pay->wap($params);
                    break;
                case 'app':
                    //APP支付
                    $result = $pay->app($params);
                    break;
                case 'scan':
                    //扫码支付
                    $result = $pay->scan($params);
                    break;
                case 'pos':
                    //刷卡支付必须要有auth_code
                    $params['auth_code'] = $auth_code;
                    $result = $pay->pos($params);
                    break;
                default:
            }
        } else {
            //如果是PC支付,判断当前环境,进行跳转
            if ($method == 'web') {
                //如果是移动端，但不是微信环境
                if ($request->isMobile() && !$isWechat) {
                    $method = 'wap';
                } else {
                    Session::set("wechatorderdata", $params);
                    $url = addon_url('epay/api/wechat', [], true, true);
                    return RedirectResponse::create($url);
                }
            }
            //创建支付对象
            $pay = Pay::wechat($config);
            $params = [
                'out_trade_no' => $orderid,//你的订单号
                'body'         => $title,
                'total_fee'    => $amount * 100, //单位分
            ];
            switch ($method) {
                //case 'web':
                //    //电脑支付,跳转到自定义展示页面(FastAdmin独有)
                //    $result = $pay->web($params);
                //    break;
                case 'mp':
                    //公众号支付
                    //公众号支付必须有openid
                    $params['openid'] = $openid;
                    $result = $pay->mp($params);
                    break;
                case 'wap':
                    //手机网页支付,跳转
                    $params['spbill_create_ip'] = $request->ip(0, false);
                    $result = $pay->wap($params);
                    break;
                case 'app':
                    //APP支付,直接返回字符串
                    $result = $pay->app($params);
                    break;
                case 'scan':
                    //扫码支付,直接返回字符串
                    $result = $pay->scan($params);
                    break;
                case 'pos':
                    //刷卡支付,直接返回字符串
                    //刷卡支付必须要有auth_code
                    $params['auth_code'] = $auth_code;
                    $result = $pay->pos($params);
                    break;
                case 'miniapp':
                    //小程序支付,直接返回字符串
                    //小程序支付必须要有openid
                    $params['openid'] = $openid;
                    $result = $pay->miniapp($params);
                    break;
                default:
            }
        }

        //使用重写的Response类、RedirectResponse、Collection类
        if ($result instanceof \Symfony\Component\HttpFoundation\RedirectResponse) {
            $result = RedirectResponse::create($result->getTargetUrl());
        } elseif ($result instanceof \Symfony\Component\HttpFoundation\Response) {
            $result = Response::create($result->getContent());
        } elseif ($result instanceof \Yansongda\Supports\Collection) {
            $result = Collection::make($result->all());
        }

        return $result;
    }

    /**
     * 验证回调是否成功
     * @param string $type   支付类型
     * @param array  $config 配置信息
     * @return bool|\Yansongda\Pay\Gateways\Alipay|\Yansongda\Pay\Gateways\Wechat
     */
    public static function checkNotify($type, $config = [])
    {
        $type = strtolower($type);
        if (!in_array($type, ['alipay', 'driver_wechat', 'user_wechat', 'mini_wechat'])) {
            return false;
        }
        try {
            $config = self::getConfig($type);
            $pay = $type != 'alipay' ? Pay::wechat($config) : Pay::alipay($config);
            $data = $pay->verify();

            if ($type == 'alipay') {
                if (in_array($data['trade_status'], ['TRADE_SUCCESS', 'TRADE_FINISHED'])) {
                    return $pay;
                }
            } else {
                return $pay;
            }
        } catch (Exception $e) {
            return false;
        }

        return false;
    }

    /**
     * 验证返回是否成功，请勿用于判断是否支付成功的逻辑验证
     * 已弃用
     *
     * @param string $type   支付类型
     * @param array  $config 配置信息
     * @return bool
     * @deprecated  已弃用，请勿用于逻辑验证
     */
    public static function checkReturn($type, $config = [])
    {
        //由于PC及移动端无法获取请求的参数信息，取消return验证，均返回true
        return true;
    }

    /**
     * 获取配置
     * @param string $type 支付类型
     * @return array|mixed
     */
    public static function getConfig($type = 'wechat')
    {
        $ddrive = get_addon_config('ddrive');
        $request = request();
        if($type == 'driver_wechat'){
            $wechat = [
                'appid' => $ddrive['driver_wx_appid'],
                //'app_secret' => '67c4edea3897b8bbdddfff45a95631de',
                'mch_id' => $ddrive['mch_id'],
                'key' => $ddrive['key'],
                'notify_url' => $request->root(true) . '/addons/epay/api/notifyx?type=driver_wechat',
                'cert_client' => Env::get('app.wechatpay_cert_client', '/addons/epay/certs/apiclient_cert.pem'),
                'cert_key' => Env::get('app.wechatpay_cert_key', '/addons/epay/certs/apiclient_key.pem'),
            ];
        }elseif ($type == 'user_wechat'){
            $wechat = [
                'appid' => $ddrive['user_wx_appid'],
                //'app_secret' => '67c4edea3897b8bbdddfff45a95631de',
                'mch_id' => $ddrive['mch_id'],
                'key' => $ddrive['key'],
                'notify_url' => $request->root(true) . '/addons/ddrive/order/notifyx/paytype/user_wechat',
                'cert_client' => '/addons/epay/certs/apiclient_cert.pem',
                'cert_key' => '/addons/epay/certs/apiclient_key.pem',
            ];
        }elseif($type == 'mini_wechat'){
            $wechat = [
                'miniapp_id' => $ddrive['wx_appid'],
                //'app_secret' => '67c4edea3897b8bbdddfff45a95631de',
                'mch_id' => $ddrive['mch_id'],
                'key' => $ddrive['key'],
                'notify_url' => $request->root(true) . '/addons/ddrive/order/notifyx/paytype/mini_wechat',
                'cert_client' => Env::get('app.wechatpay_cert_client', '/addons/epay/certs/apiclient_cert.pem'),
                'cert_key' => Env::get('app.wechatpay_cert_key', '/addons/epay/certs/apiclient_key.pem'),
            ];
        }else{
            $alipay = [
                'app_id' => $ddrive['user_alipay_app_id'],
                //'app_secret' => '67c4edea3897b8bbdddfff45a95631de',
                'ali_public_key' => $ddrive['user_alipay_ali_public_key'],
                'private_key' => $ddrive['user_alipay_private_key'],
                'notify_url' =>$request->root(true) .  '/addons/ddrive/order/notifyx/paytype/alipay',
            ];
        }
        if($type == 'alipay'){
            $config['alipay'] = $alipay;
        }else{
            $config['wechat'] = $wechat;
        }
        $config = isset($config[$type]) ? $config[$type] : $config['wechat'];
        if (isset($config['log'])) {
            $config['log'] = [
                'file'  => LOG_PATH . 'epaylogs' . DS . $type . '-' . date("Y-m-d") . '.log',
                'level' => 'debug'
            ];
        }
        if (isset($config['cert_client']) && substr($config['cert_client'], 0, 8) == '/addons/') {
            $config['cert_client'] = ROOT_PATH . str_replace('/', DS, substr($config['cert_client'], 1));
        }
        if (isset($config['cert_key']) && substr($config['cert_key'], 0, 8) == '/addons/') {
            $config['cert_key'] = ROOT_PATH . str_replace('/', DS, substr($config['cert_key'], 1));
        }
        // 可选
        $config['http'] = [
            'timeout'         => 10,
            'connect_timeout' => 10,
            // 更多配置项请参考 [Guzzle](https://guzzle-cn.readthedocs.io/zh_CN/latest/request-options.html)
        ];

        $config['notify_url'] = empty($config['notify_url']) ? addon_url('epay/api/notifyx', [], false) . '/type/' . $type : $config['notify_url'];
        $config['notify_url'] = !preg_match("/^(http:\/\/|https:\/\/)/i", $config['notify_url']) ? request()->root(true) . $config['notify_url'] : $config['notify_url'];
        $config['return_url'] = empty($config['return_url']) ? addon_url('epay/api/returnx', [], false) . '/type/' . $type : $config['return_url'];
        $config['return_url'] = !preg_match("/^(http:\/\/|https:\/\/)/i", $config['return_url']) ? request()->root(true) . $config['return_url'] : $config['return_url'];
        return $config;
    }

    /**
     * 获取微信Openid
     *
     * @return mixed|string
     */
    public static function getOpenid()
    {
        $config = self::getConfig('wechat');
        $openid = '';
        $auth = Auth::instance();
        if ($auth->isLogin()) {
            $third = get_addon_info('third');
            if ($third && $third['state']) {
                $thirdInfo = Third::where('user_id', $auth->id)->where('platform', 'wechat')->where('apptype', 'mp')->find();
                $openid = $thirdInfo ? $thirdInfo['openid'] : '';
            }
        }
        if (!$openid) {
            $openid = Session::get("openid");

            //如果未传openid，则去读取openid
            if (!$openid) {
                $wechat = new Wechat($config['app_id'], $config['app_secret']);
                $openid = $wechat->getOpenid();
            }
        }
        return $openid;
    }

}
