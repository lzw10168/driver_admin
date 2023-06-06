<?php
/**
 * CityRefund.php
 * @des
 * Created by PhpStorm.
 * Date: 2021/1/7
 * Time: 16:12
 */

namespace addons\ddrive\model;


use think\Model;

class DriverRefund extends Model
{
    // 表名
    protected $name = 'ddrive_refund';
    // 追加属性
    protected $append = [
        'status_text',
    ];

    public function getInfo(array $where, string $field = "*", string $select = 'select')
    {
        return $this->where($where)->field($field)->$select();
    }

    public function getStatusList()
    {
        return ['-1' => '未到账', '0' => '未到账', '1' => '未到账', '2' => '已到账'];
    }

    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['check_status']) ? $data['check_status'] : '');
        $list  = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }
}