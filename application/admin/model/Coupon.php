<?php

namespace app\admin\model;

use think\Model;


class Coupon extends Model
{

    

    

    // 表名
    protected $name = 'coupon';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'coupon_type_text',
        'coupon_name_text',
        'coupon_status_text'
    ];
    

    
    public function getCouponTypeList()
    {
        return ['1' => __('Coupon_type 1'), '2' => __('Coupon_type 2'), '3' => __('Coupon_type 3')];
    }

    public function getCouponNameList()
    {
        return ['1' => __('Coupon_name 1'), '2' => __('Coupon_name 2')];
    }

    public function getCouponStatusList()
    {
        return ['0' => __('Coupon_status 0'), '1' => __('Coupon_status 1')];
    }


    public function getCouponTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['coupon_type']) ? $data['coupon_type'] : '');
        $list = $this->getCouponTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getCouponNameTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['coupon_name']) ? $data['coupon_name'] : '');
        $list = $this->getCouponNameList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getCouponStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['coupon_status']) ? $data['coupon_status'] : '');
        $list = $this->getCouponStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }




}
