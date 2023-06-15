<?php

namespace addons\ddrive\model;

use think\Model;

class Order extends Model
{

    // 表名
    protected $name = 'ddrive_order';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'status_text',
        'comment_text',
    ];

    public function getStatusList()
    {
        return ['-2' => __('Status -2'),'-1' => __('Status -1'), '0' => __('Status 0'), '1' => __('Status 1'), '4' => __('Status 4'), '2' => __('Status 2'), '3' => __('Status 3'), '99' => __('Status 99'), '5' => __('Status 5'), '-2' => __('Status -2')];
    }

    public function getCommentList()
    {
        return ['0' => __('Comment 0'), '1' => __('Comment 1')];
    }
    public function getTypeList()
    {
        return ['1' => __('Type 1'), '2' => __('Type 2')];
    }

    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list  = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getCommentTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['comment']) ? $data['comment'] : '');
        $list  = $this->getCommentList();
        return isset($list[$value]) ? $list[$value] : '';
    }
    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['Type']) ? $data['Type'] : '');
        $list  = $this->getTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function user()
    {
        return $this->belongsTo('\\app\\admin\\model\\User', 'user_id', 'id', [], 'LEFT')->field('avatar,mobile,group_id,id,username,nickname,email,prevtime,logintime,jointime,score')->setEagerlyType(0);
    }
    public function driver()
    {
        return $this->belongsTo('\\app\\admin\\model\\User', 'driver_id', 'id', [], 'LEFT')->field('avatar,mobile,group_id,id,username,nickname,email,prevtime,logintime,jointime,score')->setEagerlyType(0);
    }
}
