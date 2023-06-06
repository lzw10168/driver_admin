<?php

namespace app\admin\model;

use think\Model;


class DdriveSfOrder extends Model
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
        'order_type_text',
        'start_time_text',
        'status_text',
        'cancel_type_text',
        'assess_text',
        'pay_type_text',
        'pay_time_text',
        'pay_status_text',
        'cancel_time_text'
    ];
    

    
    public function getOrderTypeList()
    {
        return ['1' => __('Order_type 1'), '2' => __('Order_type 2')];
    }

    public function getStatusList()
    {
        return ['-2' => __('Status -2'), '-1' => __('Status -1'), '1' => __('Status 1'), '2' => __('Status 2'), '3' => __('Status 3'), '4' => __('Status 4'), '5' => __('Status 5')];
    }

    public function getCancelTypeList()
    {
        return [' 1' => __('Cancel_type  1'), '2' => __('Cancel_type 2'), '3' => __('Cancel_type 3'), '4' => __('Cancel_type 4'), '5' => __('Cancel_type 5'), '6' => __('Cancel_type 6'), '7' => __('Cancel_type 7'), '8' => __('Cancel_type 8'), '9' => __('Cancel_type 9'), '10' => __('Cancel_type 10'), '11' => __('Cancel_type 11'), '12' => __('Cancel_type 12')];
    }

    public function getAssessList()
    {
        return ['0' => __('Assess 0'), '1' => __('Assess 1')];
    }

    public function getPayTypeList()
    {
        return ['1' => __('Pay_type 1'), '2' => __('Pay_type 2'), '3' => __('Pay_type 3')];
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


    public function getStartTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['start_time']) ? $data['start_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getCancelTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['cancel_type']) ? $data['cancel_type'] : '');
        $list = $this->getCancelTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getAssessTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['assess']) ? $data['assess'] : '');
        $list = $this->getAssessList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getPayTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['pay_type']) ? $data['pay_type'] : '');
        $list = $this->getPayTypeList();
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


    public function getCancelTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['cancel_time']) ? $data['cancel_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setStartTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setPayTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setCancelTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }
    public function user()
    {
        return $this->belongsTo('User', 'user_id', 'id', [], 'LEFT')->field('username,mobile')->setEagerlyType(0);
    }
    public function driver()
    {
        return $this->belongsTo('User', 'other_user_id', 'id', [], 'LEFT')->field('username,mobile')->setEagerlyType(0);
    }

}
