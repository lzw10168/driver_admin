<?php

namespace addons\ddrive\model;

use think\Model;

class Message extends Model
{

    // 表名
    protected $name = 'ddrive_message';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];

    protected static function init()
    {
        self::afterInsert(function ($row) {
            $pk = $row->getPk();
            $row->getQuery()->where($pk, $row[$pk])->update(['weigh' => $row[$pk]]);
        });
    }

    public function user()
    {
        return $this->belongsTo('\\app\\common\\model\\User', 'user_id', 'id', [], 'LEFT')->field('avatar,group_id,id,username,nickname,email')->setEagerlyType(0);
    }
}
