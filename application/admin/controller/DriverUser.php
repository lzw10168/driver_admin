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
            $where = (array)$where;
            $where2 = [];
            $where2['driver_verified.status'] = '1';
            $list = $this->model
                ->with(['user'])
                // 从user表中取出username和mobile 匹配$search
                ->where('user.username|user.mobile', 'like', '%' . $search . '%')
                ->where($where2)
                ->order($sort, $order)
                ->paginate($limit);
            // 在日志中打印
            foreach ($list as $row) {
                $row->getRelation('user')->visible(['username', 'mobile', 'longitude', 'latitude']);
            }
            $result = ["total" => $list->total(), "rows" => $list->items()];

            return json($result);
        }
        return $this->view->fetch();
    }


    function distance($lat1, $lon1, $lat2, $lon2, $unit = 'km', $decimal = 2) {
      $theta = $lon1 - $lon2;
      $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
      $dist = acos($dist);
      $dist = rad2deg($dist);
      $miles = $dist * 60 * 1.1515;
      $unit = strtolower($unit);
      if ($unit == "km") {
          return round($miles * 1.609344, $decimal);
      } else if ($unit == "nautical miles") {
          return round($miles * 0.8684, $decimal);
      } else {
          return round($miles, $decimal);
      }
  }
    
    // 获取最近的20个司机
    public function getNearbyDriver()
    {

      $longitude = $this->request->request('longitude');
      $latitude = $this->request->request('latitude');
      $where = [];
      $where['driver_verified.status'] = '1';
      $list = $this->model
          ->with(['user'])
          ->where($where)
          ->select();
      $result = [];
      foreach ($list as $row) {
          $row->getRelation('user')->visible(['username', 'mobile', 'longitude', 'latitude']);
          $distance = $this->distance($latitude, $longitude, $row->user->latitude, $row->user->longitude);
          $row->distance = $distance;
          $result[] = $row;
      }
      //  按照距离排序
      // 自定义排序算法


// 使用usort()函数进行排序
      usort($result, function ($a, $b) {
        return $a['distance'] - $b['distance'];
      });
      $result = array_slice($result, 0, 20);
      return json($result);
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
        $params = $this->request->request('params');
        parse_str($params, $paramsArr);
        Log::write($paramsArr);
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);

            // list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            // 
            // 定义默认的$sort $order $limit
            $sort = 'id';
            $order = 'desc';
            $limit = 10;
            // 从post请求单独取search参数
            

            $where2['driver_verified.status'] = '1';
            $list = $this->model
                ->with(['user'])
                // 从user表中取出username和mobile 匹配$search
                // ->where('user.username|user.mobile', 'like', '%' . $search . '%')
                ->where($where2)
                ->order($sort, $order)
                ->paginate($limit);
            // 在日志中打印
            foreach ($list as $row) {
                $row->getRelation('user')->visible(['username', 'mobile', 'longitude', 'latitude']);
            }
            // 把user内的数据取出来, 放到list里面
            foreach ($list as $key => $value) {
                $list[$key]['username'] = $value['user']['username'];
                $list[$key]['mobile'] = $value['user']['mobile'];
                // 把id 换成user_id
                $list[$key]['id'] = $list[$key]['user_id'];
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

