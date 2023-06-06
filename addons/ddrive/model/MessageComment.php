<?php

namespace addons\ddrive\model;

use think\Model;

class MessageComment extends Model
{

    // 表名
    protected $name = 'ddrive_message_comment';

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
        return $this->belongsTo('\\app\\common\\model\\User', 'user_id', 'id', [], 'LEFT')->field('avatar,group_id,id,username,nickname,email')->setEagerlyType(0);
    }

    public function message()
    {
        return $this->belongsTo('Message', 'message_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
