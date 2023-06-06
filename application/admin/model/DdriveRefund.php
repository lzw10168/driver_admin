<?php

namespace app\admin\model;

use think\Model;


class DdriveRefund extends Model
{

    

    

    // 表名
    protected $name = 'ddrive_refund';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'pay_type_text',
        'check_status_text',
        'apply_time_text',
        'confirm_time_text',
        'success_time_text'
    ];
    

    
    public function getPayTypeList()
    {
        return ['1' => __('Pay_type 1'), '2' => __('Pay_type 2'), '3' => __('Pay_type 3')];
    }

    public function getCheckStatusList()
    {
        return ['0' => __('Check_status 0'), '1' => __('Check_status 1'),  '-1' => __('Check_status -1')];
    }


    public function getPayTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['pay_type']) ? $data['pay_type'] : '');
        $list = $this->getPayTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getCheckStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['check_status']) ? $data['check_status'] : '');
        $list = $this->getCheckStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getApplyTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['apply_time']) ? $data['apply_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getConfirmTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['confirm_time']) ? $data['confirm_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getSuccessTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['success_time']) ? $data['success_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setApplyTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setConfirmTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setSuccessTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    public function user()
    {
        return $this->belongsTo('app\admin\model\User', 'user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
