<?php

namespace addons\epay\controller;

use addons\epay\library\QRCode;
use addons\epay\library\Service;
use addons\epay\library\Wechat;
use addons\third\model\Third;
use app\admin\model\Details;
use app\common\library\Auth;
use think\addons\Controller;
use think\Db;
use think\Response;
use think\Session;
use Yansongda\Pay\Exceptions\GatewayException;
use Yansongda\Pay\Pay;

/**
 * API接口控制器
 *
 * @package addons\epay\controller
 */
class Api extends Controller
{

    protected $layout = 'default';
    protected $config = [];

    /**
     * 默认方法
     */
    public function index()
    {
        return;
    }

    /**
     * 外部提交
     */
    public function submit()
    {
        $this->request->filter('trim');
        $out_trade_no = $this->request->request("out_trade_no");
        $title        = $this->request->request("title");
        $amount       = $this->request->request('amount');
        $type         = $this->request->request('type');
        $method       = $this->request->request('method', 'web');
        $openid       = $this->request->request('openid', '');
        $auth_code    = $this->request->request('auth_code', '');
        $notifyurl    = $this->request->request('notifyurl', '');
        $returnurl    = $this->request->request('returnurl', '');

        if (!$amount || $amount < 0) {
            $this->error("支付金额必须大于0");
        }

        if (!$type || !in_array($type, ['alipay', 'wechat'])) {
            $this->error("支付类型错误");
        }

        $params = [
            'type'         => $type,
            'out_trade_no' => $out_trade_no,
            'title'        => $title,
            'amount'       => $amount,
            'method'       => $method,
            'openid'       => $openid,
            'auth_code'    => $auth_code,
            'notifyurl'    => $notifyurl,
            'returnurl'    => $returnurl,
        ];
        return Service::submitOrder($params);
    }

    /**
     * 微信支付(公众号支付&PC扫码支付)
     * @return string
     */
    public function wechat()
    {
        $config = Service::getConfig('wechat');

        $isWechat = stripos($this->request->server('HTTP_USER_AGENT'), 'MicroMessenger') !== false;
        $isMobile = $this->request->isMobile();
        $this->view->assign("isWechat", $isWechat);
        $this->view->assign("isMobile", $isMobile);

        //发起PC支付(Scan支付)(PC扫码模式)
        if ($this->request->isAjax()) {
            $pay     = Pay::wechat($config);
            $orderid = $this->request->post("orderid");
            try {
                $result = $pay->find($orderid);
                if ($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS') {
                    $this->success("", "", ['status' => $result['trade_state']]);
                } else {
                    $this->error("查询失败");
                }
            } catch (GatewayException $e) {
                $this->error("查询失败");
            }
        }

        $orderData = Session::get("wechatorderdata");
        if (!$orderData) {
            $this->error("请求参数错误");
        }
        if ($isWechat) {
            //发起公众号(jsapi支付),openid必须

            //如果没有openid，则自动去获取openid
            if (!isset($orderData['openid']) || !$orderData['openid']) {
                $orderData['openid'] = Service::getOpenid();
            }

            $orderData['method'] = 'mp';
            $type                = 'jsapi';
            $payData             = Service::submitOrder($orderData);
            if (!isset($payData['paySign'])) {
                $this->error("创建订单失败，请返回重试", "");
            }
        } else {
            $orderData['method'] = 'scan';
            $type                = 'pc';
            $payData             = Service::submitOrder($orderData);
            if (!isset($payData['code_url'])) {
                $this->error("创建订单失败，请返回重试", "");
            }
        }
        $this->view->assign("orderData", $orderData);
        $this->view->assign("payData", $payData);
        $this->view->assign("type", $type);

        $this->view->assign("title", "微信支付");
        return $this->view->fetch();
    }

