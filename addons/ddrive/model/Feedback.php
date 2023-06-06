<?php

namespace addons\ddrive\model;

use think\Model;

class Feedback extends Model
{

    // 表名
    protected $name = 'ddrive_feedback';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];

    public function user()
    {
        return $this->belongsTo('\\app\\admin\\model\\User', 'user_id', 'id', [], 'LEFT')->field('avatar,group_id,id,username,nickname,email,prevtime,logintime,jointime')->setEagerlyType(0);
    }
}
