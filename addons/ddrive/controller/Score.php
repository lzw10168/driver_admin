<?php

namespace addons\ddrive\controller;

use app\common\controller\Api;
use think\addons\Controller;
use think\Db;

/**
 * 积分接口
 */
class Score extends Api
{
    protected $noNeedLogin = [];
    protected $noNeedRight = ['*'];

    /**
     * 获取积分日志
     *
     * @return void
     */
    public function index()
    {
        $pageSize = $this->request->param('pageSize', 10);
        $list     = Db::name('user_score_log')->where('user_id', $this->auth->id)->order('id desc')->paginate($pageSize)->each(function ($item) {
            $item['createdate'] = date('Y-m-d H:i:s', $item['createtime']);
            return $item;
        });
        $this->success("", $list);
    }
}
