<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/12/1
 * Time: 17:55
 */

namespace addons\ddrive\model;

use think\Model;

class RealVerified extends Model
{

    // 表名
    protected $name = 'real_verified';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'status_text',
    ];

    public function getStatusList()
    {
        return ['-1' => __('Status -1'), '0' => __('Status 0'), '1' => __('Status 1')];
    }

    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list  = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function user()
    {
        return $this->belongsTo('\\app\\admin\\model\\User', 'user_id', 'id', [], 'LEFT')->field('avatar,group_id,id,username,nickname,email,prevtime,logintime,jointime')->setEagerlyType(0);
    }
}