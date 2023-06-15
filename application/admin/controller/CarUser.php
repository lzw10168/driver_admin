<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\Db;
use think\Exception;
use think\exception\PDOException;
use think\exception\ValidateException;

/**
 * 车主认证管理
 *
 * @icon fa fa-circle-o
 */
class CarUser extends Backend
{
    
    /**
     * CardVerified模型对象
     * @var \app\admin\model\CardVerified
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\CardVerified;
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
    /**
     * 查看
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
            $where2['card_verified.status'] = '1';
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
     * 编辑
     */
    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
//        $sign_areas = Db::name('areas')->where(['id' => $row['sign_city']])->find();
//        $areas     = Db::name('areas')->where(['id' => $row['area']])->find();
//        $row['sign_province'] = $sign_areas['province'];
//        $row['sign_city'] = $sign_areas['city'];
//        $row['province'] = $areas['province'];
//        $row['city'] = $areas['city'];
//        $row['area'] = $areas['district'];
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                $params = $this->preExcludeFields($params);
                $result = false;
                Db::startTrans();
                try {
                    // if($row['status'] == 1 || $row['status'] == -1){
                    //     $this->error('当前状态无法操作');
                    // }
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                        $row->validateFailException(true)->validate($validate);
                    }
                    $result = $row->allowField(true)->save($params);
                    if($params['status'] == 1){
                        Db::name('user_verified')->where('user_id', $row['user_id'])->setField('card_verified', 1);
                    }
                    if($params['status'] == -1){
                        Db::name('user_verified')->where('user_id', $row['user_id'])->setField('card_verified', -1);
                    }
                    Db::commit();
                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (PDOException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    $this->success();
                } else {
                    $this->error(__('No rows were updated'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->view->assign("row", $row);
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
                Db::name('user_verified')->where('user_id', $field['user_id'])->setField('card_verified', -1);
            }
            if ($paramsArr['status'] == 1) {
                Db::name('user_verified')->where('user_id', $field['user_id'])->setField('card_verified', 1);
            }
            $field->save($paramsArr);
            $this->success('操作成功');
        }
        return parent::multi($ids);
    }

}
