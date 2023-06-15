<?php

namespace addons\ddrive\controller;

use addons\ddrive\library\Common;
use addons\ddrive\model\Apply;
use addons\ddrive\model\CardVerified;
use addons\ddrive\model\DriverVerified;
use addons\ddrive\model\Hyaddress;
use addons\ddrive\model\RealVerified;
use addons\epay\library\Service;
use app\admin\model\Complaint;
use app\admin\model\ComplaintCategory;
use app\admin\model\Coupon;
use app\admin\model\Details;
use app\common\controller\Api;
use app\common\library\Ems;
use app\common\library\Sms;
use addons\ddrive\model\UserCoupon;
use fast\Random;
use think\Db;
use think\Exception;
use think\Lang;
use think\Log;
use think\Validate;

/**
 * 会员接口
 */
class User extends Api
{
    protected $noNeedLogin = ['login', 'mobilelogin', 'register', 'resetpwd', 'changeemail', 'changemobile', 'third'];
    protected $noNeedRight = '*';
    protected $_token = '';
    //Token默认有效时长
    protected $keeptime = 2592000;

    public function _initialize()
    {
        parent::_initialize();
        $this->driverVerifiedModel = new \app\admin\model\DriverVerified();
        Lang::load(APP_PATH . 'api/lang/zh-cn/user.php');
    }

    /**
     * 用户资料
     */
    public function index()
    {
        $allowFields       = ['id', 'username', 'nickname', 'mobile', 'avatar', 'score', 'money', 'group_id', 'bio'];
        $user              = $this->auth->getUser()->toArray();
        $userinfo          = array_intersect_key($user, array_flip($allowFields));
        $userinfo['token'] = $this->auth->getToken();
        // 查询用户司机身份
        $userinfo['dirver'] = Apply::where('user_id', $this->auth->id)->where('status', 1)->count();
        $this->success('', $userinfo);
    }

