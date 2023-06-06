<?php

namespace addons\ddrive\model;

use think\Model;

class Hyorder extends Model
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
        'status_text',
        'comment_status_text',
        'appointment_time_text',
        'pay_type_text'
    ];

    public function getStatusList()
    {
        return ['-2' => __('Status -2'),'-1' => __('Status -1'), '0' => __('Status 0'), '1' => __('Status 1'), '2' => __('Status 2'), '3' => __('Status 3'), '4' => __('Status 4'),'5' => __('Status 5'), '99' => __('Status 99')];
    }

    public function getCommentStatusList()
    {
        return ['0' => __('Comment_status 0'), '1' => __('Comment_status 1')];
    }
    public function getTypeList()
    {
        return ['1' => __('Type 1'), '2' => __('Type 2')];
    }
    public function getDemand()
    {
        return ['1' => __('Demand 1'),'2' => __('Demand 2'), '3' => __('Demand 3'), '4' => __('Demand 4'), '5' => __('Demand 5'), '6' => __('Demand 6'), '7' => __('Demand 7')];
    }
    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list  = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getCommentStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['comment_status']) ? $data['comment_status'] : '');
        $list  = $this->getCommentStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }
    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['Type']) ? $data['Type'] : '');
        $list  = $this->getTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }
    public function getAppointmentTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['appointment_time']) ? $data['appointment_time'] : '');
        return is_numeric($value) ? ($value ? date("m-d H:i", $value) : '') : $value;
    }
    public function getPayTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['pay_type']) ? $data['pay_type'] : '');
        $list  = ['1' => '余额', '2' => '微信', '3' => '支付宝'];
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function user()
    {
        return $this->belongsTo('\\app\\admin\\model\\User', 'user_id', 'id', [], 'LEFT')->field('avatar,mobile,group_id,id,username,nickname,email,prevtime,logintime,jointime')->setEagerlyType(0);
    }
    public function driver()
    {
        return $this->belongsTo('\\app\\admin\\model\\User', 'cargo_driver_id', 'id', [], 'LEFT')->field('avatar,mobile,group_id,id,username,nickname,email,prevtime,logintime,jointime')->setEagerlyType(0);
    }
    public function shaddress()
    {
        return $this->hasMany('Hyaddress', 'order_id', 'id');
    }
}
