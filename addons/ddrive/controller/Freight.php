<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/2/22
 * Time: 11:26
 */
namespace addons\ddrive\controller;

use app\common\controller\Api;

/**
 * 货运车辆接口
 */
class Freight extends Api{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 货运车辆名称
     *
     * @return void
     */
    public function car_name(){
        $car_name = (new \addons\ddrive\model\Freight())->field('car_name,id')->select();
        if(!$car_name){
            $this->error('平台暂无车辆可选');
        }
        foreach ($car_name as $k=>$v){
            $car_name[$k]['name'] = $v['car_name'];
        }
        $this->success('成功',$car_name);
    }
    /**
     * 货运车辆详情
     *
     * @return void
     */
    public function Info(){
        $car_id = $this->request->param('car_id','小面');
        if(empty($car_id)){
            $this->error('请选择车辆');
        }
        $carInfo = (new \addons\ddrive\model\Freight())->where('id',$car_id)->find();
        if(!$carInfo){
            $this->error('车辆不存在');
        }
        $carInfo['car_image'] = cdnurl($carInfo['car_image']);
        $this->success('成功',$carInfo);
    }

    /**
     * 货运车辆详情
     *
     * @return void
     */
    public function carInfo(){
        $carInfo = (new \addons\ddrive\model\Freight())->select();
        foreach ($carInfo as $k=>$v){
            $carInfo[$k]['car_image'] = cdnurl($v['car_image']);
        }
        $this->success('成功',$carInfo ? $carInfo : []);

    }
}