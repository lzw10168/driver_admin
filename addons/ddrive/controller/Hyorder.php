<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/2/22
 * Time: 15:42
 */

namespace addons\ddrive\controller;

use addons\ddrive\library\Common;
use addons\ddrive\model\DdriveHyOrderComment;
use addons\ddrive\model\UserVerified;
use addons\ddrive\model\DriverRefund;
use addons\epay\library\Service;
use app\admin\model\DdriveSfOrderComment;
use app\common\controller\Api;
use addons\ddrive\library\Hyorder as Hy;
use addons\ddrive\library\Check;
use think\Db;
use think\Lang;
use think\Log;

class Hyorder extends Api
{
    protected $noNeedLogin = ['notifyx','amount'];
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \addons\ddrive\model\Hyorder;
        Lang::load(APP_PATH . 'admin/lang/zh-cn/ddrive/hyorder.php');
    }

    /**
     * 创建订单
     *
     * @return void
     */
    public function create()
    {
        $params        = $this->request->param();
        $start_address = str_replace('&quot;', '"', $params['start_address']);
        $start_address = json_decode($start_address, true);
        $sh_address    = str_replace('&quot;', '"', $params['sh_address']);
        $sh_address    = json_decode($sh_address, true);
        if (empty($sh_address) || empty($start_address)) {
            $this->error('请选择地址');
        }
        $address = [];
        foreach ($sh_address as $k => $v) {
            $address[$k]['mobile']       = $v['tel'];
            $address[$k]['end']          = $v['address']['name'];
            $address[$k]['end_city']     = $v['address']['city'];
            $address[$k]['end_address']  = $v['address']['address'];
            $address[$k]['end_lat']      = $v['address']['latitude'];
            $address[$k]['end_lng']      = $v['address']['longitude'];
            $address[$k]['floor']        = $v['floor'];
            $address[$k]['house_number'] = $v['code'];
        }
        if (empty($address)) {
            $this->error('请选择收获地址');
        }
        $rule = [
            ['people_num', 'require', '请选中跟车人数'],
            ['car_id', 'require', '请选择运输车辆'],
        ];
        (new Check())->checkParam($rule);
        $carInfo = (new \addons\ddrive\model\Freight())->where('id', $params['car_id'])->field('start_price,start_mileage,section1_price,section1_min_mileage,section1_max_mileage,section2_price,section2_mileage')->find();
        if (!$carInfo) {
            $this->error('请先选择车辆');
        }
        // 计算距离
        $distance = Hy::GetDistance($start_address['latitude'], $start_address['longitude'], $address[0]['end_lat'], $address[0]['end_lng']); //计算两点间距离
        foreach ($address as $k => $v) {
            if (isset($address[$k + 1])) {
                $distance2 = Hy::GetDistance($v['end_lat'], $v['end_lng'], $address[$k + 1]['end_lat'], $address[$k + 1]['end_lng']); //计算两点间距离
                $distance  = $distance + $distance2;
            }
        }
        $is_transport = '0';
        $order_price = Hy::getPrice($distance, $carInfo);
        $demand      = $params['demand'] ? explode(',', $params['demand']) : $params['demand'];
        if (is_array($demand)) {
            if (in_array('2', $demand)) {
                $order_price = $order_price + round($order_price * 0.4, 1);
            }
            if (in_array('1', $demand)) {
                $is_transport = 1;
            }
        }
        if($params['pay_method'] == 2 &&$params['coupon_id']){
            $this->error('暂不支持货到付款');
        }
        if ($params['coupon_id']) {
            $coupon_list = Db::name('user_coupon')
                ->where('id', $params['coupon_id'])
                ->field('limit_price,coupon_price,coupon_status')
                ->find();
            if (!$coupon_list) {
                $this->error('该优惠券不存在');
            }
            if ($coupon_list['coupon_status'] == 2) {
                $this->error('该优惠券已过期');
            }
            if ($order_price < $coupon_list['limit_price']) {
                $this->error('该订单没达到最低抵扣额');
            }
            $discount_price = $order_price - $coupon_list['coupon_price'];
        } else {
            $discount_price = $order_price;
        }
        $user = $this->auth->getUser();
        Db::startTrans();
        try {
            $data     = [
                'user_id'          => $this->auth->id,
                'out_trade_no'     => Hy::createOrderSn(),
                'mobile'           => $params['mobile'] ? $params['mobile'] : $user['mobile'],
                'start_mobile'     => '',
                'car_id'           => $params['car_id'],
                'type'             => $params['appointment_time'] ? 2 : 1,
                'appointment_time' => $params['appointment_time'] ? strtotime($params['appointment_time']) : 0,
                'people_num'       => $params['people_num'],
                'demand'           => $params['demand'],
                'order_price'      => $order_price,
                'discount_price'   => $discount_price,
                'is_transport'     => $is_transport,
                'pay_method'       => $params['pay_method'],
                'remark'           => $params['remark'],
                'start'            => $start_address['name'],
                'start_city'       => $start_address['city'],
                'start_address'    => $start_address['address'],
                'start_lat'        => $start_address['latitude'],
                'start_lng'        => $start_address['longitude'],
                'status'           => $params['pay_method'] == 1 ? 7 : 0,
                'createtime'       => time(),
                'distance'         => $distance,
            ];
            $order_id = Db::name('ddrive_hy_order')->insertGetId($data);
            foreach ($address as $k => $v) {
                $address[$k]['order_id']   = $order_id;
                $address[$k]['createtime'] = time();
            }
            Db::name('ddrive_hy_address')->insertAll($address);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $this->error('预约失败');
        }
        
        $sms_config = get_addon_config('alisms');
        $ddr_config = get_addon_config('ddrive');
        $mobiles = explode("\r\n", $ddr_config['noticeMobile']);
        \app\common\library\Sms::notice($ddr_config['noticeMobile'], ['type'=>'货运'], $sms_config['template']['notice']);
        
        $this->success('预约成功', ['order_id' => $order_id]);
    }

    /**
     * 根据距离估算价格
     *
     * @return void
     */
    public function amount()
    {
        $start_lat = $this->request->param('start_lat');
        $start_lng = $this->request->param('start_lng');
        $car_id    = $this->request->param('car_id');
        $demand    = $this->request->param('demand');
        $coupond_id    = $this->request->param('coupond_id');
        $carInfo   = (new \addons\ddrive\model\Freight())->where('id', $car_id)->field('start_price,start_mileage,section1_price,section1_min_mileage,section1_max_mileage,section2_price,section2_mileage')->find();
        if (!$carInfo) {
            $this->error('请先选择车辆');
        }
        $end_address = str_replace('&quot;', '"', $this->request->param('end_address'));
        $end_address = json_decode($end_address, true);
        if (empty($end_address)) {
            $this->error('请选择收获地址');
        }
        $end_new_address = [];
        $address_num     = 0;
        foreach ($end_address as $k => $v) {
            $end_new_address[] = $v['address'];
            $address_num       += 1;
        }
        if (empty($end_new_address)) {
            $this->error('请选择收获地址');
        }

        $distance = Hy::GetDistance($start_lat, $start_lng, $end_new_address[0]['latitude'], $end_new_address[0]['longitude']); //计算两点间距离
        foreach ($end_new_address as $k => $v) {
            if (isset($end_new_address[$k + 1])) {
                $distance2 = Hy::GetDistance($v['latitude'], $v['longitude'], $end_new_address[$k + 1]['latitude'], $end_new_address[$k + 1]['longitude']); //计算两点间距离
                $distance  = $distance + $distance2;
            }
        }
        $price = Hy::getPrice($distance, $carInfo);
        if (isset($demand)) {
            if ($demand == 1) {
                $price = $price + round($price * 0.4, 1);
            }
        }
        if ($coupond_id) {
            $coupon_list = Db::name('user_coupon')
                ->where('id', $coupond_id)
                ->field('limit_price,coupon_price,coupon_status')
                ->find();
            if (!$coupon_list) {
                $this->error('该优惠券不存在');
            }
            if ($coupon_list['coupon_status'] == 2) {
                $this->error('该优惠券已过期');
            }
            if ($price < $coupon_list['limit_price']) {
                $this->error('该订单没达到最低抵扣额');
            }
            $price = $price - $coupon_list['coupon_price'];
        } else {
            $price = $price;
        }
        $this->success('', ['distance_price' => $price, 'distance' => $distance]);
    }

    /**
     * 订单详情
     *
     * @return void
     */
    public function info()
    {
        $order_id = $this->request->param('order_id');
        $info     = $this->model::with(['shaddress'=>function($query){
            $query->field('id, order_id, mobile,end,end_city,end_address,end_lat as end_latitude,end_lng as end_longitude,floor,house_number,createtime');
        }])
            ->where('id', $order_id)
            ->find();
        if (!$info) {
            $this->error('该订单不存在');
        }
        $score            = (new DdriveHyOrderComment())->where('order_id', $info['id'])->value('score');
        $info['score']    = $score ? $score : 0;
        $info['car_name'] = (new \addons\ddrive\model\Freight())->where('id', $info['car_id'])->value('car_name');
        if ($info['demand']) {
            $info['demand_text'] = Hy::getDemand($info['demand']);
        } else {
            $info['demand_text'] = '';
        }

        if ($info->driver) {
            $info['driver'] = $info->driver;
            $score_sum      = (new DdriveHyOrderComment())->where('driver_id', $info['driver']['id'])->sum('score');
            $score_count    = (new DdriveHyOrderComment())->where('driver_id', $info['driver']['id'])->count();
            if ($score_sum) {
                $info['driver']['score'] = round($score_sum / $score_count);
            } else {
                $info['driver']['score'] = 5;
            }
        }
        $info['start_latitude'] = $info['start_lat'];
        $info['start_longitude'] = $info['start_lng'];
        //$info['time'] = $info['createtime'];
        $info['createtime'] = $info['createtime'];
        $this->success('', $info);
    }

    /**
     * 待接单列表
     *
     * @return void
     */
    public function taskingList()
    {
        $pageSize = $this->request->param('pageSize', 10);
        $city     = $this->request->param('city', '');
        if ($city) {
            $list = (new \addons\ddrive\model\Hyorder())::with('shaddress')
                ->field('id,user_id,type,appointment_time,status,people_num,demand,order_price,
                discount_price,remark,start,start_city,start_address,start_lat,is_transport,start_lng,distance')
                ->where('status', '0')
                ->where('start_city', 'like', '%' . $city . '%')
                ->order('createtime desc')
                ->paginate($pageSize);
        } else {
            $list = (new \addons\ddrive\model\Hyorder())::with('shaddress')
                ->field('id,user_id,type,appointment_time,status,people_num,demand,order_price,
                discount_price,platform_service_fee,remark,start,start_city,start_address,is_transport,start_lat,start_lng')
                ->where('status', '0')
                ->order('createtime desc')
                ->paginate($pageSize);
        }
        foreach ($list as $k => $v) {
            if($v['demand']){
                $list[$k]['demand_text'] = Hy::getDemand($v['demand']);
            }else{
                $list[$k]['demand_text'] = '';
            }

        }
        $this->success("", $list);
    }

    /**
     * 接单
     *
     * @return void
     */
    public function tasking()
    {
        //欠缴平台服务费上限
        $restrict_service_fee = get_addon_config('ddrive')['restrict_service_fee'];
        $platform_service_fee = (new \addons\ddrive\model\User())->where('id', $this->auth->id)->value('platform_service_fee');
        $mobile               = (new \addons\ddrive\model\User())->where('id', $this->auth->id)->value('mobile');
        $user_verified        = (new UserVerified())->where('user_id', $this->auth->id)->find();
        if ($user_verified) {
            if ($user_verified['real_verified'] != 1) {
                $this->error('请完成实名认证');
            }
            if ($user_verified['driver_verified'] != 1) {
                $this->error('请完成驾照认证');
            }
            if ($user_verified['card_verified'] != 1) {
                $this->error('请完成车主认证');
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
            $driver_order = $this->model->where('cargo_driver_id', $this->auth->id)->whereIn('status', [1, 2, 4, 5])->whereNotIn('type', [2])->find();
            if ($driver_order) {
                $this->error('存在未完成订单,禁止重复接单');
            }
            $status = 1;
        } else {
            $status = 5;
        }
        // 接单
        $res = $this->model->where('id', $orderId)->update(['status' => $status, 'cargo_driver_id' => $this->auth->id]);
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
        // 判断订单是否被接单
        $order = $this->model->where('id', $orderId)->find();
        if (!$order) {
            $this->error('订单不存在');
        }
        if($order['status'] !=1){
            $this->error('无需重复操作');
        }
        $res = $this->model->where('id', $orderId)->update(['status' => 4]);
        if ($res) {
            $this->success('操作成功');
        } else {
            $this->error('操作失败');
        }
    }

    /**
     * 开始出发
     *
     * @return void
     */
    public function start()
    {
        $orderId = $this->request->param('order_id');
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
        if ($res) {
            $this->success('操作成功');
        } else {
            $this->success('操作失败');
        }
    }

    /**预约单开始出发
     * set_out
     * @des
     */
    public function set_out()
    {
        $orderId = $this->request->param('order_id');
        $order = $this->model->where('id', $orderId)->find();
        if (!$order) {
            $this->error('订单不存在');
        }
        if($order['status'] !=5){
            $this->error('无需重复操作');
        }
        $res = $this->model->where('id', $orderId)->update(['status' => 1]);
        if ($res) {
            $this->success('操作成功');
        } else {
            $this->error('操作失败');
        }
    }

    /**
     * 确认货物已到达
     *
     * @return void
     */
    public function done()
    {
        $orderId = $this->request->param('order_id');
        // 判断订单是否进行中
        $order = $this->model->where('id', $orderId)->find();
        if (!$order) {
            $this->error('订单不存在');
        }
        if($order['status'] !=2){
            $this->error('无需重复操作');
        }
        // 接单
        if ($order['pay_method'] == 2) {
            $res = $this->model->where('id', $orderId)->update(['status' => 3]);
        } else {
            //平台服务费
            $platform_service_fee             = get_addon_config('ddrive')['platform_service_fee'];
            $fee                              = number_format(($order['discount_price'] * ($platform_service_fee / 100)), 2);
            $money = round($order['order_price'] - $fee,2);
            // 增加司机余额
            Db::name('user')->where('id', $order['cargo_driver_id'])->setInc('money', $money);
            Db::name('details')->insert([
                'user_id'        => $order['cargo_driver_id'],
                'fluctuate_type' => 1,
                'msg'            => '货运收入',
                'amount'         => $money,
                'assets_type'    => 2,
                'source_type'    => 2,
                'createtime'     => time(),
                'form_id'        => $orderId,
            ]);
            $res = $this->model->where('id', $orderId)->update(['status' => 99,'complete_time'=>time()]);
        }

        if ($res) {
            $this->success('操作成功');
        } else {
            $this->success('操作失败');
        }
    }

    /**
     * 货到付款
     *
     * @return void
     */
    public function offline()
    {
        $orderId = $this->request->param('order_id');
        // 判断订单是否被接单
        $order = $this->model->where('id', $orderId)->find();
        if (!$order) {
            $this->error('订单不存在');
        }
        if($order['pay_method'] ==1){
            $this->error('该订单无需确认');
        }
        $platform_service_fee = get_addon_config('ddrive')['platform_service_fee'];
        $service_fee          = number_format(($order['discount_price'] * ($platform_service_fee / 100)), 2);
        $this->model->where('id', $orderId)->update(['platform_service_fee' => $service_fee]);
        $this->success('成功', ['order_price' => $order['discount_price'], 'service_fee' => $service_fee]);
    }

    /**
     * 确认结算
     *
     * @return void
     */
    public function confirm()
    {
        $orderId = $this->request->param('order_id');
        // 判断订单是否进行中
        $order = $this->model->where('id', $orderId)->find();
        if (!$order) {
            $this->error('订单不存在');
        }
        if ($order['status'] != 3) {
            $this->error('无需重复操作');
        }
        Db::startTrans();
        try {
            switch ($order['pay_method']){
                case '1':

                    break;
                case '2':
                    // 修改订单状态
                    $this->model->where('id', $orderId)->update(['status' => 99, 'complete_time' => time()]);
                    $platform_service_fee      = get_addon_config('ddrive')['platform_service_fee'];
                    $user_platform_service_fee = (new \addons\ddrive\model\User())->where('id', $this->auth->id)->value('platform_service_fee');
                    //累加服务费
                    (new \addons\ddrive\model\User())->where('id', $this->auth->id)->update(['platform_service_fee' => $user_platform_service_fee + round(($order['discount_price'] * ($platform_service_fee / 100)), 2)]);
                    break;
            }

            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        $this->success('操作成功');

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
        $comment = Db::name('ddrive_hy_order_comment')->where('order_id', $orderId)->find();
        if ($comment) {
            $this->error('已评价');
        }
        $score = $this->request->param('score', 5);
        $data  = [
            'user_id'    => $this->auth->id,
            'order_id'   => $orderId,
            'score'      => $score,
            'driver_id'  => $order['cargo_driver_id'],
            'createtime' => time(),
        ];
        $res   = Db::name('ddrive_hy_order_comment')->insert($data);

        if ($res) {
            $this->model->where('id', $orderId)->setField('comment_status', 1);
            $this->success('评价成功');
        } else {
            $this->error('评价失败');
        }
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
        $order       = $this->model->where('id', $orderId)->find();
        if (!$order) {
            $this->error('订单不存在');
        }
        if (!in_array($order['status'], [0, 1, 4, 5])) {
            $this->error('订单不在取消状态');
        }
        if ($order['user_id'] != $this->auth->id) {
            $this->error('网络异常,请稍后重试！');
        }
        $data = [
            'user_id'      => $this->auth->id,
            'order_id'     => $order['id'],
            'account'      => $order['pay_type_text'],
            'order_number' => $order['out_trade_no'],
            'number'       => $order['pay_number'],
            'pay_type'     => $order['pay_type'],
            'check_status' => '0',
            'apply_money'  => $order['discount_price'],
            'apply_time'   => time(),
        ];
        Db::startTrans();
        try {
            $id = 0;
            if($order['pay_method'] == 1){
                $id = Db::name('ddrive_refund')->insertGetId($data);
            }
            $this->model->where('id', $orderId)->update(['status' => -1, 'cancel_type' => $cancel_type]);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $this->error('订单取消失败');
        }
        $this->success('订单取消成功', ['refund_id' => $id]);
    }

    /**司机端-订单列表
     * order_info
     * @des
     */
    public function order_list()
    {
        $model    = $this->model;
        $pageSize = $this->request->param('pageSize', 10);
        $map      = [];
        if (!$this->auth->id) {
            $this->success("", ['date' => []]);
        }
        // 订单类型
        if ($this->request->has('type')) {
            $map['cargo_driver_id'] = $this->auth->id;
            if ($this->request->request('type') == 1) {
                $map['status'] = ['in', [1, 2, 4,5]];//已接单
            } else {
                $map['status'] = ['in', [3,99]];
            }
            $list = $model::with('shaddress')
                ->field('id,user_id,type,appointment_time,status,people_num,demand,order_price,
                discount_price,remark,start,start_city,is_transport,start_address,start_lat,start_lng,distance,platform_service_fee')
                ->where($map)
                ->order('createtime desc')
                ->paginate($pageSize);
            foreach ($list as $k => $v) {
                if($v['demand']){
                    $list[$k]['demand_text'] = Hy::getDemand($v['demand']);
                }else{
                    $list[$k]['demand_text'] = '';
                }

            }
            $this->success("查询成功", $list);
        }
    }

    /**
     * 用户端-用户订单
     *
     * @return void
     */
    public function index()
    {
        $model    = $this->model;
        $pageSize = $this->request->param('pageSize', 10);
        $map      = [];
        // 用户身份
        $map['user_id'] = $this->auth->id;
        $map['status']  = ['<>', '7'];
        // 订单状态
        if ($this->request->has('status') && $this->request->request('status') != 'all') {
            $map['comment_status'] = 0;
            $map['status']         = ['in', $this->request->param('status')];
        }
        $list = $model::with('shaddress')
            ->field('id,user_id,type,appointment_time,status,people_num,demand,order_price,
                discount_price,remark,start,start_city,is_transport,start_address,start_lat,start_lng,distance,comment_status')
            ->where($map)
            ->order('createtime desc')
            ->paginate($pageSize);
        foreach ($list as $k => $v) {
            if($v['demand']){
                $list[$k]['demand_text'] = Hy::getDemand($v['demand']);
            }else{
                $list[$k]['demand_text'] = '';
            }

        }
        $this->success("", $list);
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
        $list_start     = $model::with('shaddress')->where($map)->order('createtime desc')->select();
        $this->success("查询成功", ['data' => $list_start]);
    }

    /**
     * 订单支付
     *
     * @return void
     */
    public function driver_pay()
    {
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

        $order_money = $order['order_price'];

        $method = $this->request->param('method', 'app');

        // 用户信息
        $user = $this->auth->getUser();
        //订单标题
        $title = '订单费用';
        // 订单编号
        $out_trade_no = $order['out_trade_no'];
        // openid
        $openid = '';
        if ($type == 'mini_wechat') {
            $openid = Db::name('ddrive_user_token')->where('user_id', $user['id'])->value('mini_openid');
        }
        if (!$openid && $type == 'mini_wechat') {
            $info = (new Common())->getOpenid($code);
            if (isset($info['openid'])) {
                $openid = $info['openid'];
            } else {
                return json_encode(['code' => 0, 'msg' => '失败']);
            }
        }

        //回调链接
        if ($type == 'alipay') {
            $notifyurl = $this->request->root(true) . '/addons/ddrive/hyorder/notifyx/paytype/alipay';
        } else {
            $notifyurl = $this->request->root(true) . '/addons/ddrive/hyorder/notifyx/paytype/' . $type;
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
            return json_encode(['code' => 0, 'msg' => '失败']);
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
            $number       = $paytype == 'alipay' ? $data['trade_no'] : $data['transaction_id'];
            $out_trade_no = $data['out_trade_no'];
            Log::record('订单编号：' . $out_trade_no);
            $order    = $this->model->where('out_trade_no', $out_trade_no)->find();
            if ($order['status'] == 7) {
                //平台服务费
                $platform_service_fee             = get_addon_config('ddrive')['platform_service_fee'];
                $fee                              = number_format(($payamount * ($platform_service_fee / 100)), 2);
                $up_order                         = [];
                $up_order['platform_service_fee'] = $fee;
                $up_order['pay_time'] = time();
                $up_order['status'] = 0;
                $up_order['pay_number'] = $number;
                $up_order['pay_type']             = $paytype == 'alipay' ? 1 : 2;
                $this->model->where('out_trade_no', $out_trade_no)->update($up_order);
            }
        } catch (\Exception $e) {
            Log::record($e->getMessage());
        }
        echo $pay->success();
    }
    /**退款详情
     * refund_info
     * @des
     */
    public function refund_info()
    {
        $id = request()->param('id');
        if (!$id) {
            $this->error('网络异常,请稍后重试！');
        }
        $where['id'] = $id;
        $info              = (new DriverRefund())->getInfo($where, 'id,user_id,apply_money,money,check_status,account,FROM_UNIXTIME(apply_time) as apply_time,FROM_UNIXTIME(confirm_time) as confirm_time,FROM_UNIXTIME(success_time) as success_time', 'find');
        if (!$info) {
            $this->error('网络异常,请稍后重试！');
        }
        if ($info['user_id'] != $this->auth->id) {
            $this->error('网络异常,请稍后重试！');
        }
        $info['predict_time'] = date("Y-m-d H:i:s", strtotime("+1 day", strtotime($info['success_time'])));
        $this->success('查询成功', $info);
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
            $info = $this->model::with('shaddress')->where('start_address', 'like', '%' . $city . '%')->order('createtime desc')->where('status', '0')->select();
            $this->success('', $info);
        }
        // 判断订单是否存在
        $createtime = $this->model->where('id', $orderId)->value('createtime');
        if (!$createtime) {
            $this->success('', []);
        }
        $info = $this->model::with('shaddress')->where('createtime', '>', $createtime)->where('start_address', 'like', '%' . $city . '%')->where('status', '0')->select();
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

        $info = $this->model::with('shaddress')->where('start_address', 'like', '%' . $city . '%')->where('status', '<>', '0')->column('id');
        if ($info) {
            $this->success('', $info);
        }
        $this->success('', []);

    }
}