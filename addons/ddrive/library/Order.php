<?php

namespace addons\ddrive\library;

class Order
{

    /**
     * 根据记录和开始时间计算价格
     *
     * @param int $distance 记录，单位米
     * @param int $time 开始时间/时
     * @param [type] $createtime 司机到达时间
     * @param [type] $starttime 订单开始时间，会根据司机到达时间计算用户是否超时
     * @return void
     */
    public static function getPrice($distance = 0, $time = null, $reachtime = null, $starttime = null, $waittime = null)
    {
        $site = get_addon_config('ddrive');
        if ($time == null) {
            $time = date('H');
        }
        // 根据时间计算基础价格使用夜间还是白天
        if ($time > $site['excision_time']) {
            $base_price = $site['night_base_price'];
        } else if ($time < $site['excision_start_time']) {
            $base_price = $site['night_base_price'];
        } else {
            $base_price = $site['base_price'];
        }

//        $base_price = $time > $site['excision_time'] ? $site['night_base_price'] : $site['base_price'];
        // 计算用户是否超时，司机到达后用户是否超过10分种
        if ($starttime && $reachtime && ($starttime - $reachtime) > $site['timeout'] * 60) {
          $base_price = $base_price + floor(($starttime - $reachtime) / ($site['timeout'] * 60)) * $site['timeout_price'];
        }
        if ($waittime) {
          // 转成数字
          $waittime = intval($waittime);
          // ms转成秒
          $waittime = $waittime / 1000;
          // 如果$waittime转成分钟后,小数位大于0.95,则向上取整
          // if (fmod($waittime / 60, 1) > 0.95) {
          //   $waittime = ceil($waittime / 60) * 60;
          // } 
          $starttime = $starttime ? $starttime : 0;
          $reachtime = $reachtime ? $reachtime : 0;
          if (!$starttime) {
            $base_price = floor(($starttime - $reachtime + $waittime) / ($site['timeout'] * 60)) * $site['timeout_price'];


          } else {
            $base_price = $base_price + floor(($starttime - $reachtime + $waittime) / ($site['timeout'] * 60)) * $site['timeout_price'];

          }

        }
        // 订单是开始状态
        if ($starttime) {
           // 达不到基础距离则使用基础价格
           if ($distance <= $site['mileage'] * 1000) {
              return $base_price;
            }
        } else if ($waittime) {
          return $base_price;
        } else {
          return 0;
        }
        // 超出基础距离则计算价格
        $kilometer_price = ($distance - $site['mileage'] * 1000) / 1000 * $site['kilometer_price'];
        return round($base_price + $kilometer_price, 2);
    }

    /**
     * 根据两点间的经纬度计算距离
     * @param $lat1
     * @param $lng1
     * @param $lat2
     * @param $lng2
     * @return int
     */
    public static function getDistance($lat1, $lng1, $lat2, $lng2)
    {
                                   //将角度转为狐度
        $radLat1 = deg2rad($lat1); //deg2rad()函数将角度转换为弧度
        $radLat2 = deg2rad($lat2);
        $radLng1 = deg2rad($lng1);
        $radLng2 = deg2rad($lng2);
        $a       = $radLat1 - $radLat2;
        $b       = $radLng1 - $radLng2;
        $s       = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2))) * 6378.137 * 1000;
        return $s;
    }
}
