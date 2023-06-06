<?php

namespace app\admin\controller;

use addons\epay\library\Service;
use app\common\controller\Backend;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;
use Yansongda\Pay\Pay;

/**
 * 退款申请管理
 *
 * @icon fa fa-circle-o
 */
class DdriveRefund extends Backend
{
    
    /**
     * DdriveRefund模型对象
     * @var \app\admin\model\DdriveRefund
     */
    protected $model = null;
    protected $noNeedLogin = ['notifyx'];
    protected $noNeedRight = '*';
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\DdriveRefund;
        $this->view->assign("payTypeList", $this->model->getPayTypeList());
        $this->view->assign("checkStatusList", $this->model->getCheckStatusList());
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

            $list = $this->model
                ->with(['user'])
                ->where($where)
                ->order($sort, $order)
                ->paginate($limit);

            foreach ($list as $row) {

                $row->getRelation('user')->visible(['username']);
            }

            $result = array("total" => $list->total(), "rows" => $list->items());

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
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                        $row->validateFailException(true)->validate($validate);
                    }
                    if($params['check_status'] ==1){
                        switch ($params['pay_type']) {
                            case '1':
                                //余额退款
                                $this->refund_update(2,$params['apply_money'],$params['number'],$params['number'],1);
                                break;
                            case '2':
                                //微信退款
                                $this->Refund($params['order_number'],$params['number'],$params['apply_money'],$params['apply_money'],$params['pay_type']);
                                break;
                            case '3':
                                //支付宝退款
                                $this->Refund($params['order_number'],$params['number'],$params['apply_money'],$params['apply_money'],$params['pay_type']);
                                break;
                            default:
                        }
                    }
                    if($params['check_status'] == -1){
                        Db::name('city_order') ->where('number',$params['number'])->update(['status'=>5,'updatetime'=>time()]);
                    }
                    if($params['check_status'] ==1 && $params['pay_type']==3){
                        unset($params['check_status']);
                    }
                    $result = $row->allowField(true)->save($params);
                    Db::commit();
                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (PDOException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (\Exception $e) {
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
     * 退款
     * @param $orderId int 对应lj_pay表id
     * @param $refundOrderNo int 对应退款的订单id
     * @param $totalFee
     * @param $refundFee
     * @return array|bool
     * @throws Exception
     */
    public function Refund($orderId, $refundOrderNo, $totalFee = '0.01', $refundFee,$pay_type = '2')
    {
        $form = 'user_wechat';
        //创建支付对象
        if($pay_type == 3){
            $form = 'alipay';
            $params = [
                'refund_amount' => $refundFee,
                'out_trade_no' => $orderId,
                'out_refund_no' => $refundOrderNo,
            ];
        }else{
            $params = [
                'type' => 'app',
                'total_fee' => $totalFee * 100,
                'refund_fee' => $refundFee * 100,
                'out_trade_no' => $orderId,
                'out_refund_no' => $refundOrderNo,
                'notify_url' => $this->request->domain() . '/eadmin.php/ddrive_refund/notifyx/paytype/wechat'
            ];
        }
        $config = Service::getConfig($form);
        try {
            if($pay_type == 2){
                $pay = Pay::wechat($config);
                $res = $pay->refund($params);
            }else{
                $pay = Pay::alipay($config);
                $res = $pay->refund($params);
            }

        } catch (\Exception $exception) {
            exception($exception->getMessage());
        }
        $data = json_decode($res,true);
        if (is_array($data)) {
            if($pay_type==2){
                if ($data['return_code'] == 'SUCCESS') {
                    return true;
                } else {
                    exception($data['return_msg']);
                }
            }else{
                if ($data['code'] == '10000') {
                    $this->refund_update(2,$data['refund_fee'],$data['trade_no'],$data['trade_no'],3);
                } else {
                    exception($data['msg']);
                }
            }

        } else {
            exception('发起退款失败');
        }
        return false;
    }

    /**
     * 微信退款异步回调
     *
     * @return void
     */
    public function notifyx()
    {
        $xml = file_get_contents("php://input");
        $notifyx = $this->xml_to_array($xml);
        $data = $this->decode($notifyx['req_info']);
        $notifyx_data = $this->xml_to_array($data);
        if($notifyx_data['refund_status'] == 'SUCCESS'){
            $this->refund_update(2,$notifyx_data['total_fee']/100,$notifyx_data['refund_id'],$notifyx_data['out_refund_no'],2);
            echo 'SUCCESS';
        }else{
            echo 'FAIL';
        }

    }

    /**
     * 格式数据
     *
     * @return void
     */
    private function xml_to_array($xml)
    {
        if (!$xml) {
            return false;
        }
        libxml_disable_entity_loader(true);
        $data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $data;
    }

    /**
     * 退款解密
     *
     * @return void
     */
    private  function decode($xml){
        $key = Service::getConfig('user_wechat')['key'];
        $decrypt = base64_decode($xml, true);
        $data = openssl_decrypt($decrypt , 'aes-256-ecb', md5($key), OPENSSL_RAW_DATA);
        return $data;
    }
    /**
     * 退款数据更改
     *
     * @return void
     */
    private function refund_update($check_status,$money,$refund_number = '',$out_refund_no = '',$pay_type){
        try {
            $ddrive_refund = Db::name('ddrive_refund')->where('number',$out_refund_no)->find();
            $updata = [];
            $updata['check_status'] = $check_status;
            $updata['money'] = $money;
            $updata['refund_number'] = $refund_number;
            $updata['success_time'] = time();
            $updata['admin_id']  = 1;
            Db::name('ddrive_refund')->where('number',$out_refund_no)->update($updata);
            Db::name('ddrive_hy_order') ->where('pay_number',$out_refund_no)->update(['status'=>-2,'updatetime'=>time()]);
            if($pay_type == 1){
                Db::name('user')->where('id',$ddrive_refund['user_id'])->setInc('money',$money);
            }
        } catch (\Exception $exception) {
            trace($exception->getMessage(), 'pay');
        }
    }

}