    /**
     * 支付宝支付(PC扫码支付)
     * @return string
     */
    public function alipay()
    {
        $config = Service::getConfig('alipay');

        $isWechat = stripos($this->request->server('HTTP_USER_AGENT'), 'MicroMessenger') !== false;
        $isMobile = $this->request->isMobile();
        $this->view->assign("isWechat", $isWechat);
        $this->view->assign("isMobile", $isMobile);

        if ($this->request->isAjax()) {
            $orderid = $this->request->post("orderid");
            $pay     = Pay::alipay($config);
            try {
                $result = $pay->find($orderid);
                if ($result['code'] == '10000' && $result['trade_status'] == 'TRADE_SUCCESS') {
                    $this->success("", "", ['status' => $result['trade_status']]);
                } else {
                    $this->error("查询失败");
                }
            } catch (GatewayException $e) {
                $this->error("查询失败");
            }
        }

        //发起PC支付(Scan支付)(PC扫码模式)
        $orderData = Session::get("alipayorderdata");
        if (!$orderData) {
            $this->error("请求参数错误");
        }

        $orderData['method'] = 'scan';
        $payData             = Service::submitOrder($orderData);
        if (!isset($payData['qr_code'])) {
            $this->error("创建订单失败，请返回重试");
        }

        $type = 'pc';
        $this->view->assign("orderData", $orderData);
        $this->view->assign("payData", $payData);
        $this->view->assign("type", $type);
        $this->view->assign("title", "支付宝支付");
        return $this->view->fetch();
    }

    /**
     *签名算法
     */
    private function signs($data)
    {
        unset($data['sign']);
        unset($data['type']);
        ksort($data);
        $stringA = '';
        foreach ($data as $key => $value) {
            if (!$value) continue;
            if ($stringA) $stringA .= '&' . $key . "=" . $value;
            else $stringA = $key . "=" . $value;
        }
        $wx_key         = config('platform.wechat')['key'] ?? ''; //申请支付后有给予一个商户账号和密码，登陆后自己设置key
        $stringSignTemp = $stringA . '&key=' . $wx_key; //申请支付后有给予一个商户账号和密码，登陆后自己设置key
        return strtoupper(md5($stringSignTemp));
    }

    /**
     * 支付成功回调
     */
    public function notifyx()
    {
        $xml = file_get_contents("php://input");
        file_put_contents('55.txt', $xml);
        $notifyx_data = $this->xml_to_array($xml);
        $sign         = $notifyx_data['sign'];
        trace('回调信息' . json_encode($notifyx_data), 'pay');
        if ($this->signs($notifyx_data) != $sign) {
            trace('签名错误' . json_encode($notifyx_data), 'pay');
            echo '签名错误';
            return;
        } else {
            trace('成功', 'pay');
        }
        if ($notifyx_data['return_code'] != "SUCCESS" || $notifyx_data['result_code'] != "SUCCESS") {
            $errorMsg = '支付回调错误:' . json_encode($notifyx_data);
            trace($errorMsg, 'pay');
            echo "fail";
            return;
        }
//        $type = $this->request->param('type');
//        if (!Service::checkNotify($type)) {
//            echo '签名错误';
//            return;
//        }

        //你可以在这里你的业务处理逻辑,比如处理你的订单状态、给会员加余额等等功能
        Db::startTrans();
        try {
            $re = $this->notifyxPay($notifyx_data);
            if ($re !== true) {
                exception($re);
            }
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $errorMsg = '支付回调业务处理错误:' . ($e->getMessage());
            trace($errorMsg . json_encode($notifyx_data), 'pay');
            echo "fail";
            return;
        }
        //下面这句必须要执行,且在此之前不能有任何输出
        echo "success";
        return;
    }

    /**
     * 支付成功返回
     */
    public function returnx()
    {
        $type = $this->request->param('type');
        if (Service::checkReturn($type)) {
            echo '签名错误';
            return;
        }

        //你可以在这里定义你的提示信息,但切记不可在此编写逻辑
        $this->success("恭喜你！支付成功!", addon_url("epay/index/index"));

        return;
    }

    /**
     * 生成二维码
     */
    public function qrcode()
    {
        $text = $this->request->get('text', 'hello world');

        //如果有安装二维码插件，则调用插件的生成方法
        if (class_exists("\addons\qrcode\library\Service") && get_addon_info('qrcode')['state']) {
            $qrCode   = \addons\qrcode\library\Service::qrcode(['text' => $text]);
            $response = Response::create()->header("Content-Type", "image/png");

            header('Content-Type: ' . $qrCode->getContentType());
            $response->content($qrCode->writeString());
            return $response;
        } else {
            $qr = QRCode::getMinimumQRCode($text);
            $im = $qr->createImage(8, 5);
            header("Content-type: image/png");
            imagepng($im);
            imagedestroy($im);
            return;
        }
    }

}
