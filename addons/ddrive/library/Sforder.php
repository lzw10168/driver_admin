<?php

namespace addons\ddrive\library;

class Sforder
{
    /**
     * 根据时间选择周几
     * @param $week
     * @return int
     */
    public static function getWeek($week)
    {
                                   //将角度转为狐度
        $weekarray = ["星期日", "星期一", "星期二", "星期三", "星期四", "星期五", "星期六"];

        return $weekarray[$week];
    }

    /**
     * 订单状态
     * @param $week
     * @return int
     */
    public static function getStatus($status){
        switch ($status) {
            case '1':
                $status = '待接单';
                break;
            case '2':
                $status = '待确认';
                break;
            case '3':
                //手机网页支付,跳转
                $status = '待出发';
                break;
            case '4':
                //APP支付,直接返回字符串
                $status = '行驶中';
                break;
            case '5':
                //APP支付,直接返回字符串
                $status = '已完成';
                break;
            case '-1':
                //APP支付,直接返回字符串
                $status = '已取消';
                break;
            case '-2':
                //APP支付,直接返回字符串
                $status = '已超时';
                break;
            default:
        }
        return $status;
    }
}
