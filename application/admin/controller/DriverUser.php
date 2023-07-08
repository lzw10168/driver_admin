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
use think\Log;
use Illuminate\Support\Arr;

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

     public function add()
     {
         if ($this->request->isPost()) {
             $params = $this->request->post("row/a");
             if ($params) {
                 $params = $this->preExcludeFields($params);
                 if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                     $params[$this->dataLimitField] = $this->auth->id;
                 }
                 $result = false;
                 Db::startTrans();
                 try {
                     //是否采用模型验证
                     if ($this->modelValidate) {
                         $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                         $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                         $this->model->validateFailException(true)->validate($validate);
                     }
                     $result = $this->model->allowField(true)->save($params);
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
                  // 往driver_status表中插入一条数据
                  $driver_status = [];
                  $driver_status['user_id'] = $params['user_id'];
                  $driver_status['status'] = '0';
                  $driver_status['create_status'] = '0';
                  $driver_status['createtime'] = time();

                  Db::name('driver_status')->insert($driver_status);

                  $real_verified = [];
                  $real_verified['user_id'] = $params['user_id'];
                  $real_verified['status'] = '1';
                  $real_verified['createtime'] = time();
                  $real_verified['truename'] = $params['truename'];
                  Db::name('real_verified')->insert($real_verified);

                  // user_verified表中插入一条数据
                  $user_verified = [];
                  $user_verified['user_id'] = $params['user_id'];
                  $user_verified['real_verified'] = '1';
                  $user_verified['driver_verified'] = '1';
                  $user_verified['card_verified'] = '1';
                  $user_verified['createtime'] = time();
                  $user_verified['updatetime'] = time();
                  Db::name('user_verified')->insert($user_verified);
                     $this->success();
                 } else {
                     $this->error(__('No rows were inserted'));
                 }
             }
             $this->error(__('Parameter %s can not be empty', ''));
         }
         return $this->view->fetch();
     }



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
            // 单独取search参数
            $search = $this->request->request('search');
            $where2 = [];
            $where2['driver_verified.status'] = '1';
            $list = $this->model
                ->with(['user'])
                // 从user表中取出username和mobile 匹配$search
                ->where('user.username|user.mobile', 'like', '%' . $search . '%')
                // ->where($where)
                ->where($where2)
                ->order($sort, $order)
                ->paginate($limit);
            // 在日志中打印
            foreach ($list as $row) {
                $row->getRelation('user')->visible(['username', 'mobile', 'longitude', 'latitude', 'money', 'nickname']);
            }
            // 再通过user_id去driver_status表中取出driver_status, driver_create_status
            foreach ($list as $key => $value) {
              $driver_status = Db::name('driver_status')->where('user_id', $value['user_id'])->find();
              // 日志中打印
              if ($driver_status) {
                $list[$key]['driver_status'] = $driver_status['status'];
                $list[$key]['driver_create_status'] = $driver_status['create_status'];
              }
          }
            $result = ["total" => $list->total(), "rows" => $list->items()];

            return json($result);
        }
        return $this->view->fetch();
    }


       /**
     * 编辑
     */
    // 充值 post请求
    public function chonzhi() {
        $ids = $this->request->request('ids');
        $money = $this->request->request('money');
        // ids = 1,1,1,
        $ids = explode(',', $ids);
        // 循环ids, 拿到user_id, 更新user表中的money字段
        $res = false;
        foreach ($ids as $key => $value) {
            $user_id = Db::name('driver_verified')->where('id', $value)->value('user_id');
            $driver_name = Db::name('real_verified')->where('user_id', $user_id)->value('truename');
            $before_money = Db::name('user')->where('id', $user_id)->value('money');
            $after_money = $before_money + $money;
            $mobile = Db::name('user')->where('id', $user_id)->value('mobile');
            $res = Db::name('user')->where('id', $user_id)->setInc('money', $money);
            // 写入日志money_log
            $money_log = [];
            $money_log['user_id'] = $user_id;
            $money_log['money'] = $money;
            $money_log['before'] = $before_money;
            $money_log['after'] = $after_money;
            $money_log['memo'] = '管理员后台充值';
            $money_log['createtime'] = time();
            $money_log['driver_name'] = $driver_name;
            $money_log['mobile'] = $mobile;

            Db::name('user_money_log')->insert($money_log);
        }
        
        if ($res) {
            $this->success('充值成功');
        } else {
            $this->error('充值失败');
        }
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

    

    public function getList()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);

            // list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            // 
            // 定义默认的$sort $order $limit
            $sort = 'id';
            $order = 'desc';
            $limit = 10;
            // 从post请求单独取search参数
            $where2 = [];
            $search = $this->request->request('username');
            $no_page = $this->request->request('no_page');
            // 如果no_page, 就不分页
            if ($no_page) {
                $limit = 1000000;
            }
            $where2['driver_verified.status'] = '1'; // 已认证
            // 在线
            // 还需要从driver_status表中过滤
            $list = $this->model
                ->with(['user'])
                // 从user表中取出username和mobile 匹配$search
                ->where('user.username|user.mobile', 'like', '%' . $search . '%')
                ->where($where2)
                ->order($sort, $order)
                ->paginate($limit);
            // 在日志中打印
            foreach ($list as $row) {
                $row->getRelation('user')->visible(['username', 'mobile', 'longitude', 'latitude', 'nickname']);
            }
            // 把user内的数据取出来, 放到list里面
            foreach ($list as $key => $value) {
                $list[$key]['username'] = $value['user']['username'];
                $list[$key]['mobile'] = $value['user']['mobile'];
                $list[$key]['nickname'] = $value['user']['nickname'];
                // 把id 换成user_id
                $list[$key]['id'] = $list[$key]['user_id'];
            }
            // 再通过user_id去driver_status表中取出driver_status, driver_create_status
            foreach ($list as $key => $value) {
                $driver_status = Db::name('driver_status')->where('user_id', $value['user_id'])->find();
                // 如果在线
                if ($driver_status ) {
                  $list[$key]['driver_status'] = $driver_status['status'];
                  // 蓝色空闲0
                  // 黄色创单1
                  // 红色有客2
                  $list[$key]['driver_create_status'] = $driver_status['create_status'];
                }
            }

            $result = ["total" => $list->total(), "list" => $list->items()];

            return json($result);
    }

    // 写一个方法更新user表中的latitude longitude
    public function updateLocation()
    {
      $user_id = $this->request->request('user_id');
      $latitude = $this->request->request('latitude');
      $longitude = $this->request->request('longitude');
      Log::write($longitude);
      if ($user_id) {
        $res = Db::name('user')->where('id', $user_id)->update(['latitude' => $latitude, 'longitude' => $longitude]);
        if ($res) {
          $this->success('更新成功');
        } else {
          $this->error('更新失败');
        }
      }
    }
}

