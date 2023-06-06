<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/12/16
 * Time: 13:54
 */

namespace addons\ddrive\controller;

use addons\ddrive\extend\Common;
use addons\ddrive\library\Check;
use addons\ddrive\model\UserVerified;
use addons\epay\library\Service;
use app\admin\model\Coupon;
use app\admin\model\UserCoupon;
use app\common\controller\Api;
use think\Db;
use addons\ddrive\library\Sforder as Lib;
use think\Exception;
use think\Log;
use app\common\model\Config;


class Sforder extends Api
{
    protected $noNeedLogin = ['order_index', 'takingList', 'order_info', 'time_out', 'order_refresh', 'order_eliminate','recommend_route','index_order'];
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \addons\ddrive\model\Sforder();
    }

    /**
     * 订单列表
     *
     * @return void
     */
    public function order_list()
    {
//        $where    = "(user_id = '" . $this->auth->id . "' OR other_user_id = '" . $this->auth->id . ",') AND pid = 0";
        $field            = 'id,order_type,start_time,order_money,start_city,start_address,end_city,end_address,status,tel,start_name,end_name,people_num,more_seats';
        $sf_user_order    = $this->model->where('user_id', $this->auth->id)->where('order_type', 1)->whereIn('status', [2, 3, 4])->field($field)->order('createtime desc')->select();
        $sf_driver_order  = $this->model->where('user_id', $this->auth->id)->where('order_type', 2)->whereIn('status', [1, 3, 4])->field($field)->order('createtime desc')->select();
        $new_driver_order = [];
        foreach ($sf_driver_order as $k => $v) {
            $child_order = $this->model->where('pid', $v['id'])->find();
            if ($child_order) {
                $new_driver_order[] = $v;
            }
        }
        $sf_order = array_merge($sf_user_order, $new_driver_order);
        //查询子单
        foreach (collection($sf_order)->toArray() as $k => $v) {
            $sf_order[$k]['new_tel']    = substr($v['tel'], 7, 4);
            $sf_order[$k]['tel']        = $v['tel'];
            $sf_order[$k]['more_seats'] = $v['more_seats'] ? $v['more_seats'] : 0;
            $sf_order[$k]['week']       = Lib::getWeek(date("w", $v['start_time']));
            $sf_order[$k]['start_time'] = date('m-d H:i', $v['start_time']);
            $sf_order[$k]['statusText'] = Lib::getStatus($v['status']);
        }
        $this->success('成功', $sf_order ? $sf_order : []);
    }

    /**
     * 预约订单(司机发布预约单)
     *
     * @return void
     */
    public function reserve_order()
    {
        $order_id   = $this->request->param('order_id');
        $people_num = $this->request->param('people_num', 1);
        $tel        = $this->request->param('tel', 1);
        $remark     = $this->request->param('remark');
        $sf_order   = $this->model->where('id', $order_id)->find();
        if (!$sf_order) {
            $this->error('该订单不存在');
        }

        if (floor($people_num) != $people_num) {
            $this->error('请填写正确人数');
        }
        if ($sf_order['more_seats'] < $people_num) {
            $this->error('当前余座已不足', '');
        }
        if ($sf_order['status'] != 1) {
            $this->error('该订单当前无法预约');
        }
        $user = $this->auth->getUser();
        // 联系电话
        if ($tel) {
            $mobile = $tel;
        } else {
            $mobile = $user['mobile'];
        }
        $data = [
            'user_id'         => $sf_order['user_id'],
            'order_type'      => $sf_order['order_type'],
            'start_address'   => $sf_order['start_address'],
            'end_address'     => $sf_order['end_address'],
            'route'           => $sf_order['route'],
            'start_time'      => $sf_order['start_time'],
            'car_type'        => $sf_order['car_type'],
            'more_seats'      => $sf_order['more_seats'] - $people_num,
            'car_price'       => $sf_order['car_price'],
            'remark'          => $remark,
            'start_city'      => $sf_order['start_city'],
            'end_city'        => $sf_order['end_city'],
            'start_name'      => $sf_order['start_name'],
            'end_name'        => $sf_order['end_name'],
            'pid'             => $sf_order['id'],
            'status'          => 3,
            'start_latitude'  => $sf_order['start_latitude'],
            'start_longitude' => $sf_order['start_longitude'],
            'end_latitude'    => $sf_order['end_latitude'],
            'end_longitude'   => $sf_order['end_longitude'],
            'tel'             => $mobile,
            'people_num'      => $people_num,
            'other_user_id'   => $this->auth->id,
        ];
        try {
            $this->model->data($data)->save();
            $updata                = [];
            $updata['more_seats']  = $sf_order['more_seats'] - $people_num;
            $updata['updatetime']  = time();
            $updata['order_money'] = $sf_order['order_money'] + ($people_num * $sf_order['car_price']);
            if ($sf_order['more_seats'] - $people_num == 0) {
                $updata['status'] = 3;
            }
            $this->model->where('id', $order_id)->update($updata);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $this->error('预约失败');
        }
        
        $sms_config = get_addon_config('alisms');
        $ddr_config = get_addon_config('ddrive');
        $mobiles = explode("\r\n", $ddr_config['noticeMobile']);
        \app\common\library\Sms::notice($ddr_config['noticeMobile'], ['type'=>'顺风车'], $sms_config['template']['notice']);
        
        $this->success('预约成功', ['order_id' => $this->model->id]);


    }

    /**
     * 发布预约订单
     *
     * @return void
     */
    public function ddriver_create()
    {
        $user = $this->auth->getUser();
        // 联系电话
        $mobile = $user['mobile'];
        if (!$mobile) {
            $this->error('请先去绑定手机号');
        }
        $start_address   = $this->request->param('start_address');
        $end_address     = $this->request->param('end_address');
        $start_name      = $this->request->param('start_name');
        $end_name        = $this->request->param('end_name');
        $start_latitude  = $this->request->param('start_latitude', '');
        $start_longitude = $this->request->param('start_longitude', '');
        $end_latitude    = $this->request->param('end_latitude', '');
        $end_longitude   = $this->request->param('end_longitude', '');
        $order_type      = $this->request->param('order_type', 2);
        $route           = $this->request->param('route');
        $start_time      = $this->request->param('start_time');
        $car_type        = $this->request->param('car_type');
        $more_seats      = $this->request->param('more_seats', 0);
        $car_price       = $this->request->param('car_price');
        $remark          = $this->request->param('remark');
        $start_city      = $this->request->param('start_city');
        $end_city        = $this->request->param('end_city');
        $people_num      = $this->request->param('people_num', 0);
        $tel             = $this->request->param('tel');
        if ($tel) {
            $mobile = $tel;
        } else {
            $mobile = $mobile;
        }
        if (!in_array($order_type, [1, 2])) {
            $this->error('公共参数错误');
        }
        $rule = [
            ['start_address', 'require', '请填写详细地址'],
            ['end_address', 'require', '请填写目的地详细地址'],
            ['start_name', 'require', '请填写出发地'],
            ['end_name', 'require', '请填写目的地'],
            ['start_time', 'require', '请选择出发时间'],
            ['start_city', 'require', '请选择出发城市'],
            ['end_city', 'require', '请选择目的地城市'],
        ];
        if ($order_type == 1) {
            $new_rule = [
                ['people_num', 'require', '请填写预约人数'],

            ];
        } else {
            $new_rule = [
                ['car_type', 'require', '请填写车型'],
                ['more_seats', 'require', '请填写余座'],
                ['car_price', 'require', '请填写座位价格'],
                ['route', 'require', '请输入路线'],
            ];

        }
        $rule = array_merge($rule, $new_rule);
        (new Check())->checkParam($rule);
        $user_verified = (new UserVerified())->where('user_id', $this->auth->id)->find();
        if ($user_verified) {
            if ($user_verified['real_verified'] != 1) {
                $this->error('请完成实名认证');
            }
        }
        if ($order_type == 2) {
            if (!$route || !$car_type || !$more_seats) {
                $this->error('司机端参数错误');
            }
            if ($user_verified['driver_verified'] != 1) {
                $this->error('请完成驾照认证');
            }
            if ($user_verified['card_verified'] != 1) {
                $this->error('请完成车辆认证');
            }
        }

        $data  = [
            'user_id'         => $user['id'],
            'order_type'      => $order_type,
            'start_address'   => $start_address,
            'start_name'      => $start_name,
            'end_address'     => $end_address,
            'end_name'        => $end_name,
            'start_latitude'  => $start_latitude,
            'start_longitude' => $start_longitude,
            'end_latitude'    => $end_latitude,
            'end_longitude'   => $end_longitude,
            'route'           => $route,
            'start_time'      => $start_time,
            'car_type'        => $car_type,
            'more_seats'      => $more_seats,
            'car_price'       => $car_price,
            'remark'          => $remark,
            'start_city'      => $start_city,
            'end_city'        => $end_city,
            'pid'             => 0,
            'status'          => 1,
            'tel'             => $mobile,
            'people_num'      => $people_num,
        ];
        $model = $this->model;
        $res   = $model->data($data)->save();
        if ($res) {
            $this->success('订单创建成功', ['order_id' => $model->id]);
        } else {
            $this->error('订单创建失败');
        }

    }

    /**
     * 首页(司机端)
     *
     * @return void
     */
    public function order_index()
    {
        $order_type = $this->request->param('order_type', 2);
        $start_city = $this->request->param('start_city', '');
        $end_city   = $this->request->param('end_city', '');
        $page       = $this->request->param('page');
        if (!in_array($order_type, [1, 2])) {
            $this->error('公共参数错误');
        }
//        if (!$start_city) {
//            $this->error('请选择乘坐城市');
//        }
        $where              = [];
        $where['SF.status'] = '1';
        //查询预约订单
        $sf_order = Db::name('ddrive_sf_order')->alias('SF')
            ->join('real_verified RV', 'RV.user_id = SF.user_id', 'LEFT')
            ->where('SF.start_address', 'like', '%' . $start_city . '%')
            ->where('SF.end_address', 'like', '%' . $end_city . '%')
            ->where('SF.order_type', $order_type)
            ->where($where)
            ->field('SF.*,RV.truename')
            ->order('SF.createtime desc')
            ->limit('10')
            ->page($page)
            ->select();
        if (!$sf_order) {
            $this->success('暂无预约单', []);
        }
        if ($order_type == 1) {
            foreach ($sf_order as $k => $v) {
                $order[] = [
                    'id'            => $v['id'],
                    'start_city'    => $v['start_city'],
                    'end_city'      => $v['end_city'],
                    'start_address' => $v['start_address'],
                    'end_address'   => $v['end_address'],
                    'start_name'    => $v['start_name'],
                    'end_name'      => $v['end_name'],
                    'people_num'    => $v['people_num'],
                    'start_time'    => date('m-d H:i', $v['start_time']),
                    'statusText'    => Lib::getStatus($v['status']),

                ];
            }
        } else {
            foreach ($sf_order as $k => $v) {
                //司机评分计算
                $score_sum   = Db::name('ddrive_sf_order_comment')->where('driver_id', $v['user_id'])->sum('score');
                $score_count = Db::name('ddrive_sf_order_comment')->where('driver_id', $v['user_id'])->count();
                if ($score_sum) {
                    $score = round($score_sum / $score_count);
                } else {
                    $score = 5;
                }
                $order[] = [
                    'id'            => $v['id'],
                    'driver_name'   => mb_substr($v['truename'], 0, 1) . '师傅',
                    'car_type'      => $v['car_type'],
                    'route'         => $v['route'],
                    'remark'        => $v['remark'] ? $v['remark'] : '顺路上下,预定后电话确认一下。',
                    'start_city'    => $v['start_city'],
                    'end_city'      => $v['end_city'],
                    'start_address' => $v['start_address'],
                    'start_name'    => $v['start_name'],
                    'end_name'      => $v['end_name'],
                    'end_address'   => $v['end_address'],
                    'more_seats'    => $v['more_seats'],
                    'car_price'     => $v['car_price'],
                    'score'         => $score,
                    'start_time'    => date('m-d H:i', $v['start_time']),
                    'statusText'    => Lib::getStatus($v['status']),
                ];
            }

        }
        $this->success('成功', $order);
    }

    /**
     * 司机接单(用户预约单)
     *
     * @return void
     */

    public function ddrive_taking()
    {
        $order_id = $this->request->param('order_id');
        $sf_order = $this->model->where('id', $order_id)->find();
        if (!$sf_order) {
            $this->error('该订单不存在');
        }
        $user_verified = (new UserVerified())->where('user_id', $this->auth->id)->find();
        if ($user_verified) {
            if ($user_verified['real_verified'] != 1) {
                $this->error('请完成实名认证');
            }
            if ($user_verified['driver_verified'] != 1) {
                $this->error('请完成驾照认证');
            }
            if ($user_verified['card_verified'] != 1) {
                $this->error('请完成车辆认证');
            }
        }
        if ($sf_order['status'] != 1) {
            $this->error('该订单在接单状态');
        }
        $ret = $this->model->where('id', $order_id)->update(['status' => 2, 'other_user_id' => $this->auth->id]);
        if ($ret) {
            $this->success('接单成功', '');
        } else {
            $this->error('接单失败');
        }
    }

    /**
     * 订单详情
     *
     * @return void
     */
    public function info()
    {
        $order_id = $this->request->param('order_id');
        $sf_order = Db::name('ddrive_sf_order')->alias('SF')
            ->join('real_verified RV', 'RV.user_id = SF.user_id', 'LEFT')
            ->join('ddrive_sf_order_comment OM', 'OM.order_id = SF.id', 'LEFT')
            ->where('SF.id', $order_id)
            ->field('SF.*,RV.truename,OM.score')
            ->find();
        if (!$sf_order) {
            $this->error('该订单不存在');
        }
        if ($sf_order['order_type'] == 2) {
            $chil_order = $this->model
                ->where('order_type', $sf_order['order_type'])
                ->where('pid', $order_id)
                ->field('id,tel,people_num,car_price,start_address,pay_status,start_name,status,order_money')
                ->select();
            foreach ($chil_order as $k => $v) {
                $chil_order[$k]['statusText'] = Lib::getStatus($v['status']);
            }
        } else {
            $chil_order = [];
        }
        $info = [
            'id'                   => $sf_order['id'],
            'truename'             => mb_substr($sf_order['truename'], 0, 1) . '师傅',
            'order_type'           => $sf_order['order_type'],
            'sf_type'              => 1,
            'start_city'           => $sf_order['start_city'],
            'end_city'             => $sf_order['end_city'],
            'start_address'        => $sf_order['start_address'],
            'end_address'          => $sf_order['end_address'],
            'start_name'           => $sf_order['start_name'],
            'end_name'             => $sf_order['end_name'],
            'route'                => $sf_order['route'] ? $sf_order['route'] : '',
            'car_type'             => $sf_order['car_type'] ? $sf_order['car_type'] : '',
            'car_price'            => $sf_order['car_price'] ? $sf_order['car_price'] : 0,
            'people_num'           => $sf_order['people_num'],
            'newtel'               => substr($sf_order['tel'], 7, 4),
            'tel'                  => $sf_order['tel'],
            'score'                => $sf_order['score'] ? $sf_order['score'] : 0,
            'start_time'           => date('m-d H:i', $sf_order['start_time']),
            'order_money'          => $sf_order['order_money'] ? $sf_order['order_money'] : '',
            'status'               => $sf_order['status'],
            'pay_status'           => $sf_order['pay_status'],
            'more_seats'           => $sf_order['more_seats'] ? $sf_order['more_seats'] : 0,
            'platform_service_fee' => $sf_order['platform_service_fee'],
            'pay_type'             => $sf_order['pay_type'],
            'remark'               => $sf_order['remark'],
            'statusText'           => Lib::getStatus($sf_order['status']),
            'chil_order'           => $chil_order,

        ];
        $this->success('成功', $info);
    }

    /**
     * 确认订单(用户预约单)
     *
     * @return void
     */
    public function confirm()
    {
        $order_id    = $this->request->param('order_id');
        $order_money = $this->request->param('order_money');
        if (!$order_id || !$order_money) {
            $this->error('参数错误');
        }
        $sf_order = $this->model->where('id', $order_id)->find();
        if (!$sf_order) {
            $this->error('该订单不存在');
        }
        if ($sf_order['status'] != 2) {
            $this->error('该订单不在确认状态');
        }
        $car_price = bcdiv($order_money, $sf_order['people_num'], 2);
        $ret       = $this->model->where('id', $order_id)->update(['status' => 3, 'order_money' => $order_money, 'car_price' => $car_price]);
        if ($ret) {
            $this->success('确认接单');
        } else {
            $this->error('失败');
        }
    }

    /**
     * 取消订单
     *
     * @return void
     */
    public function cancel()
    {
        $order_id    = $this->request->param('order_id');
        $cancel_type = $this->request->param('cancel_type');
        $sf_order    = $this->model->where('id', $order_id)->find();
        if (!$sf_order) {
            $this->error('该订单不存在');
        }
        if (!in_array($sf_order['status'], [1, 2, 3])) {
            $this->error('该订单无法取消');
        }
        if ($sf_order['status'] == -1) {
            $this->error('该订单已取消');
        }
        try {
            if ($sf_order['order_type'] == 1) {
                if ($sf_order['other_user_id'] == $this->auth->id && !$cancel_type) {//司机取消
                    $data = [
                        'status'        => 1,
                        'updatetime'    => time(),
                        'other_user_id' => 0,
                    ];
                } else {//用户取消
                    $data = [
                        'status'      => -1,
                        'cancel_time' => time(),
                        'updatetime'  => time(),
                        'cancel_type' => $cancel_type,
                    ];
                }

            } else {
                if ($sf_order['other_user_id'] == $this->auth->id && $cancel_type) {//用户取消
                    $data = [
                        'status'      => -1,
                        'cancel_time' => time(),
                        'updatetime'  => time(),
                        'cancel_type' => $cancel_type,
                    ];
                    Db::name('ddrive_sf_order')->where('id', $sf_order['pid'])->setInc('more_seats', $sf_order['people_num']);
                } else {//司机取消
                    //检测是否有客户预约
                    $chile_order = Db::name('ddrive_sf_order')->where('pid', $order_id)->where('status', '<>', '-1')->column('id');
                    if ($chile_order) {
                        $data = [
                            'updatetime' => time(),
                        ];
                        $this->model->where('id', $order_id)->update(['more_seats' => 0]);
                    } else {
                        $data = [
                            'status'      => -1,
                            'cancel_time' => time(),
                            'updatetime'  => time(),
                        ];
                    }
                }
            }
            $this->model->where('id', $order_id)->update($data);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $this->error('取消失败');
        }
        $this->success('取消成功');
    }

    /**
     * 司机开始出发
     *
     * @return void
     */
    public function set_out()
    {
        $order_id = $this->request->param('order_id');

        $sf_order = $this->model->where('id', $order_id)->find();
        if (!$sf_order) {
            $this->error('该订单不存在');
        }
        if ($sf_order['status'] == -1) {
            $this->error('该订单已取消');
        }
        if ($sf_order['status'] == -2) {
            $this->error('该订单已超时');
        }
        if ($sf_order['status'] == 4) {
            $this->error('该订单已出发');
        }
        if ($sf_order['status'] == 5) {
            $this->error('该订单已完成');
        }
        if ($sf_order['order_type'] == 2) {
            $chil_order_id = Db::name('ddrive_sf_order')
                ->where('order_type', $sf_order['order_type'])
                ->where('pid', $order_id)
                ->column('id');
            array_push($chil_order_id, $order_id);
            $ret = $this->model->whereIn('id', $chil_order_id)->update(['status' => 4, 'updatetime' => time()]);
        } else {
            $ret = $this->model->where('id', $order_id)->update(['status' => 4, 'updatetime' => time()]);
        }
        if ($ret) {
            $this->success('出发成功');
        } else {
            $this->error('失败');
        }
    }

    /**
     * 线下结算
     *
     * @return void
     */
    public function offline_settlement()
    {
        $order_id = $this->request->param('order_id'); //司机端发布 传子单id
        $sf_order = $this->model->where('id', $order_id)->find();
        if (!$sf_order) {
            $this->error('该订单不存在');
        }
        if ($sf_order['pay_status'] == 1) {
            $this->error('该订单已结算');
        }
        if ($sf_order['status'] != 4) {
            $this->error('该订单不在结算状态');
        }
        //服务费率
        $platform_service_fee = get_addon_config('ddrive')['platform_service_fee'];
        if ($sf_order['order_type'] == 1) {
            $platform_service_fee = number_format(($sf_order['order_money'] * ($platform_service_fee / 100)), 2);
        } else {
            $platform_service_fee = number_format((($sf_order['car_price'] * $sf_order['people_num']) * ($platform_service_fee / 100)), 2);
        }
        $data = [
            'pay_type'             => 3,
            'pay_time'             => time(),
            'pay_status'           => 1,
            'platform_service_fee' => $platform_service_fee,
        ];
        $this->model->where('id', $order_id)->update($data);
        try {
            if ($sf_order['order_type'] == 2) {//司机发布预约单，存在子单
                $chil_order_id = Db::name('ddrive_sf_order')->where('pid', $sf_order['pid'])->where('pay_status', 0)->column('id');
                if (!$chil_order_id) { // 无子单未支付
                    $updata = [
                        'pay_type'   => 3,
                        'pay_time'   => time(),
                        'pay_status' => 1,
                    ];
                    $this->model->where('id', $sf_order['pid'])->update($updata);
                }
                $user_order_platform_service_fee = Db::name('ddrive_sf_order')->where('id', $sf_order['pid'])->value('platform_service_fee');
                //累加服务费
                Db::name('ddrive_sf_order')->where('id', $sf_order['pid'])->update(['platform_service_fee' => $user_order_platform_service_fee + $platform_service_fee]);
            }
            //累加服务费
            Db::name('user')->where('id', $sf_order['user_id'])->setInc('platform_service_fee', $platform_service_fee);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $this->error('线下结算失败');
        }
        $this->success('线下结算成功');
    }

    /**
     * 行程结束
     *
     * @return void
     */
    public function order_complete()
    {
        $order_id = $this->request->param('order_id');
        $sf_order = $this->model->where('id', $order_id)->find();
        if (!$sf_order) {
            $this->error('该订单不存在');
        }
        if ($sf_order['pay_status'] != 1 && $sf_order['order_type'] == 1) {
            $this->error('乘客还未支付');
        }
        if ($sf_order['status'] == 5) {
            $this->error('该行程已完成');
        }
        $data = [
            'status'        => 5,
            'updatetime'    => time(),
            'complete_time' => time(),
        ];
        try {
            if ($sf_order['order_type'] == 2) {//司机发布预约单，存在子单
                $chil_order_id = Db::name('ddrive_sf_order')->where('pid', $sf_order['id'])->where('pay_status', 0)->column('id');
                if ($chil_order_id) { // 有未支付订单
                    exception('乘客还未支付');
                }
                $this->model->where('pid', $sf_order['id'])->update($data);
            }
            $this->model->where('id', $order_id)->update($data);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        $this->success('结束成功');
    }

    /**
     * 顺风车发布
     *
     * @return void
     */
    public function release_order()
    {
        $page     = $this->request->param('page', 1);
        $where    = "(user_id = '" . $this->auth->id . "' OR other_user_id = '" . $this->auth->id . ",') AND pid = 0 ";
        $field    = 'id,start_time,start_city,start_address,end_city,end_address,more_seats,start_name,end_name,status';
        $sf_order = $this->model->where($where)->where('order_type', 2)->field($field)->limit(10)->page($page)->order('createtime desc')->select();
        foreach ($sf_order as $k => $v) {
            $sf_order[$k]['week']       = Lib::getWeek(date("w", $v['start_time']));
            $sf_order[$k]['start_time'] = date('m-d H:i', $v['start_time']);
            $sf_order[$k]['statusText'] = Lib::getStatus($v['status']);
        }
        $this->success('成功', $sf_order ? $sf_order : []);
    }

    /**
     * 预约人次（弃用）
     *
     * @return void
     */
    public function release_people()
    {
        $order_id       = $this->request->param('order_id');
        $release_people = $this->model->where('pid', $order_id)->field('id,tel,people_num,car_price,start_address')->select();
        if (!$release_people) {
            $this->error('该订单不存在');
        }
        $this->success('成功', $release_people);
    }

    /**
     * 我的订单
     *
     * @return void
     */
    public function user_order()
    {
        $type = $this->request->param('type');
        $page = $this->request->param('page', 1);
        if ($type == 1) {
            $where  = "(user_id = '" . $this->auth->id . "' AND order_type = 1) OR (other_user_id = '" . $this->auth->id . "' AND order_type = 2 AND pid != 0)";
            $status = ['-2', '-1', '1', '2', '3', '4', '5'];
        } else {
            $where  = "(other_user_id = '" . $this->auth->id . "' AND order_type = 1) OR (user_id = '" . $this->auth->id . "' AND order_type = 2 AND pid = 0)";
            $status = ['-2', '-1', '5'];
        }
        $field      = 'id,order_type,start_time,order_money,start_city,start_address,end_city,end_address,status,platform_service_fee,pay_type,start_name,end_name,more_seats,people_num';
        $user_order = $this->model->where($where)->whereIn('status', $status)->field($field)->limit(10)->page($page)->order('createtime desc')->select();
        foreach ($user_order as $k => $v) {
            $user_order[$k]['week']                 = Lib::getWeek(date("w", $v['start_time']));
            $user_order[$k]['start_time']           = date('m-d H:i', $v['start_time']);
            $user_order[$k]['platform_service_fee'] = $v['platform_service_fee'];
            $user_order[$k]['statusText']           = Lib::getStatus($v['status']);
        }
        $this->success('成功', $user_order);
    }

    /**
     * 推荐路线
     *
     * @return void
     */
    public function recommend_route()
    {
        $route = [
            '杭州市' . '->' . '郑州市',
            '杭州市' . '->' . '绍兴市',
            '杭州市' . '->' . '温州市',
            '杭州市' . '->' . '丽水市',
            '杭州市' . '->' . '北京市',
            '杭州市' . '->' . '上海市',
            '杭州市' . '->' . '武汉市',
            '杭州市' . '->' . '苏州市',
        ];
        $this->success('成功', $route);
    }

    /**
     * 首页状态(用户端订单)
     *
     * @return void
     */
    public function index_order()
    {
        if($this->auth->id){
            $where       = "(user_id = '" . $this->auth->id . "' AND order_type = 1) OR (other_user_id = '" . $this->auth->id . "' AND order_type = 2 AND pid != 0)";
            $index_order = $this->model->where($where)->whereIn('status', [1, 2, 3, 4])->field('id,start_city,end_city,status,start_time,order_type')->select();
            foreach ($index_order as $k => $v) {
                $index_order[$k]['start_time'] = date('m-d H:i');
                $index_order[$k]['statusText'] = Lib::getStatus($v['status']);
            }
        }else{
            $index_order = [];
        }

        $this->success('成功', $index_order ? $index_order : []);
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
        if ($order['order_type'] == 1) {
            $driver_id = $order['other_user_id'];
        } else {
            $driver_id = $order['user_id'];
        }
        $comment = Db::name('ddrive_sf_order_comment')->where('order_id', $orderId)->find();
        if ($comment) {
            $this->error('已评价');
        }
        $score = $this->request->param('score', 5);
        $data  = [
            'user_id'    => $this->auth->id,
            'order_id'   => $orderId,
            'score'      => $score,
            'driver_id'  => $driver_id,
            'createtime' => time(),
        ];
        $res   = Db::name('ddrive_sf_order_comment')->insert($data);

        if ($res) {
            $this->model->where('id', $orderId)->setField('assess', 1);
            $this->success('评价成功');
        } else {
            $this->error('评价失败');
        }
    }

    /**
     * 司机端订单支付
     *
     * @return void
     */
    public function driver_pay()
    {
        $site     = Config::get('site');
        $orderId  = $this->request->param('order_id');
        $id = $this->request->param('coupon_id');
        $code    = $this->request->param('code');
        $type     = $this->request->param('type', 'user_wechat'); // driver_wechat 司机端 user_wechat 用户端 mini_wechat 小程序
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
            if ($order['order_money'] < $coupon_list['limit_price']) {
                $this->error('该订单没达到最低抵扣额');
            }
            $order_money = $order['order_money'] - $coupon_list['coupon_price'];
        } else {
            $order_money = $order['order_money'];
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
        $notifyurl = $this->request->root(true) . '/addons/ddrive/order/notifyx/paytype/wechat';
        $params    = [
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
            $pay_type     = $paytype == 'alipay' ? 2 : 1;
            $out_trade_no = $data['out_trade_no'];
            Log::record('订单编号：' . $out_trade_no);
            $order_id = explode('-', $out_trade_no)[0];
            $order    = $this->model->where('id', $order_id)->find();
            if ($order['pay_status'] != 1) {

                //平台服务费
                $platform_service_fee = get_addon_config('ddrive')['platform_service_fee'] / 100;
                $fee                  = round($platform_service_fee * $order['price'], 2);
                $update = [];
                $update['pay_status'] = 1;
                $update['pay_time'] = time();
                $update['pay_type'] = $pay_type;
                $update['status'] = 5;
                $update['platform_service_fee'] = $fee;
                $this->model->where('id', $order_id)->update($update);
                // 增加司机余额
                Db::name('user')->where('id', $order['driver_id'])->setInc('money', $order['price']);
                Db::name('details')->insert([
                    'user_id'        => $order['driver_id'],
                    'fluctuate_type' => 1,
                    'msg'            => '顺风车收入',
                    'amount'         => $order['price'],
                    'assets_type'    => 2,
                    'source_type'    => 2,
                    'createtime'     => time(),
                    'form_id'        => $order_id,
                ]);
            }
        } catch (Exception $e) {
            Log::record($e->getMessage());
        }
        echo $pay->success();
    }

    /**更新司机端首页订单信息
     * order_refresh
     * @des
     */
    public function order_refresh()
    {
        $orderId    = $this->request->param('order_id');
        $city       = $this->request->param('city');
        $order_type = $this->request->param('order_type', 1);
        if (!$orderId) {
            $info = $this->model->where('start_city', 'like', '%' . $city . '%')->where('order_type', $order_type)->order('createtime desc')->where('status', '1')->select();
            $this->success('', $info);
        }
        // 判断订单是否存在
        $createtime = $this->model->where('id', $orderId)->value('createtime');
        if (!$createtime) {
            $this->success('', []);
        }
        $info = $this->model->where('createtime', '>', $createtime)->where('start_city', 'like', '%' . $city . '%')->where('order_type', $order_type)->where('status', '1')->select();
        foreach ($info as $k => $v) {
            $info[$k]['start_time'] = date('m-d H:i', $v['start_time']);
            $info[$k]['statusText'] = Lib::getStatus($v['status']);
        }
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
        $city       = $this->request->param('city');
        $order_type = $this->request->param('order_type', 1);
        $info       = $this->model->where('start_city', 'like', '%' . $city . '%')->where('status', '<>', '1')->where('order_type', $order_type)->column('id');
        if ($info) {
            $this->success('', $info);
        }
        $this->success('', []);
    }
}