    /**
     * 会员登录
     *
     * @param string $account 账号
     * @param string $password 密码
     */
    public function login()
    {
        $mobile   = $this->request->param('mobile');
        $password = $this->request->param('password');
        $app_type = $this->request->param('app_type');
        $openid   = $this->request->param('openid');
        if (!$mobile || !$password) {
            $this->error(__('Invalid parameters'));
        }
        if ($app_type != 1 && !$openid) {
            $this->error(__('Invalid parameters'));
        }
        $ret = $this->auth->login($mobile, $password);
        if ($ret) {
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $user = Db::name('user')->alias('U')
                ->join('ddrive_user_token DU', 'DU.user_id = U.id', 'LEFT')
                ->join('real_verified RV', 'RV.user_id = U.id', 'LEFT')
                ->join('card_verified CV', 'CV.user_id = U.id', 'LEFT')
                ->join('driver_status DS', 'DS.user_id = U.id', 'LEFT')
                ->field('U.mobile,DU.wx_openid,DU.mini_openid,U.avatar,DU.wx_name,RV.truename,RV.idcard,U.emergency_contact,U.contact_tel,CV.card_brand,CV.card_type,DS.status as driver_status')
                ->where('U.id', $data['userinfo']['id'])
                ->find();
            //2021-2-1 by shen 无法登录
            //  $user = \addons\ddrive\extend\Common::object_array($user);
            $data['userinfo']['avatar'] = $user['avatar'] ? cdnurl($user['avatar']) : '';
            // 更新openid
//            if ($app_type != 1) {
//                Db::name('ddrive_user_token')->where('id', $this->auth->id)->update(['openid' => $openid]);
//            }

            if ($app_type == 1) {
                $verified                              = Db::name('user_verified')->where('user_id', $data['userinfo']['id'])->order('id desc')->find();
                $data['userinfo']['real_verified']     = $verified['real_verified'];
                $data['userinfo']['driver_verified']   = $verified['driver_verified'];
                $data['userinfo']['card_verified']     = $verified['card_verified'];
                $data['userinfo']['wx_name']           = $user['wx_name'] ? $user['wx_name'] : '';
                $data['userinfo']['openid']            = $user['wx_openid'] ? $user['wx_openid'] : '';
                $data['userinfo']['mini_openid']       = $user['mini_openid'] ? $user['mini_openid'] : '';
                $data['userinfo']['truename']          = $user['truename'] ? $user['truename'] : '';
                $data['userinfo']['idcard']            = $user['idcard'] ? $user['idcard'] : '';
                $data['userinfo']['emergency_contact'] = $user['emergency_contact'] ? $user['emergency_contact'] : '';
                $data['userinfo']['contact_tel']       = $user['contact_tel'] ? $user['contact_tel'] : '';
                $data['userinfo']['card_brand']        = $user['card_brand'] ? $user['card_brand'] : '';
                $data['userinfo']['card_type']         = $user['card_type'] ? $user['card_type'] : '';
                $data['userinfo']['driver_status']     = $user['driver_status'] === 0 ? $user['driver_status'] : 1;
                $data['userinfo']['coupon_count']      = Db::name('user_coupon')->where('user_id', $data['userinfo']['id'])->where('coupon_status', 0)->count();

            }

            $this->success(__('Logged in successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 注册会员
     *
     * @param string $username 用户名
     * @param string $password 密码
     * @param string $email 邮箱
     * @param string $mobile 手机号
     * @param string $captcha 验证码
     */
    public function register()
    {
        $mobile   = $this->request->request('mobile');
        $username = $this->request->request('username', $mobile);
        $password = $this->request->request('password');
        // 官方数据库中email字段为非空，这里只能给个默认值做兼容
        $email   = $this->request->request('email', $mobile . '@test.com');
        $captcha = $this->request->request('captcha');

        $nickname = $this->request->request('nickname');
        $avatar   = $this->request->request('avatar');
        $gender   = $this->request->request('gender');
        $openid   = $this->request->request('openid');

        if (!$username || !$password) {
            $this->error(__('Invalid parameters'));
        }
        if ($email && !Validate::is($email, "email")) {
            $this->error(__('Email is incorrect'));
        }
        if ($mobile && !Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        $ret = Sms::check($mobile, $captcha, 'register');
        if (!$ret) {
            //$this->error(__('Captcha is incorrect'));
        }
        $ret = $this->auth->register($username, $password, $email, $mobile, [
            'nickname' => $nickname,
            'avatar'   => $avatar,
            'gender'   => $gender,
            'group_id' => 1,
        ]);
        if ($ret) {
            $data = ['userinfo' => $this->auth->getUserinfo()];
            Db::name('ddrive_user_token')->insert(['openid' => $openid, 'user_id' => $this->auth->id]);
            $this->success(__('Sign up successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 注销登录
     */
    public function logout()
    {
        $this->auth->logout();
        $this->success(__('Logout successful'));
    }

    /**
     * 修改会员个人信息
     *
     * @param string $avatar 头像地址
     * @param string $username 用户名
     * @param string $nickname 昵称
     * @param string $bio 个人简介
     */
    public function profile()
    {
        $user     = $this->auth->getUser();
        $nickname = $this->request->request('nickname');
        $bio      = $this->request->request('bio');
        $avatar   = $this->request->request('avatar', '', 'trim,strip_tags,htmlspecialchars');
        // 经纬度
        $longitude = $this->request->request('longitude');
        $latitude  = $this->request->request('latitude');

        if ($longitude && $latitude) {
            $user->longitude = $longitude;
            $user->latitude  = $latitude;
        }
        if ($nickname) {
            $user->nickname = $nickname;
        }
        if ($bio) {
            $user->bio = $bio;
        }
        if ($avatar) {
            $user->avatar = $avatar;
        }

        $user->save();
        $this->success('更新成功');
    }

    /**
     * 重置密码
     *
     * @param string $mobile 手机号
     * @param string $newpassword 新密码
     * @param string $captcha 验证码
     */
    public function resetpwd()
    {
        $mobile      = $this->request->param("mobile");
        $newpassword = $this->request->param("newpassword");
        $captcha     = $this->request->param("captcha");
        if (!$newpassword || !$captcha) {
            $this->error(__('Invalid parameters'));
        }

        if (!Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        $user = \app\common\model\User::getByMobile($mobile);
        if (!$user) {
            $this->error(__('User not found'));
        }
        $ret = Sms::check($mobile, $captcha, 'resetpwd');
        if (!$ret) {
            $this->error(__('Captcha is incorrect'));
        }
        Sms::flush($mobile, 'resetpwd');

        //模拟一次登录
        $this->auth->direct($user->id);
        $ret = $this->auth->changepwd($newpassword, '', true);
        if ($ret) {
            $this->success(__('Reset password successful'));
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 手机验证码登录
     *
     * @param string $mobile 手机号
     * @param string $captcha 验证码
     */
    public function mobilelogin()
    {
        $mobile   = $this->request->param('mobile');
        $captcha  = $this->request->param('captcha');
        $event    = $this->request->param('event');
        $password = $this->request->param('password');
        $event    = $event ? $event : 'mobilelogin';
        if (!$mobile || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        if (!Sms::check($mobile, $captcha, $event)) {
            $this->error(__('Captcha is incorrect'));
        }
        $user = \app\common\model\User::getByMobile($mobile);
        if ($user) {
            if ($user->status != 'normal') {
                $this->error(__('Account is locked'));
            }
            //如果已经有账号则直接登录
            $ret = $this->auth->direct($user->id);
        } else {
            $ret = $this->auth->register($mobile, $password, '', $mobile, []);
        }
        if ($ret) {
            Sms::flush($mobile, 'mobilelogin');
            $data   = ['userinfo' => $this->auth->getUserinfo()];
            $coupon = Db::name('coupon')->where('coupon_name', 1)->select();
            if ($coupon && $event == 'register') {
                foreach ($coupon as $k => $v) {
                    $user_coupon[] = [
                        'user_id'       => $data['userinfo']['id'],
                        'coupon_id'     => $v['id'],
                        'expiration'    => $v['expiration'] * 86400 + time(),
                        'coupon_status' => 0,
                        'createtime'    => time(),
                    ];
                }
                (new UserCoupon())->saveAll($user_coupon);
            }
            $user_verified = Db::name('user_verified')->where('user_id', $data['userinfo']['id'])->find();
            $ddrive_user   = Db::name('ddrive_user_token')->where('user_id', $data['userinfo']['id'])->find();
            if (!$ddrive_user) {
                Db::name('ddrive_user_token')->insert([
                    'wx_openid'   => '',
                    'mini_openid' => '',
                    'wx_name'     => '',
                    'user_id'     => $data['userinfo']['id'],
                    'unionId'     => '',
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
            $data['userinfo']['mini_openid']       = $user['mini_openid'] ? $user['mini_openid'] : '';
            $data['userinfo']['emergency_contact'] = $user['emergency_contact'] ? $user['emergency_contact'] : '';
            $data['userinfo']['contact_tel']       = $user['contact_tel'] ? $user['contact_tel'] : '';
            $verified                              = Db::name('user_verified')->where('user_id', $data['userinfo']['id'])->order('id desc')->find();
            $data['userinfo']['real_verified']     = $verified['real_verified'];
            $data['userinfo']['driver_verified']   = $verified['driver_verified'];
            $data['userinfo']['card_verified']     = $verified['card_verified'];
            $data['userinfo']['truename']          = $user['truename'] ? $user['truename'] : '';
            $data['userinfo']['idcard']            = $user['idcard'] ? $user['idcard'] : '';
            $data['userinfo']['card_brand']        = $user['card_brand'] ? $user['card_brand'] : '';
            $data['userinfo']['card_type']         = $user['card_type'] ? $user['card_type'] : '';
            $data['userinfo']['driver_status']     = $user['driver_status'] === 0 ? $user['driver_status'] : 1;
            $data['userinfo']['coupon_count']      = Db::name('user_coupon')->where('user_id', $data['userinfo']['id'])->where('coupon_status', 0)->count();
            $this->success(__('Logged in successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 实名认证
     *
     * @param string $truename 真实姓名
     * @param string $idcard 身份证号
     * @param string $front_card_image 身份证正面
     * @param string $back_card_image 身份证反面
     */
    public function verified()
    {
        $type               = $this->request->param('type');
        $truename           = $this->request->param('truename');
        $idcard             = $this->request->param('idcard');
        $front_card_image   = $this->request->param('front_card_image');
        $back_card_image    = $this->request->param('back_card_image');
        $sign_areas         = $this->request->param('sign_areas');
        $areas              = $this->request->param('areas');
        $driver_license     = $this->request->param('driver_license');
        $driver_front_image = $this->request->param('driver_front_image');
        $driver_back_image  = $this->request->param('driver_back_image');
        $card_brand         = $this->request->param('card_brand');
        $card_type          = $this->request->param('card_type');
        $number_plate       = $this->request->param('number_plate');
        $card_front_image   = $this->request->param('card_front_image');
        $card_back_image    = $this->request->param('card_back_image');
        $driver_age         = $this->request->param('driver_age');
        $user_verified      = Db::name('user_verified')->where('user_id', $this->auth->id)->find();
        Db::startTrans();
        try {
            if ($type == 1) {
                if (!$truename || !$idcard || !$front_card_image || !$back_card_image) {
                    exception(__('Invalid parameters'));
                }
                $real_verified = Db::name('real_verified')->where('user_id', $this->auth->id)->whereIn('status', [0, 1])->find();
                if ($real_verified) {
                    if ($real_verified['status'] == 0) {
                        exception(__('您当前正在审核中,请耐心等待'));
                    }
                    if ($real_verified['status'] == 1) {
                        exception(__('您已实名认证,无需再审核'));
                    }
                    $data = [
                        'user_id'          => $this->auth->id,
                        'truename'         => $truename,
                        'idcard'           => $idcard,
                        'front_card_image' => $front_card_image,
                        'back_card_image'  => $back_card_image,
                        'status'           => 0,
                        'createtime'       => time(),
                    ];
                    RealVerified::update($data);
                } else {
                    $data = [
                        'user_id'          => $this->auth->id,
                        'truename'         => $truename,
                        'idcard'           => $idcard,
                        'front_card_image' => $front_card_image,
                        'back_card_image'  => $back_card_image,
                        'status'           => 0,
                        'createtime'       => time(),
                    ];
                    RealVerified::create($data);
                }
                Db::name('user_verified')->where('user_id', $this->auth->id)->setField('real_verified', 2);

            } elseif ($type == 2) {
                if ($user_verified['real_verified'] != 1) {
                    exception('请先实名认证');
                }
                if (!$sign_areas || !$areas || !$driver_license || !$driver_front_image || !$driver_back_image) {
                    exception(__('Invalid parameters'));
                }
                $sign_areas = Db::name('areas')->where(['id' => $sign_areas])->find();
                if (empty($sign_areas)) {
                    exception('地址不存在');
                }
                $sign_path = explode(',', $sign_areas['parent_path']);
                $areas     = Db::name('areas')->where(['id' => $areas])->find();
                if (empty($areas)) {
                    exception('地址不存在');
                }
                $parent_path     = explode(',', $areas['parent_path']);
                $driver_verified = Db::name('driver_verified')->where('user_id', $this->auth->id)->whereIn('status', [0, 1])->find();
                if ($driver_verified) {
                    if ($driver_verified['status'] == 0) {
                        exception(__('您当前正在审核中,请耐心等待'));
                    }
                    if ($driver_verified['status'] == 1) {
                        exception(__('您已驾照认证,无需再审核'));
                    }
                    $data = [
                        'user_id'            => $this->auth->id,
                        'province'           => $parent_path['0'],
                        'city'               => $parent_path['1'],
                        'area'               => $parent_path['2'],
                        'sign_province'      => $sign_path['0'],
                        'sign_city'          => $sign_path['1'],
                        'driver_license'     => $driver_license,
                        'driver_front_image' => $driver_front_image,
                        'driver_back_image'  => $driver_back_image,
                    ];
                    DriverVerified::update($data);
                } else {
                    $data = [
                        'user_id'            => $this->auth->id,
                        'province'           => $parent_path['0'],
                        'city'               => $parent_path['1'],
                        'area'               => $parent_path['2'],
                        'sign_province'      => $sign_path['0'],
                        'sign_city'          => $sign_path['1'],
                        'driver_license'     => $driver_license,
                        'driver_front_image' => $driver_front_image,
                        'driver_back_image'  => $driver_back_image,
                    ];
                    DriverVerified::create($data);
                }
                Db::name('user_verified')->where('user_id', $this->auth->id)->setField('driver_verified', 2);
            } else {
                if ($user_verified['driver_verified'] != 1 || $user_verified['real_verified'] != 1) {
                    exception('请先实名认证或者驾照认证');
                }
                if (!$sign_areas || !$areas || !$card_brand || !$number_plate || !$card_front_image || !$card_back_image || !$driver_age) {
                    exception(__('Invalid parameters'));
                }
                $sign_areas = Db::name('areas')->where(['id' => $sign_areas])->find();
                if (empty($sign_areas)) {
                    exception('地址不存在');
                }
                $sign_path = explode(',', $sign_areas['parent_path']);
                $areas     = Db::name('areas')->where(['id' => $areas])->find();
                if (empty($areas)) {
                    exception('地址不存在');
                }
                $parent_path   = explode(',', $areas['parent_path']);
                $card_verified = Db::name('card_verified')->where('user_id', $this->auth->id)->whereIn('status', [0, 1])->find();
                if ($card_verified) {
                    if ($card_verified['status'] == 0) {
                        exception(__('您当前正在审核中,请耐心等待'));
                    }
                    if ($card_verified['status'] == 1) {
                        exception(__('您已车主认证,无需再审核'));
                    }
                    $data = [
                        'user_id'          => $this->auth->id,
                        'province'         => $parent_path['0'],
                        'city'             => $parent_path['1'],
                        'area'             => $parent_path['2'],
                        'sign_province'    => $sign_path['0'],
                        'sign_city'        => $sign_path['1'],
                        'card_brand'       => $card_brand,
                        'card_type'        => $card_type,
                        'number_plate'     => $number_plate,
                        'card_front_image' => $card_front_image,
                        'card_back_image'  => $card_back_image,
                        'driver_age'       => $driver_age,
                    ];
                    CardVerified::update($data);
                } else {
                    $data = [
                        'user_id'          => $this->auth->id,
                        'province'         => $parent_path['0'],
                        'city'             => $parent_path['1'],
                        'area'             => $parent_path['2'],
                        'sign_province'    => $sign_path['0'],
                        'sign_city'        => $sign_path['1'],
                        'card_brand'       => $card_brand,
                        'card_type'        => $card_type,
                        'number_plate'     => $number_plate,
                        'card_front_image' => $card_front_image,
                        'card_back_image'  => $card_back_image,
                        'driver_age'       => $driver_age,
                    ];
                    CardVerified::create($data);
                }
                Db::name('user_verified')->where('user_id', $this->auth->id)->setField('card_verified', 2);
            }
            Db::commit();
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            Db::rollback();
        }
        $this->success('提交成功,请耐心等待');
    }

    /**
     * 修改手机号
     *
     * @param string $mobile 手机号
     * @param string $captcha 验证码
     */
    public function changemobile()
    {
        $user    = $this->auth->getUser();
        $mobile  = $this->request->param('mobile');
        $captcha = $this->request->param('captcha');
        if (!$mobile || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        if (\app\common\model\User::where('mobile', $mobile)->where('id', '<>', $user->id)->find()) {
            $this->error(__('Mobile already exists'));
        }
        $result = Sms::check($mobile, $captcha, 'changemobile');
        if (!$result) {
            $this->error(__('Captcha is incorrect'));
        }
        $verification         = $user->verification;
        $verification->mobile = 1;
        $user->verification   = $verification;
        $user->mobile         = $mobile;
        $user->save();

        Sms::flush($mobile, 'changemobile');
        $this->success('修改手机号成功');
    }

    /**
     * 检测是否可修改手机号/微信
     * @ApiMethod   (POST)
     * @ApiParams (name="type", type="string", required=true, description="1手机号2微信")
     * @param string $live_video 视频
     */
    public function check_account()
    {
        $type = $this->request->param('type');
        $user = Db::name('user')->alias('U')
            ->join('ddrive_user_token DU', 'DU.user_id = U.id', 'LEFT')
            ->field('U.mobile,DU.openid')
            ->where('U.id', $this->auth->id)
            ->find();
        Db::startTrans();
        try {
            if ($type == 1) {
                if (!$user['openid']) {
                    exception('您当前不可修改手机号，请先绑定微信');
                }
            } else {
                if (!$user['mobile']) {
                    exception('您当前不可修改微信号，请先绑定手机号');
                }
            }

            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        $this->success('成功');
    }

    /**
     * 微信登录绑定已有账号
     *
     * @param string $mobile 手机号
     * @param string $captcha 验证码
     */
    public function bindOnAccount()
    {
        $mobile  = $this->request->param('mobile');
        $captcha = $this->request->param('captcha');
        if (!$mobile) {
            $this->error('请填写手机号');
        }
        if (!$captcha) {
            $this->error('请填写验证码');
        }
        $ret = Sms::check($mobile, $captcha, 'mobilelogin');
        if (!$ret) {
            $this->error('验证码错误');
        }
        $user = Db::name('user')->where('mobile', $mobile)->find();
        if ($user) {
            $this->error('该账号已被绑定');
        }
        $nickname = preg_match("/^1[3-9]{1}\d{9}$/", $mobile) ? substr_replace($mobile, '****', 3, 4) : $mobile;
        Db::name('user')->where('id', $this->auth->id)->update(['mobile' => $mobile, 'username' => $mobile, 'nickname' => $nickname]);
        $this->success('绑定成功');
    }

    /**
     * 收益
     * @ApiReturnParams (name="today_income", type="str", required=true, description="今日收益")
     */

    public function userIncome()
    {
        $begin_time           = strtotime(date('Y-m-d', time()));
        $end_time             = $begin_time + 86400;
        $today_income         = (new Details())
            ->where('user_id', $this->auth->id)
            ->where('assets_type', 2)
            ->where('fluctuate_type', 1)
            ->whereBetween('createtime', [$begin_time, $end_time])
            ->sum('amount');
        $income               = (new Details())
            ->where('user_id', $this->auth->id)
            ->where('assets_type', 2)
            ->where('fluctuate_type', 1)
            ->sum('amount');
        $user                 = Db::name('user')->where('id', $this->auth->id)->field('money,platform_service_fee')->find();
        $platform_service_fee = get_addon_config('ddrive')['platform_service_fee'] / 100;
        $this->success('成功', [
            'today_income'         => $today_income,
            'money'                => $user['money'],
            'income'               => $income,
            'platform_service_fee' => $user['platform_service_fee'],
            'withdraw_money'       => round($user['money'] - round($platform_service_fee * $user['money'], 2),2),
        ]);
    }

    /**
     * 账单管理
     * @ApiMethod   (POST)
     * @ApiParams (name="source_type", type="int", required=true, description="来源类型:1=提现,2=接单)
     * @ApiReturnParams (name="user_id", type="str", required=true, description="用户id")
     * @ApiReturnParams (name="task_id", type="str", required=true, description="消费类型:1=健身,2=ktv,3=酒吧,4=保龄球")
     * @ApiReturnParams (name="fluctuate_type", type="str", required=true, description="状态 1=增加，2=减少")
     * @ApiReturnParams (name="msg", type="str", required=true, description="说明")
     * @ApiReturnParams (name="amount", type="str", required=true, description="消费金额")
     * @ApiReturnParams (name="assets_type", type="str", required=true, description="资产类型:1=余额,2=奖金")
     * @ApiReturnParams (name="source_type", type="str", required=true, description="来源类型:1=消费,2=平台分红,3=提现,4=推广返利")
     * @ApiReturnParams (name="income", type="str", required=true, description="收入")
     * @ApiReturnParams (name="expenses", type="str", required=true, description="支出")
     *
     */

    public function assets()
    {
        $source_type = $this->request->param('source_type');
        $asset_id   = $this->request->param('asset_id');
        $page       = $this->request->param('page', 1);
        $where       = [];

        if ($asset_id) {
            $where['id'] = $asset_id;
        }

        if ($source_type) {
            $where['source_type'] = $source_type;
        }
        $result = Db::name('details')
            ->where($where)
            ->where('user_id', $this->auth->id)
            ->page($page, 10)
            ->order('createtime desc')
            ->select();
        foreach ($result as $k => $v) {
            $result[$k]['amount']     = $v['amount'] ? $v['amount'] : 0;
            $result[$k]['createtime'] = date('Y-m-d H:i:d', $v['createtime']);
        }
        $this->success('成功', ['data' => $result ?: []]);

    }

    /**
     * 用户信息
     *
     */
    public function userInfo()
    {
        $userInfo = Db::name('user')->alias('U')
            ->join('user_verified UV', 'UV.user_id = U.id')
            ->join('ddrive_user_token DU', 'DU.user_id = U.id', 'LEFT')
            ->join('real_verified RV', 'RV.user_id = U.id', 'LEFT')
            ->join('card_verified CV', 'CV.user_id = U.id', 'LEFT')
            ->join('driver_status DS', 'DS.user_id = U.id', 'LEFT')
            ->where('U.id', $this->auth->id)
            ->field('U.id,U.username,U.nickname,U.mobile,U.score,U.money,U.avatar,UV.real_verified,UV.driver_verified,UV.card_verified,DU.wx_openid,DU.mini_openid,DU.wx_name,RV.truename,RV.idcard,U.emergency_contact,U.contact_tel,CV.card_brand,CV.card_type,DS.status as driver_status')
            ->find();
        //设置Token
        $this->_token = Random::uuid();
        \app\common\library\Token::set($this->_token, $userInfo['id'], $this->keeptime);
        $userInfo                      = array_merge($userInfo, \app\common\library\Token::get($this->_token));
        $userInfo['avatar']            = $userInfo['avatar'] ? cdnurl($userInfo['avatar']) : '';
        $userInfo['openid']            = $userInfo['wx_openid'] ? $userInfo['wx_openid'] : '';
        $userInfo['mini_openid']       = $userInfo['mini_openid'] ? $userInfo['mini_openid'] : '';
        $userInfo['truename']          = $userInfo['truename'] ? $userInfo['truename'] : '';
        $userInfo['idcard']            = $userInfo['idcard'] ? $userInfo['idcard'] : '';
        $userInfo['emergency_contact'] = $userInfo['emergency_contact'] ? $userInfo['emergency_contact'] : '';
        $userInfo['contact_tel']       = $userInfo['contact_tel'] ? $userInfo['contact_tel'] : '';
        $userInfo['card_brand']        = $userInfo['card_brand'] ? $userInfo['card_brand'] : '';
        $userInfo['card_type']         = $userInfo['card_type'] ? $userInfo['card_type'] : '';
        $userInfo['driver_status']     = $userInfo['driver_status'] === 0 ? $userInfo['driver_status'] : 1;
        $userInfo['coupon_count']      = Db::name('user_coupon')->where('user_id', $this->auth->id)->where('coupon_status', 0)->count();
        $this->success('成功', $userInfo);
    }

    /**
     * 手机登录绑定微信
     *
     * @param string $mobile 手机号
     * @param string $openid openid
     * @param string $wx_name wx_name
     */
    public function mobileOnAccount()
    {
        $openid  = $this->request->param('openid');
        $wx_name = $this->request->param('wx_name');
        $unionId = $this->request->param('unionId');
        if (!$openid || !$wx_name || !$unionId) {
            $this->error('参数错误');
        }
        $user = Db::name('user')->where('id', $this->auth->id)->find();
        if (!$user) {
            $this->error('该用户不存在');
        }
        $ddrive_user = Db::name('ddrive_user_token')->where('wx_openid', $openid)->find();
        if ($ddrive_user['wx_openid']) {
            $this->error('该微信账户已存在');
        }
        Db::name('ddrive_user_token')->where('user_id', $this->auth->id)->update(['wx_openid' => $openid, 'wx_name' => $wx_name, 'unionId' => $unionId]);
        $this->success('绑定成功');
    }

    /**
     * 提现配置
     *
     * @param string $mobile 手机号
     * @param string $openid openid
     * @param string $wx_name wx_name
     */
    public function cash_set()
    {
        $ddrive_config             = get_addon_config('ddrive');
        $cash_set                  = [];
        $cash_set['withdraw_rate'] = $ddrive_config['withdraw_rate'];
        $cash_set['withdraw_min']  = $ddrive_config['withdraw_min'];
        $cash_set['withdraw_max']  = $ddrive_config['withdraw_max'];
        $this->success('成功', ['cash_set' => $cash_set]);
    }

    /**
     * 服务费支付
     *
     * @param string $type 1余额2微信
     */
    public function service_pay()
    {
        $type = $this->request->param('type');
        $user = Db::name('user')->where('id', $this->auth->id)->find();
        if ($user['platform_service_fee'] > 0) {
            if ($type == 1) {
                if ($user['money'] < $user['platform_service_fee']) {
                    $this->error('余额不足, 请联系客服充值');
                }
                try {
                    Db::name('user')->where('id', $this->auth->id)->setDec('money', $user['platform_service_fee']);
                    Db::name('user')->where('id', $this->auth->id)->setField('platform_service_fee', 0);
                    $detailModel = new Details();
                    $detailModel->addDetail($this->auth->id, 2, '支付平台服务费', $user['platform_service_fee'], 2, 2, 0);
                } catch (\Exception $e) {
                    $this->error($e->getMessage());
                }
                $this->success('支付成功');

            } else {
                //回调链接
                $notifyurl      = $this->request->root(true) . '/addons/ddrive/user/notifyx/paytype/wechat';
                $merchant_order = $this->auth->id . 'SE' . date('Ymd') . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
                $params         = [
                    'type'      => 'driver_wechat',
                    'orderid'   => $merchant_order,
                    'title'     => '代驾',
                    'amount'    => $user['platform_service_fee'],
                    'method'    => 'app',
                    'notifyurl' => $notifyurl,
                ];
                try {
                    $pay = json_encode(Service::submitOrder($params));
                    return json_encode(['code' => 1, 'msg' => '成功', 'data' => json_decode(json_decode($pay, true), true)]);
                } catch (\Throwable $th) {
                    return json_encode(['code' => 0, 'msg' => '失败']);
                }
            }
        } else {
            $this->error('您暂时不欠平台服务费');
        }

    }

    /**
     * 支付成功
     *
     * @return void
     */
    public function notifyx()
    {
        Log::record('支付回调');
        $paytype = $this->request->param('paytype');
        $pay     = \addons\epay\library\Service::checkNotify($paytype);
        if (!$pay) {
            Log::record('签名错误');
            echo '签名错误';
            return;
        }
        $data = $pay->verify();
        try {
            $user_id = substr($data['out_trade_no'], 0, strpos($data['out_trade_no'], 'SE'));
            Db::name('user')->where('id', $user_id)->setField('platform_service_fee', 0);
        } catch (Exception $e) {
            Log::record($e->getMessage());
        }
        echo $pay->success();
    }

    /**
     * 投诉中心
     *
     */
    public function complaint_category()
    {
        $order_id = $this->request->param('order_id');
        $sf_type  = $this->request->param('sf_type');
        if (!$order_id) {
            $this->error('参数错误');
        }
        if ($sf_type == 1) {
            $order = (new \addons\ddrive\model\Sforder())->where('id', $order_id)->find();
        } elseif ($sf_type == 2) {
            $order = (new \addons\ddrive\model\Hyorder())::with('shaddress')->where('id', $order_id)->where('user_id', $this->auth->id)->find();
        } else {
            $order = (new \addons\ddrive\model\Order())->where('id', $order_id)->where('user_id', $this->auth->id)->find();
        }

        if (!$order) {
            $this->error('该订单不存在');
        }
        if ($order['status'] != 99 && in_array($sf_type, [1, 2])) {
            $this->error('该订单还未完成，暂无法投诉');
        }
        if ($order['status'] != 5 && $sf_type == 1) {
            $this->error('该订单还未完成，暂无法投诉');
        }
        if (($order['complete_time'] + 86400 * 7) < time()) {
            $this->error('该订单已超过售后时间');
        }
        $order_info                  = [];
        $order_info['createtime']    = date('m-d H:i', $order['createtime']);
        $order_info['status']        = $order['status'];
        $order_info['start_address'] = $order['start_address'];
        if($sf_type == 2){
            $order_info['end_address']   = $order['shaddress'];
        }else{
            $order_info['end_address']   = $order['end_address'];
        }
        $list                        = (new ComplaintCategory())->select();
        foreach ($list as $k => $v) {
            $list[$k]['category_image'] = cdnurl($v['category_image']);
        }
        $this->success('成功', ['list' => $list ? $list : [], 'order_info' => $order_info]);
    }

    /**
     * 投诉
     *
     */
    public function complaint()
    {
        $order_id          = $this->request->param('order_id');
        $complaint_id      = $this->request->param('complaint_id');
        $remark            = $this->request->param('remark');
        $certificate_image = $this->request->param('certificate_image');
        $sf_type           = $this->request->param('sf_type');
        if (!$order_id || !$remark || !$certificate_image || !$complaint_id) {
            $this->error('参数错误');
        }
        $id = (new Complaint())->insertGetId([
            'user_id'           => $this->auth->id,
            'order_id'          => $order_id,
            'complaint_id'      => $complaint_id,
            'type'              => $sf_type == 1 ? 2 : ($sf_type == 2 ? 3 : 1),
            'remark'            => $remark,
            'certificate_image' => $certificate_image,
            'status'            => 0,
            'createtime'        => time(),
            'updatetime'        => time(),
        ]);
        if ($id) {
            $this->success('投诉成功,请等待审核结果', ['id' => $id]);
        } else {
            $this->error('网络错误,请稍后再试');
        }
    }

    /**
     * 投诉进度
     *
     */
    public function complaint_schedule()
    {
        $order   = Db::name('ddrive_order')->alias('O')
            ->join('complaint C', 'C.order_id = O.id')
            ->join('complaint_category CA', 'CA.id = C.complaint_id')
            ->where('C.user_id', $this->auth->id)
            ->where('C.type', 1)
            ->field('C.createtime,C.status,O.start_address,C.type,O.end_address,O.createtime as create_time,CA.name,C.id')
            ->order('C.id desc')
            ->select();
        $sforder = Db::name('ddrive_sf_order')->alias('SO')
            ->join('complaint C', 'C.order_id = SO.id')
            ->join('complaint_category CA', 'CA.id = C.complaint_id')
            ->where('C.user_id', $this->auth->id)
            ->where('C.type', 2)
            ->field('C.createtime,C.status,SO.start_address,C.type,SO.end_address,SO.createtime as create_time,CA.name,C.id')
            ->order('C.id desc')
            ->select();
        $hyorder = (new \addons\ddrive\model\Hyorder())->alias('HO')
            ->join('complaint C', 'C.order_id = HO.id')
            ->join('complaint_category CA', 'CA.id = C.complaint_id')
            ->where('C.user_id', $this->auth->id)
            ->where('C.type', '3')
            ->field('C.createtime,C.status,HO.start_address,C.type,HO.createtime as create_time,CA.name,C.id,C.order_id')
            ->order('C.id desc')
            ->select();
        foreach ($hyorder as $k=>$v){
            $hyorder[$k]['shaddress'] =  (new Hyaddress())->where('order_id',$v['order_id'])->select();
        }
        $new_order = array_merge($order, $sforder, $hyorder);
        foreach ($new_order as $k => $v) {
            $new_order[$k]['createtime'] = (new Common())->getTimeInfoUser($v['createtime']);
        }
        $this->success('成功', ['order_info' => $new_order]);

    }

    /**
     * 投诉详情
     *
     */
    public function complaint_info()
    {
        $id = $this->request->param('complaint_id');
        if (!$id) {
            $this->error('参数错误');
        }
        $complaint                      = (new Complaint())->where('id', $id)->find();
        $complaint['certificate_image'] = explode(',', $complaint['certificate_image']);
        foreach ($complaint['certificate_image'] as $k => $v) {
            $certificate_image[] = cdnurl($v);
        }
        $complaint['createtime']        = date('Y-m-d H:i:s', time());
        $complaint['updatetime']        = $complaint['updatetime'] ? $complaint['updatetime'] : '';
        $complaint['certificate_image'] = $certificate_image;
        $complaint['handling_opinions'] = $complaint['handling_opinions'] ? $complaint['handling_opinions'] : '平台正在核查,请耐心等待';
        $this->success('成功', ['complaint_info' => $complaint]);

    }

    /**
     * 客服电话
     *
     */
    public function consumer_hotline()
    {
        $tel = get_addon_config('ddrive')['phone'];
        $this->success('成功', ['tel' => $tel]);
    }

    /**
     * 添加紧急联系人
     *
     */
    public function add_contact()
    {
        $emergency_contact = $this->request->param('emergency_contact');
        $contact_tel       = $this->request->param('contact_tel');
        if (!$emergency_contact || !$contact_tel) {
            $this->error('参数错误');
        }
        $ret = Db::name('user')->where('id', $this->auth->id)->update(['emergency_contact' => $emergency_contact, 'contact_tel' => $contact_tel]);
        if ($ret) {
            $this->success('添加成功');
        }
    }

    /**
     * 优惠券
     *
     */
    public function coupon()
    {
        $type  = $this->request->param('type', 1); //用户 1=待使用,2=已使用,3=已过期
        $where = [];
        if ($type == 1) {
            $where['coupon_status'] = 0;
        } elseif ($type == 2) {
            $where['coupon_status'] = 1;
        } else {
            $where['coupon_status'] = 2;
        }
        $coupon_list = Db::name('user_coupon')
            ->where($where)
            ->where('user_id', $this->auth->id)
            ->field('coupon_id,coupon_type,coupon_name,remark,coupon_price,limit_price,coupon_status,createtime,coupon_status as status,id,expiration as exp')
            ->order('id desc')
            ->select();
        foreach ($coupon_list as $k => $v) {
            $coupon_list[$k]['expiration'] = $v['exp'] ? date('Y-m-d', $v['exp']) : '';
            $coupon_list[$k]['createtime'] = $v['createtime'] ? date('Y-m-d', $v['createtime']) : '';
        }
        $this->success('成功', $coupon_list ? $coupon_list : []);
    }

    /**
     * 支付优惠券
     *
     */
    public function pay_coupon()
    {
        $order_id = $this->request->param('order_id');
        $sf       = $this->request->param('sf_type');
        $hy_order_price       = $this->request->param('order_price');
        $where    = [];
        if ($sf == 1) {
            $where['coupon_type'] = 2;
            $order                = Db::name('ddrive_sf_order')->where('id', $order_id)->find();
            if (!$order) {
                $this->error('该订单不存在');
            }
            $order_money = $order['order_money'];
        } elseif ($sf == 2){
            $where['coupon_type'] = 3;
            if(empty($hy_order_price)){
                $this->error('请选择订单金额');
            }
            $order_money = $hy_order_price;
        }else {
            $where['coupon_type'] = 1;
            $order                = Db::name('ddrive_order')->where('id', $order_id)->find();
            if (!$order) {
                $this->error('该订单不存在');
            }
            $order_money = $order['price'];
        }
        $coupon_list = Db::name('user_coupon')->alias('UC')
            ->where('coupon_status', 0)
            ->where('user_id', $this->auth->id)
            ->field('coupon_id,coupon_type,coupon_name,remark,coupon_price,limit_price,coupon_status,createtime,coupon_status as status,id,expiration as exp')
            ->order('id desc')
            ->select();
        foreach ($coupon_list as $k => $v) {
            if ($v['limit_price'] <= $order_money) {
                $coupon_list[$k]['be_status'] = 1;
            } else {
                $coupon_list[$k]['be_status'] = 0;
            }
            $coupon_list[$k]['expiration'] = $v['exp'] ? date('Y-m-d', $v['exp']) : '';
            $coupon_list[$k]['createtime'] = $v['createtime'] ? date('Y-m-d', $v['createtime']) : '';
        }
        $this->success('成功', $coupon_list ? $coupon_list : []);
    }

    public function driver_status()
    {
        $status = $this->request->param('status', 1);
        if (!in_array($status, [0, 1])) {
            $this->error('参数错误');
        }
        $driver_status = Db::name('driver_status')->where('user_id', $this->auth->id)->find();
        if ($driver_status) {
            Db::name('driver_status')->where('user_id', $this->auth->id)->setField('status', $status);
            Db::name('driver_status')->where('user_id', $this->auth->id)->setField('createtime', time());
        } else {
            Db::name('driver_status')->insert([
                'user_id'    => $this->auth->id,
                'status'     => $status,
                'creating'   => 0,
                'createtime' => time(),
            ]);
        }
        $this->success('成功');
    }
    // 司机正在创建订单状态
    public function driver_creating_status()
    {
        $status = $this->request->param('status', 1);
        if (!in_array($status, [0, 1])) {
            $this->error('参数错误');
        }
        $driver_status = Db::name('driver_status')->where('user_id', $this->auth->id)->find();
        if ($driver_status) {
            Db::name('driver_status')->where('user_id', $this->auth->id)->setField('creating', $status);
            Db::name('driver_status')->where('user_id', $this->auth->id)->setField('createtime', time());
        } else {
            Db::name('driver_status')->insert([
                'user_id'    => $this->auth->id,
                'status'     => 0,
                'creating'   => $status,
                'createtime' => time(),
            ]);
        }
        $this->success('成功');
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
    // 获取最近的多少个司机
    public function getNearbyDriver()
    {

      $longitude = $this->request->request('longitude');
      $latitude = $this->request->request('latitude');
      $num = $this->request->request('num', 20);
      $where = [];
      $where['driver_verified.status'] = '1';
      // 输出到日志
      // Log::write(Db::name('driver_verified'));
      $list = $this->driverVerifiedModel
          ->with(['user'])
          ->where($where)
          ->select();
      $result = [];
      foreach ($list as $row) {
          $row->getRelation('user')->visible(['username', 'mobile', 'longitude', 'latitude', 'avatar']);
          $distance = $this->distance($latitude, $longitude, $row->user->latitude, $row->user->longitude);
          $row->distance = $distance;
          // 把->user
          $result[] = $row;
      }
      //  按照距离排序
      // 自定义排序算法


// 使用usort()函数进行排序
      usort($result, function ($a, $b) {
        return $a['distance'] - $b['distance'];
      });
      // 再通过user_id去driver_status表中取出driver_status, driver_create_status
      foreach ($list as $key => $value) {
          $driver_status = Db::name('driver_status')->where('user_id', $value['user_id'])->find();
          // 再去real_verifiyed 取出turename
          $real_verified = Db::name('real_verified')->where('user_id', $value['user_id'])->find();
          if ($real_verified) {
            $list[$key]['truename'] = $real_verified['truename'];
          }
          // 从card_verified 取出驾龄
          $card_verified = Db::name('driver_verified')->where('user_id', $value['user_id'])->find();
          if ($card_verified) {
            $list[$key]['driver_age'] = $card_verified['driver_age'];
          }
          if ($driver_status) {
            $list[$key]['driver_status'] = $driver_status['status'];
            $list[$key]['driver_create_status'] = $driver_status['create_status'];
          }
          // 从订单表中取出订单数
          $order_count = Db::name('ddrive_order')->where('driver_id', $value['user_id'])->count();
          $list[$key]['order_count'] = $order_count;
          // 把user展开放到外面
          $list[$key]['username'] = $value['user']['username'];
          $list[$key]['mobile'] = $value['user']['mobile'];
          $list[$key]['longitude'] = $value['user']['longitude'];
          $list[$key]['latitude'] = $value['user']['latitude'];

      }
      $result = array_slice($result, 0, $num );
      $this->success('成功', $result);
      
    }


}
