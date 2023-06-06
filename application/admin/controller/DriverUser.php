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
        // 过滤请求参数中的 HTML 标签和空格，并把它们作为筛选条件
        $this->request->filter(['strip_tags', 'trim']);

        // 判断是否是 AJAX 请求
        if ($this->request->isAjax()) {
            // 如果请求中包含名为 keyField 的参数，则说明该请求是从 Selectpage 页面发送的，因此需要跳转到 Selectpage 页面处理
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            
            // 解析请求参数，包括筛选条件、排序方式、分页和每页数量等
            list( $sort, $order, $offset, $limit) = $this->buildparams();
            // 构造额外的筛选条件，只查询 driver_verified 表中 status 字段为 1 的记录
            $where2 = [];
            $where2['driver_verified.status'] = '1';
            // 打印$where
            // 使用 with 方法关联 user 表，查询满足 where 条件和 where2 条件的数据并按照 sort 和 order 排序，分页查询 limit 条数据
            $list = $this->model
                ->with(['user'])
                ->where($where)
                ->where($where2)
                ->order($sort, $order)
                ->paginate($limit);
            
            // 对查询结果进行遍历，只返回关联表 user 中的 username 和 mobile 字段数据
            foreach ($list as $row) {
                $row->getRelation('user')->visible(['username', 'mobile']);
            }
            
            // 组合查询结果并以 JSON 格式返回
            $result = ["total" => $list->total(), "rows" => $list->items()];
            return json($result);
        }

        // 如果不是 AJAX 请求，则渲染视图并返回
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
