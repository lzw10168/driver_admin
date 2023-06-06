<?php

namespace app\admin\model;

use think\Model;


class Market extends Model
{

    

    

    // 表名
    protected $name = 'market';
    
     // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'dj_status_text',
        'sf_status_text',
        'dc_status_text',
    ];
    





}
