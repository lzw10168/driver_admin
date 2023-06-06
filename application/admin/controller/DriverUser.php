<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/12/24
 * Time: 13:44
 */
namespace app\admin\controller;

use app\common\controller\Backend;
use think\Db;
use think\Exception;
use think\exception\PDOException;
use think\exception\ValidateException;

/**
 * 驾驶证认证管理
 *
 * @icon fa fa-circle-o
 */
class DriverUser extends Backend
{

    /**
     * DriverVerified模型对象
     * @var \app\admin\model\DriverVerified
     */
    protected $model = null;
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\DriverVerified();
        $this->view->assign("statusList", $this->model->getStatusList());
    }

    public function import()
    {
        parent::import();
    }

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $where2 = [];
            $where2['driver_verified.status'] = '1';
            $list = $this->model
                ->with(['user'])
                ->where($where)
                ->where($where2)
                ->order($sort, $order)
                ->paginate($limit);
            foreach ($list as $row) {
                $row->getRelation('user')->visible(['username', 'mobile']);
            }
            $result = ["total" => $list->total(), "rows" => $list->items()];

            return json($result);
        }
        return $this->view->fetch();
    }


    /**
     * 批量操作
     * @param string $ids
     */
    public function multi($ids = "")
    {
        $params = $this->request->request('params');
        parse_str($params, $paramsArr);
        if (isset($paramsArr)) {
            $field = $this->model::get($ids);
            if ($paramsArr['status'] == 0) {
                $paramsArr['status'] = -1;
                Db::name('user_verified')->where('user_id', $field['user_id'])->setField('driver_verified', -1);
            }
            if ($paramsArr['status'] == 1) {
                Db::name('user_verified')->where('user_id', $field['user_id'])->setField('driver_verified', 1);
            }
            $field->save($paramsArr);
            $this->success('操作成功');
        }
        return parent::multi($ids);
    }
}