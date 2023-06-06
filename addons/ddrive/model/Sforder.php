<?php

namespace addons\ddrive\model;

use think\Model;

class Sforder extends Model
{

    // 表名
    protected $name = 'ddrive_sf_order';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];



    public function getOrderTypeList()
    {
        return ['1' => __('Order_type 1'), '2' => __('Order_type 2')];
    }

    public function getStatusList()
    {
        return ['1' => __('Status 1'), '2' => __('Status 2'), '3' => __('Status 3'), '4' => __('Status 4'), '5' => __('Status 5')];
    }

    public function getAssessList()
    {
        return ['0' => __('Assess 0'), '1' => __('Assess 1')];
    }

    public function getPayStatusList()
    {
        return ['0' => __('Pay_status 0'), '1' => __('Pay_status 1')];
    }


    public function getOrderTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['order_type']) ? $data['order_type'] : '');
        $list = $this->getOrderTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }

//    public function getStatusTextAttr($value, $data)
//    {
//        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
//        $list = $this->getStatusList();
//        return isset($list[$value]) ? $list[$value] : '';
//    }


    public function getAssessTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['assess']) ? $data['assess'] : '');
        $list = $this->getAssessList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getPayTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['pay_time']) ? $data['pay_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getPayStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['pay_status']) ? $data['pay_status'] : '');
        $list = $this->getPayStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    protected function setPayTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    public function user()
    {
        return $this->belongsTo('\\app\\admin\\model\\User', 'user_id', 'id', [], 'LEFT')->field('avatar,mobile,group_id,id,username,nickname,email,prevtime,logintime,jointime')->setEagerlyType(0);
    }
    public function driver()
    {
        return $this->belongsTo('\\app\\admin\\model\\User', 'driver_id', 'id', [], 'LEFT')->field('avatar,mobile,group_id,id,username,nickname,email,prevtime,logintime,jointime')->setEagerlyType(0);
    }
}
