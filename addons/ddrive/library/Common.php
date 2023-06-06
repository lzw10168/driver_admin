<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/12/14
 * Time: 10:42
 */
namespace addons\ddrive\library;

class Common{
    /**
     * 得到类似几分钟前等
     */
    function getTimeInfoUser($time){
        $mtime=time()-$time;
        if($mtime<=60){
            $time=$mtime.'秒前';
        }else if($mtime>60 && $mtime<3600){
            $time=round($mtime/60).'分前';
        }else if($mtime>=3600 && $mtime<3600*24){
            $time=round($mtime/3600).'小时前';
        }else if($mtime>=(3600*24) && $mtime<(3600*24*2)){
            $time='昨日'.date('H:i',$time);
        }else if($mtime>=(3600*24*2) && $mtime<(3600*24*3)){
            $time='两天前';
        }else if($mtime>=(3600*24*3) && $mtime<(3600*24*4)){
            $time='三天前';
        }else if($mtime>=(3600*24*7) && $mtime<(3600*24*14)){
            $time='一周前';
        }else if($mtime>=(3600*24*14) && $mtime<(3600*24*21)){
            $time='两周前';
        }else if($mtime>=(3600*24*30) && $mtime<(3600*24*60)){
            $time='一个月前';
        }else if($mtime>=(3600*24*60) && $mtime<(3600*24*90)){
            $time='两个月前';
        }else{
            $time=date('Y-m-d',$time);
        }
        return $time;
    }

    /**
     * 获取微信openid
     *
     * @return void
     */
    function getOpenid($code = '')
    {
        if ($code) {
            $wx_info['code'] = $code;
        }
        $config  = get_addon_config('ddrive');
        $appid   = $config['wx_appid'];
        $secret  = $config['wx_secret'];
        $api    = "https://api.weixin.qq.com/sns/jscode2session?appid={$appid}&secret={$secret}&js_code={$wx_info['code']}&grant_type=authorization_code";

        $res = \fast\Http::get($api);
        $res = json_decode($res, true);
        if ($res['session_key']) {

            if (isset($res['errmsg'])) {
                return $res['errmsg'];
            } else {
                return  $data = [ 'openid' => $res['openid']];
            }
        }
    }

    //计算奖励金钱乘
    function except($money1, $money, $scale = '0')
    {
        $newMoney = bcdiv($money1, $money, $scale);
        return $newMoney;
    }

//计算金钱加
    function math_add($money, $money1, $scale = '2')
    {
        return bcadd($money, $money1, $scale);
    }

//计算金钱减
    function math_jian($money, $money1, $scale = '2')
    {
        return bcsub($money, $money1, $scale);
    }

    /**对象转数组
     * object_array
     * @des
     * @param $array
     * @return array
     */
    function object_array($array)
    {
        if (isset($array)) {
            return $array->toArray();
        }
        return [];
    }
}