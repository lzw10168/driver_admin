<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/8/10
 * Time: 17:18
 */

namespace addons\ddrive\controller;

use app\common\controller\Api;
use app\common\library\Auth;
use fast\Random;
use think\Db;

/**
 * 三方登录
 */
class WxLogin extends Api
{
    protected $noNeedLogin = ['wxlogin', 'getOpenid'];
    protected $noNeedRight = '*';
    protected $_token = '';
    //Token默认有效时长
    protected $keeptime = 2592000;

    /**
     * 微信登录
     * @ApiMethod   (POST)
     * @param string $openid 用户标识
     * @param string $wx_name 微信名称
     * @param string $unionid unionid
     */
    public function wxlogin()
    {
        $openid  = $this->request->param('openid');
        $wx_name = $this->request->param('wx_name');
        $avatar = $this->request->param('avatar');
        $unionid = $this->request->param('unionId');
        $mini    = $this->request->param('mini');
        if (empty($openid)) {
            $this->error('参数error1001');
        }
        if(empty($unionid)){
            $this->error('参数error1002');
        }
        $ddrive_user = Db::name('ddrive_user_token')->where('unionId', $unionid)->find();

        if (!$ddrive_user) {

            $ret = $this->auth->register($wx_name, '', '', '', ['avatar' => $avatar]);
        } else {
            $updata = [];
            if ($mini) {
                $updata['mini_openid'] = $openid;
            } else {
                $updata['wx_openid'] = $openid;
            }
            $updata['wx_name'] = $wx_name;
            Db::name('ddrive_user_token')->where('user_id', $ddrive_user['user_id'])->update($updata);
            $ret = $this->auth->direct($ddrive_user['user_id']);

        }
//        var_dump($ddrive_user);
//        exit();
        if ($ret) {
            $data          = ['userinfo' => $this->auth->getUserinfo()];
            $ddrive_user   = Db::name('ddrive_user_token')->where('user_id', $data['userinfo']['id'])->find();
            $user_verified = Db::name('user_verified')->where('user_id', $data['userinfo']['id'])->find();
            if (!$ddrive_user) {
                Db::name('ddrive_user_token')->insert([
                    'mini_openid' => $mini ? $openid : '',
                    'wx_openid'   => $mini ? '' : $openid,
                    'wx_name'     => $wx_name,
                    'user_id'     => $data['userinfo']['id'],
                    'unionId'     => $unionid,
                ]);
            }
            if (!$user_verified) {
                Db::name('user_verified')->insert([
                    'user_id'         => $data['userinfo']['id'],
                    'real_verified'   => 0,
                    'driver_verified' => 0,
                    'card_verified'   => 0,
                    'createtime'      => time(),
                    'updatetime'      => time(),
                ]);
            }
            $user                                  = Db::name('user')->alias('U')
                ->join('ddrive_user_token DU', 'DU.user_id = U.id', 'LEFT')
                ->join('real_verified RV', 'RV.user_id = U.id', 'LEFT')
                ->join('card_verified CV', 'CV.user_id = U.id', 'LEFT')
                ->join('driver_status DS', 'DS.user_id = U.id', 'LEFT')
                ->field('U.mobile,DU.wx_openid,DU.mini_openid,U.avatar,DU.wx_name,RV.truename,RV.idcard,U.emergency_contact,U.contact_tel,CV.card_brand,CV.card_type,DS.status as driver_status')
                ->where('U.id', $data['userinfo']['id'])
                ->find();
            $data['userinfo']['avatar']            = $user['avatar'] ? cdnurl($user['avatar']) : '';
            $data['userinfo']['wx_name']           = $user['wx_name'] ? $user['wx_name'] : '';
            $data['userinfo']['openid']            = $user['wx_openid'] ? $user['wx_openid'] : '';
            $data['userinfo']['mini_openid']            = $user['mini_openid'] ? $user['mini_openid'] : '';
            $data['userinfo']['truename']          = $user['truename'] ? $user['truename'] : '';
            $data['userinfo']['idcard']            = $user['idcard'] ? $user['idcard'] : '';
            $data['userinfo']['emergency_contact'] = $user['emergency_contact'] ? $user['emergency_contact'] : '';
            $data['userinfo']['contact_tel']       = $user['contact_tel'] ? $user['contact_tel'] : '';
            $data['userinfo']['card_brand']        = $user['card_brand'] ? $user['card_brand'] : '';
            $data['userinfo']['card_type']         = $user['card_type'] ? $user['card_type'] : '';
            $verified                              = Db::name('user_verified')->where('user_id', $data['userinfo']['id'])->order('id desc')->find();
            $data['userinfo']['real_verified']     = $verified['real_verified'];
            $data['userinfo']['driver_verified']   = $verified['driver_verified'];
            $data['userinfo']['card_verified']     = $verified['card_verified'];
            $data['userinfo']['driver_status']     = $user['driver_status']===0 ? $user['driver_status'] : 1 ;
            $data['userinfo']['coupon_count']         = Db::name('user_coupon')->where('user_id', $data['userinfo']['id'])->where('coupon_status',0)->count();
            $this->success('成功', $data);
        } else {
            $this->error('失败');
        }
    }

    /**
     * 解绑
     */
    public function untie()
    {
        $res = Db::name('ddrive_user_token')->where('user_id', $this->auth->id)->setField('wx_openid', '');
        if ($res) {
            $this->success('成功');
        }
    }

    /**
     * 获取微信openid
     *
     * @return void
     */
    public function getOpenid()
    {
        $wx_info = $this->request->param();
        $config  = get_addon_config('ddrive');
        $appid   = $config['wx_appid'];
        $secret  = $config['wx_secret'];
        $api     = "https://api.weixin.qq.com/sns/jscode2session?appid={$appid}&secret={$secret}&js_code={$wx_info['code']}&grant_type=authorization_code";
        $res     = \fast\Http::get($api);
        $res     = json_decode($res, true);
        if ($res['session_key']) {
            $encryptedData = urldecode($wx_info['encryptedData']);
            $iv            = $this->define_str_replace($wx_info['iv']);
            $errCode       = $this->decryptData($appid, $res['session_key'], $encryptedData, $iv);//获取unioID
            $data          = [
                'unionId' => $errCode['unionId'],
                'openid'  => $res['openid'],
            ];
        }
        if (isset($res['errmsg'])) {
            $this->error($res['errmsg']);
        } else {
            $this->success("", $data);
        }
    }

    //获取unionID
    private function decryptData($appid, $sessionKey, $encryptedData, $iv)
    {
        $IllegalAesKey     = -41001;
        $IllegalIv         = -41002;
        $IllegalBuffer     = -41003;
        $DecodeBase64Error = -41004;

        if (strlen($sessionKey) != 24) {
            return $IllegalAesKey;
        }
        // $str = base64_decode(str_replace(" ","+",$_GET['str']));
        $aesKey = base64_decode(str_replace(" ", "+", $sessionKey));
        // var_dump($aesKey);exit;
        if (strlen($iv) != 24) {
            return $IllegalIv;
        }
        $aesIV     = base64_decode(str_replace(" ", "+", $iv));
        $aesCipher = base64_decode(str_replace(" ", "+", $encryptedData));
        $result    = openssl_decrypt($aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);
        $dataObj   = json_decode($result);
        // var_dump($dataObj);exit;
        if ($dataObj == NULL) {
            return $IllegalBuffer;
        }
        if ($dataObj->watermark->appid != $appid) {
            return $DecodeBase64Error;
        }
        $data = json_decode($result, true);

        return $data;
    }

    public function define_str_replace($data)
    {
        return str_replace(' ', '+', $data);
    }

}