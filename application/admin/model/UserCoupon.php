<?php

namespace app\admin\model;

use think\Model;


class UserCoupon extends Model
{

    

    

    // 表名
    protected $name = 'user_coupon';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'issuance_time_text',
        'usage_time_text',
        'coupon_status_text',
        'coupon_type_text',
        'coupon_name_text',
    ];
    

    
    public function getCouponStatusList()
    {
        return ['0' => __('Coupon_status 0'), '1' => __('Coupon_status 1'), '2' => __('Coupon_status 2')];
    }
    public function getCouponTypeList()
    {
        return ['1' => __('Coupon_type 1'), '2' => __('Coupon_type 2'), '3' => __('Coupon_type 3')];
    }

    public function getCouponNameList()
    {
        return ['1' => __('Coupon_name 1'), '2' => __('Coupon_name 2')];
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
    public function getIssuanceTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['issuance_time']) ? $data['issuance_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getUsageTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['usage_time']) ? $data['usage_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getCouponStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['coupon_status']) ? $data['coupon_status'] : '');
        $list = $this->getCouponStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setIssuanceTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setUsageTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    public function coupon()
    {
        return $this->belongsTo('Coupon', 'coupon_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
    public function user()
    {
        return $this->belongsTo('app\admin\model\User', 'user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
