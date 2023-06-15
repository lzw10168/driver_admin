<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\Config;

/**
 * 控制台
 *
 * @icon fa fa-dashboard
 * @remark 用于展示当前系统中的统计数据、统计报表及重要实时数据
 */
class Dashboard extends Backend
{

    /**
     * 查看
     */
    public function index()
    {
        $seventtime = \fast\Date::unixtime('day', -7);
        $paylist = $createlist = [];
        for ($i = 0; $i < 7; $i++)
        {
            $day = date("Y-m-d", $seventtime + ($i * 86400));
            $createlist[$day] = mt_rand(20, 200);
            $paylist[$day] = mt_rand(1, mt_rand(1, $createlist[$day]));
        }
        $hooks = config('addons.hooks');
        $uploadmode = isset($hooks['upload_config_init']) && $hooks['upload_config_init'] ? implode(',', $hooks['upload_config_init']) : 'local';
        $addonComposerCfg = ROOT_PATH . '/vendor/karsonzhang/fastadmin-addons/composer.json';
        Config::parse($addonComposerCfg, "json", "composer");
        $config = Config::get("composer");
        $addonVersion = isset($config['version']) ? $config['version'] : __('Unknown');
        // 总订单量
        $totalorder = \addons\ddrive\model\Order::count();
        // 总余额
        $totalmoney = \app\common\model\User::sum('money');
        // 今日平台收入, 计算今日订单的总金额
        $todayorderamount = \addons\ddrive\model\Order::whereTime('createtime', 'today')->sum('price');
        // 平台累计收入
        $totalorderamount = \addons\ddrive\model\Order::sum('price');

        // 今日订单量
        $todayorder = \addons\ddrive\model\Order::whereTime('createtime', 'today')->count();
        // 今日正在进行中的订单量
        // 状态:-2=已超时,-1=已取消,0=呼叫中,1=已接单,2=进行中,3=待支付,4=司机已到达,5=预约单待司机出发,99=已完成
        $todayongoingorder = \addons\ddrive\model\Order::whereTime('createtime', 'today')->where('status', 'in', [0, 1, 2, 3, 4, 5])->count();
        // 今日完单量
        $todayfinishorder = \addons\ddrive\model\Order::whereTime('createtime', 'today')->where('status', 99)->count();
        // 今日订单金额
        $todayorderamount = \addons\ddrive\model\Order::whereTime('createtime', 'today')->sum('price');
        // 今日信息费,detail表中,计算msg === '平台服务费' 的amount
        $todaymsgfee = \addons\ddrive\model\Details::whereTime('createtime', 'today')->where('msg', '平台服务费')->sum('amount');
        // 今日保险费,detail表中,计算msg === '订单保险费' 的amount
        $todayinsurefee = \addons\ddrive\model\Details::whereTime('createtime', 'today')->where('msg', '订单保险费')->sum('amount');
        // 今日订单平均时长, order表中duration字段
        $todayorderavgduration = \addons\ddrive\model\Order::whereTime('createtime', 'today')->avg('duration');
        // 平均里程, order表中distance字段 /1000
        $todayorderavgdistance = \addons\ddrive\model\Order::whereTime('createtime', 'today')->avg('distance');
        $todayorderavgdistance = round($todayorderavgdistance / 1000, 2);
        $this ->view ->assign([
            'totalorder' => $totalorder,
            'totalmoney' => $totalmoney,
            'todayorderamount' => $todayorderamount,
            'totalorderamount' => $totalorderamount,
            'todayorder' => $todayorder,
            'todayongoingorder' => $todayongoingorder,
            'todayfinishorder' => $todayfinishorder,
            'todayorderamount' => $todayorderamount,
            'todaymsgfee' => $todaymsgfee,
            'todayinsurefee' => $todayinsurefee,
            'todayorderavgduration' => $todayorderavgduration,
            'todayorderavgdistance' => $todayorderavgdistance,
            'totaluser'        => 35200,
            'totalviews'       => 219390,
            // 'totalorder'       => 32143,
            // 'totalorderamount' => 174800,
            'todayuserlogin'   => 321,
            'todayusersignup'  => 430,
            // 'todayorder'       => 2324,
            'unsettleorder'    => 132,
            'sevendnu'         => '80%',
            'sevendau'         => '32%',
            'paylist'          => $paylist,
            'createlist'       => $createlist,
            'addonversion'       => $addonVersion,
            'uploadmode'       => $uploadmode
        ]);
        return $this->view->fetch();

        // $this->view->assign([
        //     'totaluser'        => 35200,
        //     'totalviews'       => 219390,
        //     'totalorder'       => 32143,
        //     'totalorderamount' => 174800,
        //     'todayuserlogin'   => 321,
        //     'todayusersignup'  => 430,
        //     'todayorder'       => 2324,
        //     'unsettleorder'    => 132,
        //     'sevendnu'         => '80%',
        //     'sevendau'         => '32%',
        //     'paylist'          => $paylist,
        //     'createlist'       => $createlist,
        //     'addonversion'       => $addonVersion,
        //     'uploadmode'       => $uploadmode
        // ]);

    }

}
