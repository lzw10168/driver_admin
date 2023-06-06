<?php

namespace app\admin\model;

use think\Model;


class DdriveOrderComment extends Model
{

    

    

    // 表名
    protected $name = 'ddrive_order_comment';
    
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
        return $this->belongsTo('User', 'user_id', 'id', [], 'LEFT')->field('username,mobile')->setEagerlyType(0);
    }
    public function driver()
    {
        return $this->belongsTo('User', 'driver_id', 'id', [], 'LEFT')->field('username,mobile')->setEagerlyType(0);
    }




}
