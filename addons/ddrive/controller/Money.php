<?php

namespace addons\ddrive\controller;

use app\common\controller\Api;
use addons\ddrive\model\Cash;
use think\Db;

/**
 * 余额接口
 */
class Money extends Api
{
    protected $noNeedLogin = [];
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();
        $this->withdraw = new \addons\ddrive\model\Withdraw();
    }

    /**
     * 获取余额日志
     *
     * @return void
     */
    public function withdrawList()
    {
        $pageSize = $this->request->param('pageSize', 10);
        $list     = $this->withdraw->where('user_id', $this->auth->id)->order('id desc')->paginate($pageSize)->each(function ($item) {
            $item['createdate'] = date('Y-m-d H:i:s', $item['createtime']);
            return $item;
        });
        $this->success("提现成功", $list);
    }

    /**
     * 提现
     *
     * @return void
     */
    public function withdraw()
    {
        $money = $this->request->param('money', 0);
        if ($money <= 0) {
            $this->error('金额不正确');
        }
        $user = $this->auth->getUser();
        if ($user['money'] <= 0) {
            $this->error('用户余额不足');
        }
        $data = [
            'user_id'    => $user['id'],
            'money'      => $money,
            'createtime' => time(),
            'status'     => 0,
        ];
        $res = $this->withdraw->insert($data);
        if ($res) {
            $log = [
                'user_id'    => $user['id'],
                'money'      => $money,
                'before'     => $user->money,
                'after'      => $user->money - $money,
                'memo'       => '用户提现',
                'createtime' => time(),
            ];
            // 添加用户余额日志
            Db::name('user_money_log')->insert($log);
            // 修改用户余额
            $user->money = $user->money - $money;
            $user->save();
            $this->error('提现申请成功，请等待管理员审核');
        } else {
            $this->error($res['提现申请失败']);
        }

    }
    /**
     * 提交提现
     * @ApiMethod   (POST)
     * @ApiParams (name="money", type="int", required=true, description="提现金额")
     * @ApiParams (name="account_number", type="int", required=true, description="收款账号")
     * @ApiParams (name="payee", type="int", required=true, description="收款人")
     */
    public function sbCash(){
        $params = $this->request->param();
        $data = [
            'user_id' => $this->auth->id,
            'money' => $params['money'],
            'account_number' => $params['account_number'],
            'payee' => $params['payee'],
            'pay_type' => 2,
        ];
        $rechargeModel = new Cash();
        Db::startTrans();
        try {
            $id = $rechargeModel->addCash($data);
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
        }
        if (!$id) {
            $this->error($rechargeModel->getError());
            Db::rollback();
        } else {
            Db::commit();
        }
        $this->success('成功');
    }
}
