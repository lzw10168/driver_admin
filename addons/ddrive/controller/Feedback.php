<?php

namespace addons\ddrive\controller;

use app\admin\model\Feedback as Model;
use app\common\controller\Api;

/**
 * 话题接口
 */
class Feedback extends Api
{

    protected $noNeedLogin = [];
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \addons\ddrive\model\Feedback;
    }

    /**
     * 创建话题
     *
     * @return void
     */
    public function add()
    {
        $data = [
            'type'    => $this->request->param('type'),
            'content' => $this->request->param('content'),
            'user_id' => $this->auth->id,
            'contact' => $this->request->param('contact'),
        ];
        if (!$data['type']) {
            $this->error('请填写问题分类');
        }
        if (!$data['content']) {
            $this->error('请填写问题内容');
        }
        $res = $this->model->data($data)->save();
        if ($res) {
            $this->success('提交成功');
        } else {
            $this->error('提交失败');
        }
    }
}
