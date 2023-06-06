<?php

namespace addons\ddrive\controller;

use app\common\controller\Api;

/**
 * 申请代驾接口
 */
class Apply extends Api
{

    protected $noNeedLogin = [];
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \addons\ddrive\model\Apply;
    }

    /**
     * 代驾申请
     *
     * @return void
     */
    public function add()
    {
        $data = [
            'name'            => $this->request->post('name'),
            'image'           => $this->request->post('image'),
            'mobile'          => $this->request->post('mobile'),
            'driving_age'     => $this->request->post('driving_age'),
            'card_id'         => $this->request->post('card_id'),
            'card_image'      => $this->request->post('card_image'),
            'card_back_image' => $this->request->post('card_back_image'),
            'driver_image'    => $this->request->post('driver_image'),
            'user_id'         => $this->auth->id,
        ];
        if ($this->request->post('id')) {
            $data['status'] = 0;
            $res            = $this->model->where('id', $this->request->post('id'))->update($data);
        } else {
            $res = $this->model->data($data)->save();
        }
        if ($res) {
            $this->success('申请成功，请等待审核');
        } else {
            if ($this->request->post('id')) {
                $this->error('申请失败，请检查信息是否修改');
            } else {
                $this->error('申请失败，请检查信息是否填写正确');
            }

        }
    }

    /**
     * 获取详情
     *
     * @return void
     */
    public function info()
    {
        $info = $this->model->where('user_id', $this->auth->id)->find();
        $this->success('', $info);
    }

}
