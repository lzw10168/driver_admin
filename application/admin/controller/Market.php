<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 币币合约控制管理
 *
 * @icon fa fa-circle-o
 */
class Market extends Backend
{
    
    /**
     * Market模型对象
     * @var \app\admin\model\Market
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Market();
    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */
    /**
     * @return string|\think\response\Json
     * @throws \think\Exception
     */
    public function index()
    {
        return $this->setting('commission');
    }

    /**
     * 会员设置
     * @return string
     * @throws \think\Exception
     */
    public function handlingset()
    {
        return $this->setting('handlingset');
    }
    /**
     * 设置
     * @param string $type
     * @return string
     * @throws \think\Exception
     */
    private function setting($type = 'commission', $params = false)
    {
        $row = $this->model->find();
        if (!$row) {
            $defaultVo['createtime'] = time();
            $defaultVo['updatetime'] = time();
            $this->model->data($defaultVo)->save();
        }
        if ($this->request->isPost()) {
            if ($params === false) {
                $params = $this->request->param("row/a");
            }
            if ($params) {
                //TODO 启用过滤，打开注释，设置每种type类型的允许键名
                try {
                    $result = $this->model->where('id',$row['id'])->update($params);
                    if ($result !== false) {
                        $this->success('');
                    } else {
                        $this->error($row->getError());
                    }
                } catch (\think\exception\PDOException $e) {
                    $this->error($e->getMessage());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

}
