<?php

namespace addons\cwmap\controller;

use think\addons\Controller;

class Index extends Controller {

    protected $model = null;
    protected $searchFields = 'id,locationname,detailaddress,phone,email,province';

    public function _initialize() {
        $this->model = new \app\admin\model\Maplocation;
        parent::_initialize();
    }

    public function index() {
        $data = $this->model->select();
        $this->assign('data', json_encode($data));

        $config = get_addon_config('cwmap');
        $this->assign('config', $config);
        return view();
    }

    public function search() {
        if (request()->isAjax()) {
            $search = request()->get('keyword');
            $searcharr = is_array($this->searchFields) ? $this->searchFields : explode(',', $this->searchFields);

            $result = $this->model->where(implode("|", $searcharr), "LIKE", "%{$search}%")->select();
            return json_encode($result);
        }
    }

}
