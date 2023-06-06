<?php

namespace addons\ddrive\model;

use addons\ddrive\extend\Common;
use app\admin\model\User;
use think\Db;
use think\Model;


class Cash extends Model
{




    const STATUS_AUDIT = 1;    //待审核
    const STATUS_SUCCESS = 2; //成功
    const STATUS_FAIL = 3;     //失败
    // 表名
    protected $name = 'cash';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'pay_type_text',
        'status_text'
    ];
    

    
    public function getPayTypeList()
    {
        return ['1' => __('Pay_type 1'), '2' => __('Pay_type 2')];
    }

    public function getStatusList()
    {
        return ['1' => __('Status 1'), '2' => __('Status 2'), '3' => __('Status 3')];
    }


    public function getPayTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['pay_type']) ? $data['pay_type'] : '');
        $list = $this->getPayTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function addCash($data)
    {
        $ddrive_config = get_addon_config('ddrive');
        $userInfo = Db::name('user')->where('id', $data['user_id'])->find();
        $availabel_bonus = $userInfo['money'] ? $userInfo['money']: 0;
        if (empty($data['account_number']) || empty($data['payee']) || empty($data['pay_type'])) {
            $this->error = '请选择收款账户';
        }
        if (!in_array($data['pay_type'], [1, 2])) {
            $this->error = '收款方式错误';
            return false;
        }
        if (!$data['money'] || $data['money'] < $ddrive_config['withdraw_min'] || $data['money'] > $ddrive_config['withdraw_max']) {
            $this->error = '提现金额错误';
            return false;
        }
        $inCash = [];
        //手续费
        $withdraw_rate = $ddrive_config['withdraw_rate'] ? $ddrive_config['withdraw_rate'] : 0;
        $fee_money = ($data['money'])*($withdraw_rate/100);
        //$inCash['actual_payment'] = (new \addons\ddrive\library\Common())::math_jian($data['money'], $fee_money);//实际到账金额
        $inCash['actual_payment'] = (new \addons\ddrive\library\Common())->math_jian($data['money'], $fee_money);//实际到账金额
        $payable_money = $data['money'];

        if ($availabel_bonus < $payable_money || $inCash['actual_payment']<=0) {
            $this->error = '余额不足';
            return false;
        }
        $inCash['money'] = $data['money'];
        $inCash['createtime'] = time();
        $inCash['updatetime'] = time();
        $inCash['user_id'] = $data['user_id'];
        $inCash['account_number'] = $data['account_number'];
        $inCash['payee'] = $data['payee'];
        $inCash['pay_type'] = $data['pay_type'];
        $inCash['actual_payment'] = $inCash['actual_payment'];
        $inCash['commission'] = $fee_money;
        $id = $this->insertGetId($inCash);
        //可用奖金
        //$new_availabel_bonus = math_jian($cash_money['all_bonus'], $data['money'] * 10000);
        (new User())->where('id', $data['user_id'])->setDec('money', $payable_money);
        //减钱
        $detailModel = new Details();
        $detailModel->addDetail($data['user_id'],2,'提现申请中',$data['money'],1,1,$id);
        return $id;
    }
    public function user()
    {
        return $this->belongsTo('User', 'user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
    public function admin()
    {
        return $this->belongsTo('Admin', 'admin_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

}
