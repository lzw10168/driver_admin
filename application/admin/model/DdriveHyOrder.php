<?php

namespace app\admin\model;

use think\Model;


class DdriveHyOrder extends Model
{

    

    

    // 表名
    protected $name = 'ddrive_hy_order';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'type_text',
        'appointment_time_text',
        'status_text',
        'cancel_type_text',
        'comment_status_text',
        'complete_time_text',
        'fail_time_text',
        'pay_type_text',
        'pay_method_text',
        'pay_time_text'
    ];
    

    
    public function getTypeList()
    {
        return ['1' => __('Type 1'), '2' => __('Type 2')];
    }

    public function getStatusList()
    {
        return ['-2' => __('Status -2'), '-1' => __('Status -1'), '0' => __('Status 0'), '1' => __('Status 1'), '2' => __('Status 2'), '3' => __('Status 3'), '4' => __('Status 4'), '5' => __('Status 5'), '99' => __('Status 99'), '7' => __('Status 7')];
    }

    public function getCancelTypeList()
    {
        return [' 1' => __('Cancel_type  1'), '2' => __('Cancel_type 2'), '3' => __('Cancel_type 3'), '4' => __('Cancel_type 4'), '5' => __('Cancel_type 5'), '6' => __('Cancel_type 6'), '7' => __('Cancel_type 7'), '8' => __('Cancel_type 8'), '9' => __('Cancel_type 9'), '10' => __('Cancel_type 10'), '11' => __('Cancel_type 11'), '12' => __('Cancel_type 12')];
    }

    public function getCommentStatusList()
    {
        return ['0' => __('Comment_status 0'), '1' => __('Comment_status 1')];
    }

    public function getPayTypeList()
    {
        return ['1' => __('Pay_type 1'), '2' => __('Pay_type 2'), '3' => __('Pay_type 3')];
    }

    public function getPayMethodList()
    {
        return ['1' => __('Pay_method 1'), '2' => __('Pay_method 2')];
    }


    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['type']) ? $data['type'] : '');
        $list = $this->getTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getAppointmentTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['appointment_time']) ? $data['appointment_time'] : '');
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


    public function getCommentStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['comment_status']) ? $data['comment_status'] : '');
        $list = $this->getCommentStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getCompleteTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['complete_time']) ? $data['complete_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getFailTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['fail_time']) ? $data['fail_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getPayTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['pay_type']) ? $data['pay_type'] : '');
        $list = $this->getPayTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getPayMethodTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['pay_method']) ? $data['pay_method'] : '');
        $list = $this->getPayMethodList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getPayTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['pay_time']) ? $data['pay_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setAppointmentTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setCompleteTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setFailTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setPayTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    public function user()
    {
        return $this->belongsTo('User', 'user_id', 'id', [], 'LEFT')->field('username,mobile')->setEagerlyType(0);
    }
    public function driver()
    {
        return $this->belongsTo('User', 'cargo_driver_id', 'id', [], 'LEFT')->field('username,mobile')->setEagerlyType(0);
    }
    public function car()
    {
        return $this->belongsTo('DdriveHyFreight', 'car_id', 'id', [], 'LEFT')->field('car_name')->setEagerlyType(0);
    }
}
