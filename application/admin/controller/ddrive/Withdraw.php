<?php

namespace app\admin\controller\ddrive;

use app\common\controller\Backend;
use think\Db;

/**
 * 提现管理
 *
 * @icon fa fa-circle-o
 */
class Withdraw extends Backend
{

    /**
     * Withdraw模型对象
     * @var \addons\ddrive\model\Withdraw
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \addons\ddrive\model\Withdraw;
        $this->view->assign("statusList", $this->model->getStatusList());
    }

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    /**
     * 查看
     */
    public function index()
    {
        //当前是否为关联查询
        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total                                       = $this->model
                ->with(['user'])
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->with(['user'])
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            foreach ($list as $row) {

            }
            $list   = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 批量更新
     */
    public function multi($ids = "")
    {
        $ids = $ids ? $ids : $this->request->param("ids");
        if ($ids) {
            if ($this->request->has('params')) {
                parse_str($this->request->post("params"), $values);
                $values = array_intersect_key($values, array_flip(is_array($this->multiFields) ? $this->multiFields : explode(',', $this->multiFields)));
                if ($values || $this->auth->isSuperAdmin()) {
                    $adminIds = $this->getDataLimitAdminIds();
                    if (is_array($adminIds)) {
                        $this->model->where($this->dataLimitField, 'in', $adminIds);
                    }
                    $count = 0;
                    Db::startTrans();
                    try {
                        $list = $this->model->where($this->model->getPk(), 'in', $ids)->select();
                        foreach ($list as $index => $item) {
                            $count += $item->allowField(true)->isUpdate(true)->save($values);
                            // 拒绝提现，需要返还用户余额
                            if (isset($values['status']) && $values['status'] == -1) {
                                $this->returndRefuse($item);
                            }

                            // 提现成功，企业付款到零钱
                            if (isset($values['status']) && $values['status'] == 1) {
                                $this->returndSuccess($item);
                            }
                        }
                        Db::commit();
                    } catch (\PDOException $e) {
                        Db::rollback();
                        $this->error($e->getMessage());
                    } catch (\Exception $e) {
                        Db::rollback();
                        $this->error($e->getMessage());
                    }
                    if ($count) {
                        $this->success();
                    } else {
                        $this->error(__('No rows were updated'));
                    }
                } else {
                    $this->error(__('You have no permission'));
                }
            }
        }
        $this->error(__('Parameter %s can not be empty', 'ids'));
    }

    /**
     * 拒绝提现，返还用户余额
     *
     * @param [type] $item
     * @return void
     */
    public function returndRefuse($item)
    {
        $user = Db::name('user')->where('id', $item['user_id'])->find();
        $log  = [
            'user_id'    => $user['id'],
            'money'      => $item['money'],
            'before'     => $user['money'],
            'after'      => $user['money'] + $item['money'],
            'memo'       => '提现失败返还',
            'createtime' => time(),
        ];
        // 添加用户余额日志
        Db::name('user_money_log')->insert($log);
        // 修改用户余额
        Db::name('user')->where('id', $item['user_id'])->setInc('money', $item['money']);
        return true;
    }

    /**
     * 提现成功，企业付款到零钱
     *
     * @return void
     */
    public function returndSuccess($item)
    {
        $openid = Db::name('ddrive_user_token')->where('user_id', $item['user_id'])->value('openid');
        \addons\ddrive\library\Transfer::pay('mini_program', $openid, $item['money'], $item['id'], '微信自助提现');
    }
}
