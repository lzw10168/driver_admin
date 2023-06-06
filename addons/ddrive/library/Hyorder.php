<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/2/23
 * Time: 14:12
 */
namespace addons\ddrive\library;

use addons\ddrive\model\Freight;

class Hyorder{
    /**
     * 根据经纬度算距离，返回结果单位是公里，先纬度，后经度
     * @param $lat1
     * @param $lng1
     * @param $lat2
     * @param $lng2
     * @return float|int
     */
    public static function GetDistance($lat1, $lng1, $lat2, $lng2)
    {
        $EARTH_RADIUS = 6378.137;

        $radLat1 = deg2rad($lat1);
        $radLat2 = deg2rad($lat2);
        $a = $radLat1 - $radLat2;
        $b = deg2rad($lng1) - deg2rad($lng2);
        $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2)));
        $s = $s * $EARTH_RADIUS;
        $s = round(round($s * 10000) / 10000);
        return $s;
    }
    /**
     * 根据距离，计算价格
     * @param $distance
     */

    public static function getPrice($distance,$carInfo){
        $distance_price = 0;
        if($distance <= $carInfo['start_mileage']){ //起始价距离
            $distance_price = $carInfo['start_price'];
        }
        if($carInfo['section1_min_mileage'] <= $distance && $distance <= $carInfo['section1_max_mileage']){ //分段价距离
            $section1_price = ($distance-$carInfo['section1_min_mileage'])*$carInfo['section1_price'];
            $distance_price = $carInfo['start_price'] + $section1_price;
        }
        if($distance >= $carInfo['section2_mileage']){ //分段距离
            $section1_price = ($carInfo['section1_max_mileage']-$carInfo['start_mileage'])*$carInfo['section2_price'];
            $section2_price = ($distance-$carInfo['section1_max_mileage'])*$carInfo['section2_price'];
            $distance_price = $carInfo['start_price'] + $section1_price + $section2_price;
        }

        return round($distance_price,1);
    }

    public static function getDemand($demand){
        $new_demand                 = explode(',', $demand);
        foreach ($new_demand as $k=>$v){
            $new_demand[$k] = (new \addons\ddrive\model\Hyorder())->getDemand()[$v];
        }
        $new_demand = implode(',', $new_demand);
        return $new_demand;
    }

    /**订单号
     * createOrderSn
     * @des
     * @return string
     */
    public static function createOrderSn()
    {
        $sn = date('Ymd', time()) . time() . mt_rand(1111, 9999);
        return $sn;
    }

}