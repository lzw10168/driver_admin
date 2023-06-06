<?php

namespace addons\ddrive\controller;

use app\common\controller\Api;

/**
 * 配置管理
 *
 * @icon fa fa-circle-o
 */
class Config extends Api
{

    // 无需登录的接口,*表示全部
    protected $noNeedLogin = '*';
    // 无需鉴权的接口,*表示全部
    protected $noNeedRight = '*';

    public function index()
    {
        $config = get_addon_config('ddrive');
        // 去掉敏感信息
        unset($config['wx_appid']);
        unset($config['wx_secret']);
        unset($config['__tips__']);
        $this->success("", $config);
    }
}
