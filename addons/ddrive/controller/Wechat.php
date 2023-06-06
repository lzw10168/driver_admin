<?php

namespace addons\ddrive\controller;

use app\common\controller\Api;
use think\Db;

/**
 * 微信接口
 */
class Wechat extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 获取微信openid
     *
     * @return void
     */
    public function getOpenid()
    {
        $code   = $this->request->param('code');
        $config = get_addon_config('ddrive');
        $appid  = $config['wx_appid'];
        $secret = $config['wx_secret'];
        $api    = "https://api.weixin.qq.com/sns/jscode2session?appid={$appid}&secret={$secret}&js_code={$code}&grant_type=authorization_code";
        $res    = \fast\Http::get($api);
        $res    = json_decode($res, true);
        if (isset($res['errmsg'])) {
            $this->error($res['errmsg']);
        } else {
            $this->success("", $res);
        }
    }

    /**
     * 同步用户信息到本地
     *
     * @return void
     */
    public function syncUser()
    {
        $openid       = $this->request->param('openid');
        $nick_name    = $this->request->param('nick_name');
        $avatar_image = $this->request->param('avatar_image');

        $user = Db::name('user')->where('openid', $openid)->find();
        if ($user) {
            $this->success("用户已存在");
        }
        $user_id = Db::name('user')->insertGetId([
            'openid'       => $openid,
            'nick_name'    => $nick_name,
            'avatar_image' => $avatar_image,
            'createtime'   => time(),
        ]);

        if ($user_id) {
            $this->success("同步成功");
        } else {
            $this->error('同步失败');
        }
    }
}
