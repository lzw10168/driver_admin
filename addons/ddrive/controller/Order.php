<?php

namespace addons\ddrive\controller;

use addons\ddrive\extend\Common;
use addons\ddrive\library\Check;
use addons\ddrive\library\Order as Lib;
use addons\ddrive\model\Apply;
use addons\epay\library\Service;
use app\common\controller\Api;
use app\common\model\Config;
use Exception;
use think\addons\Controller;
use think\Db;
use think\Lang;
use think\Log;
use Yansongda\Pay\Pay;
use addons\ddrive\model\User;
use addons\ddrive\model\UserVerified;
use addons\ddrive\model\DdriveOrderComment;



/**
 * 订单接口
 */
class Order extends Api
{
    protected $noNeedLogin = ['notifyx', 'takingList', 'order_info', 'time_out', 'order_taking', 'order_refresh', 'order_eliminate', 'create'];
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \addons\ddrive\model\Order;
        Lang::load(APP_PATH . 'admin/lang/zh-cn/ddrive/order.php');
    }

    /**
     * 用户端-查询用户订单
     *
     * @return void
     */
    public function index()
    {
        $model    = $this->model;
        $pageSize = $this->request->param('pageSize', 10);
        $map      = [];
        // 用户身份
        if ($this->request->request('type') == 'driver') {
            $map['driver_id'] = $this->auth->id;
        } else {
            $map['user_id'] = $this->auth->id;
        }
        // 订单状态
        if ($this->request->has('status') && $this->request->request('status') != 'all') {
            $map['status'] = ['in', $this->request->param('status')];
        }
        $list = $model->where($map)->order('createtime desc')->paginate($pageSize);
        $this->success("", $list);
    }

    /**司机端-订单列表
     * order_info
     * @des
     */
    public function order_info()
    {
        $model    = $this->model;
        $pageSize = $this->request->param('pageSize', 10);
        $map      = [];
        if (!$this->auth->id) {
            $this->success("", ['date' => []]);
        }

        // 订单类型
        if ($this->request->has('order_type')) {
            $map['driver_id'] = $this->auth->id;
            if ($this->request->request('order_type') == 1) {
                $map['status'] = ['in', [1, 2, 4]];//进行中
                $list_start    = $model->where($map)->order('createtime desc')->select();

                $map['status'] = ['in', [5]];//已接单

                $list_receiving = $model->where($map)->order('createtime desc')->select();
                $list           = array_merge($list_start, $list_receiving);
                $this->success("查询成功", ['data' => $list]);
            } elseif ($this->request->request('order_type') == 2) {
                $map['status'] = ['in', [3, 99]];
                $list          = $model->where($map)->order('createtime desc')->paginate($pageSize);
                $this->success("查询成功", $list);
            }
        }
    }

    /**用户端-订单检测
     * order_info
     * @des
     */
    public function order_taking()
    {
        if (!$this->auth->id) {
            $this->success("查询成功", ['data' => []]);
        }
        $model = $this->model;
        // 订单类型
        $map['user_id'] = $this->auth->id;
        $map['status']  = ['in', [0, 1, 2, 3, 4, 5]];
        $list_start     = $model->where($map)->order('createtime desc')->select();
        $list_hy_start = (new \addons\ddrive\model\Hyorder())::with('shaddress')->where($map)->order('createtime desc')->select();
        $this->success("查询成功", ['data' => $list_start,'hy'=>$list_hy_start]);
    }

    /**继续呼叫
     * again
     * @des
     */
    public function again()
    {
        $orderId = $this->request->param('order_id');
        $info    = $this->model->get($orderId);
        if ($info['status'] == '-2') {
            $res = $this->model->where('id', $info['id'])->update(['status' => '0', 'createtime' => time()]);
            if ($res) {
                $this->success("继续呼叫成功", ['data' => $info['id']]);
            }
            $this->error('继续呼叫失败');
        }
        $this->error('继续呼叫失败');
    }

    /**
     * 订单超时
     *
     * @return boolean
     */
    public function time_out()
    {
        $time     = time() - 300;
        $order_id = $this->model->where('createtime', '<', $time)->where('status', '=', 0)->where('type', '=', 1)->column('id');
        if ($order_id) {
            $this->model->whereIn('id', $order_id)->update(['status' => '-2']);
        }
    }


    /**
     * 待接单列表
     *
     * @return void
     */
    public function takingList()
    {
        $model    = $this->model;
        $pageSize = $this->request->param('pageSize', 10);
        $city     = $this->request->param('city', '');
        if ($city) {
            $list = $model->where('status', 0)->where('start_address', 'like', '%' . $city . '%')->order('createtime desc')->paginate($pageSize);
        } else {
            $list = $model->where('status', 0)->order('createtime desc')->paginate($pageSize);
        }
        $this->success("", $list);
    }

    /**
     * 创建订单
     *
     * @return void
     */
    public function create()
    {
        $user = $this->auth->getUser();

        // 联系电话
        $mobile = $user['mobile'];
        if ($this->request->post('mobile')) {
            $mobile = $this->request->post('mobile');
        }
        $type             = 1;
        $appointment_time = 0;
        $order_pay        = $this->model->where('user_id', $this->auth->id)->where('status', 3)->find();
        if ($order_pay) {
            $this->error('您有订单待支付');
        }
        //预约时间
        if ($this->request->post('appointment_time')) {
            $type             = 2;
            $appointment_time = $this->request->post('appointment_time');
        }
        // 下单数据
        $data = [
            'mobile'           => $mobile,
            'start'            => $this->request->post('start'),
            'start_city'       => $this->request->post('start_city'),
            'start_address'    => $this->request->post('start_address'),
            'start_latitude'   => $this->request->post('start_latitude'),
            'start_longitude'  => $this->request->post('start_longitude'),
            'end'              => $this->request->post('end'),
            'end_city'         => $this->request->post('end_city'),
            'end_address'      => $this->request->post('end_address'),
            'end_latitude'     => $this->request->post('end_latitude'),
            'end_longitude'    => $this->request->post('end_longitude'),
            'distance'         => $this->request->post('distance'),
            'duration'         => $this->request->post('duration'),
            'estimated_price'  => Lib::getPrice($this->request->post('distance'), date('H', time())),
            'user_id'          => $this->auth->id,
            'reachtime'        => 0,
            'type'             => $type,
            'appointment_time' => $appointment_time,
        ];
        $rule = [
            ['start', 'require', '请填写出发地'],
            ['end', 'require', '请填写目的地'],
        ];

        (new Check())->checkParam($rule);
        $model = $this->model;
        $res   = $model->data($data)->save();
        if ($res) {
            
            $sms_config = get_addon_config('alisms');
        
            $ddr_config = get_addon_config('ddrive');
            $mobiles = explode("\r\n", $ddr_config['noticeMobile']);
            \app\common\library\Sms::notice($ddr_config['noticeMobile'], ['type'=>'代驾'], $sms_config['template']['notice']);
            
            $this->success('订单创建成功', ['order_id' => $model->id]);
        } else {
            $this->error('订单创建失败');
        }
    }

    /**
     * 订单详情
     *
     * @return void
     */
    public function info()
    {
        $orderId       = $this->request->param('order_id');
        $info          = $this->model->get($orderId);
        $info['score'] = (new DdriveOrderComment())->where('order_id', $info['id'])->value('score');
        $info['user']  = $info->user;
        if ($info->driver) {
            $info['driver'] = $info->driver;
            // 司机照片
            $apply                         = Apply::where('user_id', $info['driver']['id'])->find();
            $info['driver']['avatar']      = url("/", "", "", true) . '..' . $apply['image'];
            $info['driver']['driving_age'] = $apply['driving_age'];
            // 总里程
            $total                            = $this->model->where('driver_id', $info['driver']['id'])->sum('distance');
            $info['driver']['total_distance'] = $total ? ceil($total / 1000) : 0;
            // 接单量
            $info['driver']['total_order'] = $this->model->where('driver_id', $info['driver']['id'])->count();
            $score_sum                     = (new DdriveOrderComment())->where('driver_id', $info['driver']['id'])->sum('score');
            $score_count                   = (new DdriveOrderComment())->where('driver_id', $info['driver']['id'])->count();
            if ($score_sum) {
                $info['driver']['score'] = round($score_sum / $score_count);
            } else {
                $info['driver']['score'] = 5;
            }
        }
        // 订单结束后查询到达时间
        if ($info['status'] > 2) {
            $info['end_time']     = Db::name('ddrive_order_location')->where('order_id', $info['id'])->where('type', 2)->value('createtime');
            $info['end_datetime'] = date('Y-m-d H:i:s', $info['end_time']);
        }
        $this->success('', $info);
    }

    /**
     * 取消订单
     *
     * @return boolean
     */
    public function cancel()
    {
        $orderId     = $this->request->param('order_id');
        $cancel_type = $this->request->param('cancel_type');
        $res         = $this->model->where('id', $orderId)->update(['status' => -1, 'cancel_type' => $cancel_type]);
        if ($res) {
            $this->success('订单取消成功');
        } else {
            $this->error('订单取消失败');
        }
    }

    /**
     * 根据距离估算价格
     *
     * @return void
     */
    public function amount()
    {
        $distance = $this->request->request('distance');
        $price    = Lib::getPrice($distance);
        $this->success('', $price);
    }

    /**
     * 接单
     *
     * @return void
     */
    public function taking()
    {
        //欠缴平台服务费上限
        $restrict_service_fee = get_addon_config('ddrive')['restrict_service_fee'];
        $platform_service_fee = (new User())->where('id', $this->auth->id)->value('platform_service_fee');
        $mobile               = (new User())->where('id', $this->auth->id)->value('mobile');
        $user_verified        = (new UserVerified())->where('user_id', $this->auth->id)->find();
        if ($user_verified) {
            if ($user_verified['real_verified'] != 1) {
                $this->error('请完成实名认证');
            }
            if ($user_verified['driver_verified'] != 1) {
                $this->error('请完成驾照认证');
            }
        }
        if (!$mobile) {
            $this->error('请绑定手机号');
        }
        if ($restrict_service_fee <= $platform_service_fee) {
            $this->error('请缴纳平台服务费后接单');
        }
        $orderId = $this->request->param('order_id');
        // 判断订单是否被接单
        $order = $this->model->where('id', $orderId)->find();
        if (!$order) {
            $this->error('订单不存在');
        }
        if ($order['status'] != 0) {
            $this->error('手慢了,订单已被抢');
        }
        if ($order['type'] == 1) {
            $driver_order = $this->model->where('driver_id', $this->auth->id)->whereIn('status', [1, 2, 4, 5])->whereNotIn('type', [2])->find();
            if ($driver_order) {
                $this->error('存在未完成订单,禁止重复接单');
            }
            $status = 1;
        } else {
            $status = 5;
        }
        // 接单
        $res = $this->model->where('id', $orderId)->update(['status' => $status, 'driver_id' => $this->auth->id]);
        if ($res) {
            $this->success('接单成功');
        } else {
            $this->error('接单失败');
        }
    }

    /**
     * 到达出发地
     *
     * @return void
     */
    public function reach()
    {
        $orderId = $this->request->param('order_id');

        $res = $this->model->where('id', $orderId)->update(['status' => 4, 'reachtime' => time()]);
        if ($res) {
            $this->success('操作成功');
        } else {
            $this->error('操作失败');
        }
    }

    /**预约单开始出发
     * set_out
     * @des
     */
    public function set_out()
    {
        $orderId = $this->request->param('order_id');

        $res = $this->model->where('id', $orderId)->update(['status' => 1]);
        if ($res) {
            $this->success('操作成功');
        } else {
            $this->error('操作失败');
        }
    }

    /**线下结算
     * offline
     * @des
     */
    public function offline()
    {
        $orderId = $this->request->param('order_id');
        $res     = $this->model->where('id', $orderId)->update(['status' => 99, 'complete_time' => time()]);
        if ($res) {
            $order = $this->model->where('id', $orderId)->find();
            // 查询订单上次位置
            $location = Db::name('ddrive_order_location')->where('order_id', $orderId)->where('type', 2)->order('id desc')->find();
            $price    = Lib::getPrice($location['distance'], date('H', $order['createtime']));
            // 计算时间
            $duration = $location['createtime'] - $order['createtime'];

            $platform_service_fee      = get_addon_config('ddrive')['platform_service_fee'];
            $user_platform_service_fee = (new User())->where('id', $this->auth->id)->value('platform_service_fee');
            //累加服务费
            $user_res = (new User())->where('id', $this->auth->id)->update(['platform_service_fee' => $user_platform_service_fee + number_format(($price * ($platform_service_fee / 100)), 2)]);
            // 修改订单信息
            $data = [
                'status'               => 3,
                'price'                => $price,
                'distance'             => $location['distance'],
                'duration'             => $duration,
                'platform_service_fee' => number_format(($price * ($platform_service_fee / 100)), 2),
            ];
            if ($user_res) {
                $this->success('操作成功');
            } else {
                $this->model->where('id', $orderId)->update($data);
                $this->error('操作失败');
            }
        } else {
            $this->error('操作失败');
        }
    }

    /**
     * 立即出发，记录出发位置
     *
     * @return void
     */
    public function start()
    {
        $orderId   = $this->request->param('order_id');
        $latitude  = $this->request->param('latitude');
        $longitude = $this->request->param('longitude');
        // 判断订单是否被接单
        $order = $this->model->where('id', $orderId)->find();
        if (!$order) {
            $this->error('订单不存在');
        }
        if ($order['status'] != 4) {
            $this->error('无需重复操作');
        }
        // 接单
        $res = $this->model->where('id', $orderId)->update(['status' => 2]);
        // 记录起始位置
        $data = [
            'order_id'   => $orderId,
            'latitude'   => $latitude,
            'longitude'  => $longitude,
            'distance'   => 0, // 行驶路程为0
            'type'       => 1, // 位置类型，1为起始位置，2为当前位置
            'createtime' => time(),
        ];
        Db::name('ddrive_order_location')->insert($data);
        if ($res) {
            $distance = Lib::getDistance($latitude, $longitude, $latitude, $longitude);
            $price    = Lib::getPrice($distance, date('H', $order['createtime']), $order['createtime'], $order['reachtime']);
            $this->success("操作成功", ['price' => $price, 'distance' => $distance]);
        } else {
            $this->error('操作失败');
        }
    }

    /**
     * 更新订单位置信息，记录订单路程
     *
     * @return void
     */
    public function updateLocation()
    {
        $orderId   = $this->request->param('order_id');
        $latitude  = $this->request->param('latitude');
        $longitude = $this->request->param('longitude');
        // 查询订单上次位置
        $last = Db::name('ddrive_order_location')->where('order_id', $orderId)->where('type', 2)->order('id desc')->find();
        if (!$last) {
            $start    = Db::name('ddrive_order_location')->where('order_id', $orderId)->where('type', 1)->order('id desc')->find();
            $distance = Lib::getDistance($latitude, $longitude, $start['latitude'], $start['longitude']);
            // 首次更新位置
            $data = [
                'order_id'   => $orderId,
                'latitude'   => $latitude,
                'longitude'  => $longitude,
                'distance'   => $distance, // 行驶路程为0
                'type'       => 2,         // 位置类型，1为起始位置，2为当前位置
                'createtime' => time(),
            ];
            $res  = Db::name('ddrive_order_location')->insert($data);
        } else {
            // 之后更新位置要根据上次位置算出行驶路程
            $thisdistance = Lib::getDistance($latitude, $longitude, $last['latitude'], $last['longitude']);
            $distance     = $thisdistance + $last['distance'];
            // 更新总路程和当前位置
            $update = [
                'distance'   => $distance,
                'latitude'   => $latitude,
                'longitude'  => $longitude,
                'createtime' => time(),
            ];
            $res    = Db::name('ddrive_order_location')->where('order_id', $orderId)->update($update);
        }
        // 计算价格返回
        $order = $this->model->where('id', $orderId)->field('createtime,reachtime')->find();
        $price = Lib::getPrice($distance, date('H', $order['createtime']), $order['createtime'], $order['reachtime']);
        $this->success("", ['price' => $price, 'distance' => $distance]);
    }

    /**
     * 结束订单，记录出发位置
     *
     * @return void
     */
    public function done()
    {
        $orderId = $this->request->param('order_id');
        // 判断订单是否结束
        $order = $this->model->where('id', $orderId)->find();
        if (!$order) {
            $this->error('订单不存在');
        }
        // 查询订单上次位置
        $location = Db::name('ddrive_order_location')->where('order_id', $orderId)->where('type', 2)->order('id desc')->find();
        $price    = Lib::getPrice($location['distance'], date('H', $order['createtime']));
        // 计算时间
        $duration = $location['createtime'] - $order['createtime'];
        // 修改订单信息
        $data = [
            'status'   => 3,
            'price'    => $price,
            'distance' => $location['distance'],
            'duration' => $duration,
        ];
        $res  = $this->model->where('id', $orderId)->update($data);

        if ($res) {
            $this->success('操作成功');
        } else {
            $this->error('操作失败');
        }
    }

    /**
     * 订单支付
     *
     * @return void
     */
    public function pay()
    {
        $site    = Config::get('site');
        $orderId = $this->request->param('order_id');
        // 判断订单是否结束
        $order = $this->model->where('id', $orderId)->find();
        if (!$order) {
            $this->error('订单不存在');
        }

        $method = $this->request->param('method', 'app');

        // 用户信息
        $user = $this->auth->getUser();
        //订单标题
        $title = $site['title'] . ' - 订单费用';
        // 订单编号
        $out_trade_no = $order['id'] . '-' . time();
        // openid
        $openid = Db::name('ddrive_user_token')->where('user_id', $user['id'])->value('openid');
        //回调链接
        $notifyurl = $this->request->root(true) . '/addons/ddrive/order/notifyx/paytype/wechat';
        $params    = [
            'type'      => 'mini',
            'orderid'   => $out_trade_no,
            'title'     => $title,
            'amount'    => $order['price'],
            'method'    => $method,
            'openid'    => $openid,
            'notifyurl' => $notifyurl,
        ];
        try {
            return Service::submitOrder($params);
        } catch (\Throwable $th) {
            $this->error('获取支付信息失败');
        }

    }

    /**
     * 司机端订单支付
     *
     * @return void
     */
    public function driver_pay()
    {
        $site    = Config::get('site');
        $orderId = $this->request->param('order_id');
        $id      = $this->request->param('coupon_id');
        $code    = $this->request->param('code');
        $type    = $this->request->param('type', 'user_wechat'); // driver_wechat 司机端 user_wechat 用户端 mini_wechat 小程序
        if ($type == 'mini_wechat' && !$code) {
            $this->error('网络错误请稍后再试');
        }
        // 判断订单是否结束
        $order = $this->model->where('id', $orderId)->find();
        if (!$order) {
            $this->error('订单不存在');
        }
        if ($id) {
            $coupon_list = Db::name('user_coupon')
                ->where('id', $id)
                ->field('limit_price,coupon_price,coupon_status')
                ->find();
            if (!$coupon_list) {
                $this->error('该优惠券不存在');
            }
            if ($coupon_list['coupon_status'] == 2) {
                $this->error('该优惠券已过期');
            }
            if ($order['price'] < $coupon_list['limit_price']) {
                $this->error('该订单没达到最低抵扣额');
            }
            $order_money = $order['price'] - $coupon_list['limit_price'];
        } else {
            $order_money = $order['price'];
        }
        $method = $this->request->param('method', 'app');

        // 用户信息
        $user = $this->auth->getUser();
        //订单标题
        $title = $site['title'] . ' - 订单费用';
        // 订单编号
        $out_trade_no = $order['id'] . '-' . time();
        // openid
        $openid = '';
        if ($type == 'mini_wechat') {
            $openid = Db::name('ddrive_user_token')->where('user_id', $user['id'])->value('mini_openid');
        }
        if (!$openid && $type == 'mini_wechat') {
            $info = (new \addons\ddrive\library\Common())->getOpenid($code);
            if (isset($info['openid'])) {
                $openid = $info['openid'];
            } else {
                return json_encode(['code' => 0, 'msg' => '失败']);
            }
        }

        //回调链接
        if ($type == 'alipay') {
            $notifyurl = $this->request->root(true) . '/addons/ddrive/order/notifyx/paytype/alipay';
        } else {
            $notifyurl = $this->request->root(true) . '/addons/ddrive/order/notifyx/paytype/' . $type;
        }
        $params = [
            'type'      => $type,
            'orderid'   => $out_trade_no,
            'title'     => $title,
            'amount'    => $order_money,
            'method'    => $method,
            'openid'    => $openid,
            'notifyurl' => $notifyurl,
        ];
        try {
            if ($id) {
                $upcoupon                  = [];
                $upcoupon['order_id']      = $orderId;
                $upcoupon['usage_time']    = time();
                $upcoupon['coupon_status'] = 1;
                Db::name('user_coupon')->where('id', $id)->update($upcoupon);
            }
            $pay = json_encode(Service::submitOrder($params));
            if ($type == 'mini_wechat') {
                return json_encode(['code' => 1, 'msg' => '成功', 'data' => json_decode($pay, true)]);
            } elseif ($type == 'alipay') {
                return json_encode(['code' => 1, 'msg' => '成功', 'data' => json_decode($pay, true)]);
            } else {
                return json_encode(['code' => 1, 'msg' => '成功', 'data' => json_decode(json_decode($pay, true), true)]);
            }
        } catch (\Throwable $th) {
            return json_encode(['code' => 0, 'msg' => '失败:'.($th->raw['return_msg']?$th->raw['return_msg']:"")]);
        }

    }

    /**
     * 支付成功
     *
     * @return void
     */
    public function notifyx()
    {
        Log::record('支付回调');
        $paytype = $this->request->param('paytype');
        $pay     = \addons\epay\library\Service::checkNotify($paytype);
        if (!$pay) {
            Log::record('签名错误');
            echo '签名错误';
            return;
        }
        $data = $pay->verify();
        try {
            $payamount    = $paytype == 'alipay' ? $data['total_amount'] : $data['total_fee'] / 100;
            $out_trade_no = $data['out_trade_no'];
            Log::record('订单编号：' . $out_trade_no);
            $order_id = explode('-', $out_trade_no)[0];
            $order    = $this->model->where('id', $order_id)->find();
            if ($order['status'] != 99) {
                $this->model->where('id', $order_id)->setField('status', 99);
                $this->model->where('id', $order_id)->setField('complete_time', time());
                //平台服务费
                $platform_service_fee = get_addon_config('ddrive')['platform_service_fee'] / 100;
                $fee                  = round($platform_service_fee * $order['price'], 2);

                $this->model->where('id', $order_id)->setField('platform_service_fee', $fee);
                // 增加司机余额
                Db::name('user')->where('id', $order['driver_id'])->setInc('money', $order['price']);
                Db::name('details')->insert([
                    'user_id'        => $order['driver_id'],
                    'fluctuate_type' => 1,
                    'msg'            => '代驾收入',
                    'amount'         => $order['price'],
                    'assets_type'    => 2,
                    'source_type'    => 2,
                    'createtime'     => time(),
                    'form_id'        => $order_id,
                ]);

                // 增加会员积分
                $pointLib = new \addons\ddrive\library\Point;
                $pointLib->orderDone($order);
            }
        } catch (Exception $e) {
            Log::record($e->getMessage());
        }
        echo $pay->success();
    }

    /**
     * 订单评价
     *
     * @return void
     */
    public function comment()
    {
        $orderId = $this->request->param('order_id');
        // 判断订单是否存在
        $order = $this->model->where('id', $orderId)->find();
        if (!$order) {
            $this->error('订单不存在');
        }
        $comment = Db::name('ddrive_order_comment')->where('order_id', $orderId)->find();
        if ($comment) {
            $this->error('已评价');
        }
        $score = $this->request->param('score', 5);
        $data  = [
            'user_id'    => $this->auth->id,
            'order_id'   => $orderId,
            'score'      => $score,
            'driver_id'  => $order['driver_id'],
            'createtime' => time(),
        ];
        $res   = Db::name('ddrive_order_comment')->insert($data);

        if ($res) {
            $this->model->where('id', $orderId)->setField('comment', 1);
            $this->success('评价成功');
        } else {
            $this->error('评价失败');
        }
    }

    /**更新司机端首页订单信息
     * order_refresh
     * @des
     */
    public function order_refresh()
    {
        $orderId = $this->request->param('order_id');
        $city    = $this->request->param('city');
        if (!$orderId) {
            $info = $this->model->where('start_address', 'like', '%' . $city . '%')->order('createtime desc')->where('status', '0')->select();
            $this->success('', $info);
        }
        // 判断订单是否存在
        $createtime = $this->model->where('id', $orderId)->value('createtime');
        if (!$createtime) {
            $this->success('', []);
        }
        $info = $this->model->where('createtime', '>', $createtime)->where('start_address', 'like', '%' . $city . '%')->where('status', '0')->select();
        if ($info) {
            $this->success('', $info);
        }
        $this->success('', []);
    }

    /**剔除司机端首页订单信息
     * order_refresh
     * @des
     */
    public function order_eliminate()
    {
        $city = $this->request->param('city');

        $info = $this->model->where('createtime', '>', time() - 303)->where('start_address', 'like', '%' . $city . '%')->where('status', '<>', '0')->column('id');
        if ($info) {
            $this->success('', $info);
        }
        $this->success('', []);
    }

}