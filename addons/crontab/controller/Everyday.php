<?php

namespace addons\crontab\controller;


use think\Controller;
use think\Db;
use think\Exception;
use think\Log;

/**
 * 定时任务接口
 *
 * 以Crontab方式每分钟定时执行,且只可以Cli方式运行
 * @internal
 */
class Everyday extends Controller
{

    /**
     * 初始化方法,最前且始终执行
     */
    public function _initialize()
    {
        // 只可以以cli方式执行
//        if (!$this->request->isCli()) {
//            $this->error('Autotask script only work at client!');
//        }

        parent::_initialize();

        // 清除错误
        error_reporting(0);

        // 设置永不超时
        set_time_limit(0);
    }

    /**
     * 执行定时任务
     */
    public function index()
    {
        $this->coupon_status();

    }

    public function coupon_status(){
        // 结算
        Db::startTrans();
        try {
            $user_coupon = Db::name('user_coupon')->where('coupon_status',0)->where('expiration','<',time())->select();
            foreach ($user_coupon as $k=>$v){
                Db::name('user_coupon')->where('id',$v['id'])->setField('coupon_status',2);
            }
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            trace('[优惠券]:,' . date('Y-m-d H:i:s') . $e->getMessage(), 'debug');
        }
    }

}
