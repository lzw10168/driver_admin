<?php

namespace addons\ddrive\controller;

use app\common\controller\Api;
use think\Db;

/**
 * 轮播图管理
 *
 * @icon fa fa-circle-o
 */
class Banner extends Api
{

    // 无需登录的接口,*表示全部
    protected $noNeedLogin = '*';
    // 无需鉴权的接口,*表示全部
    protected $noNeedRight = '*';

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \addons\ddrive\model\Banner;
    }

    public function index()
    {
        $map               = [];
        $map['effectime']  = ['<', time()];
        $map['expiretime'] = ['>', time()];
        $list              = $this->model->where($map)->order('weigh desc,id desc')->select();
        $this->success("", $list);
    }

    public function car_brand(){
        $car_brand = Db::name('car_brand')->where('status',1)->select();
        foreach ($car_brand as $k=>$v){
            $car_brand[$k]['pic_image'] = cdnurl($v['pic_image']);
        }
        $this->success('成功',['car_brand'=>$car_brand]);
    }

    public function  car_type(){
        $brand_id = $this->request->param('brand_id');
        $car_type = Db::name('car_type')->where('brand_id',$brand_id)->field('id,brand_id,name,pic_image')->select();
        if(!$car_type){
            $this->error('该品牌暂无车辆');
        }
        foreach ($car_type as $k=>$v){
            $car_type[$k]['pic_image'] = cdnurl($v['pic_image']);
        }
        $this->success('成功',['car_type'=>$car_type]);
    }

    /**
     * market_setting
     * @des
     */
    public function market_setting()
    {
        $list = Db::name('market')->field('dj_status,sf_status,dc_status')->find();
        $this->success("", $list);
    }
}
