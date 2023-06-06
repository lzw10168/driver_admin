<template>
  <view class="index">
    <view class="logo">
      <view class="top">
        你好！
        <image src="../../static/djr.png" mode=""></image>
      </view>
      <view class="bottom">欢迎注册满城代驾</view>
    </view>
    <view class="input mobile" :class="{ active: focus == 'mobile' }">
      <text class="icon">&#xe605;</text>
      <input type="number" value="" maxlength="11" id="mobile" v-model="info.mobile" @focus="onInputFocus" placeholder="请输入手机号" />
      <view class="right" @tap="getCaptcha">{{ isSend ? second + '秒后获取' : '发送验证码' }}</view>
    </view>
    <view class="input" :class="{ active: focus == 'captcha' }">
      <text class="icon">&#xe623;</text>
      <input type="number" value="" id="captcha" maxlength="4" v-model="info.captcha" @focus="onInputFocus" placeholder="请输入验证码" />
    </view>
    <view class="input" :class="{ active: focus == 'password' }">
      <text class="icon">&#xe60d;</text>
      <input type="password" value="" id="password" @focus="onInputFocus" v-model="info.password" placeholder="请输入密码" />
    </view>
    <view class="input" :class="{ active: focus == 'repassword' }">
      <text class="icon">&#xe60d;</text>
      <input type="password" value="" id="repassword" @focus="onInputFocus" v-model="info.repassword" placeholder="请确认密码" />
    </view>

    <view class="link">
      <view class="left" @tap="navigateTo('/pages/login/forgetThePassword')">忘记密码？</view>
      <view class="right" @tap="navigateTo('/pages/login/login')">去登陆</view>
    </view>
    <button type="primary" class="button" open-type="getUserInfo" @tap="register">立即注册</button>
  </view>
</template>

<script>
import helper from '../../common/helper.js';
import api from '../../common/api.js';

export default {
  data() {
    return {
      focus: '',
      info: {},
      second: 60,
      isSend: false
    };
  },
  methods: {
    navigateTo(url) {
      uni.navigateTo({
        url: url
      });
    },
    onInputFocus(e) {
      let id = e.target.id;
      this.focus = id;
    },
    async getCaptcha() {
      if (!this.info.mobile || this.info.mobile.length != 11) {
        helper.toast('请输入正确的验证码');
        return false;
      }
      if (this.isSend) {
        return false;
      }
      let res = await api.getCaptcha(this.info.mobile, 'register');
      if (res.code == 1) {
        this.countdown();
      }
      helper.toast(res.msg);
    },
    countdown() {
      this.isSend = false;
      this.interval = setInterval(() => {
        if (this.second <= 1) {
          this.second = 60;
          this.isSend = false;
          clearInterval(this.interval);
        } else {
          this.isSend = true;
          this.second--;
        }
      }, 1000);
    },
    async register() {
      if (!this.info.mobile) {
        helper.toast('请输入手机号');
        return false;
      }
      if (!this.info.captcha) {
        helper.toast('请输入验证码');
        return false;
      }
      if (!this.info.password) {
        helper.toast('请输入密码');
        return false;
      }
      if (this.info.password != this.info.repassword) {
        helper.toast('两次密码输入不一致');
        return false;
      }
      uni.showLoading();
      uni.login({
        provider: 'weixin',
        success: async loginRes => {
			console.log(1111111111111);
          let code = loginRes.code;
          // 用code换取openid
          const ret = await api.getOpenid(loginRes.code);
          this.info.openid = ret.data.openid;
          // 获取用户信息
          uni.getUserInfo({
            provider: 'weixin',
            success: async infoRes => {
              let userinfo = infoRes.userInfo;
              this.info.nickname = userinfo.nickName;
              this.info.avatar = userinfo.avatarUrl;
              this.info.gender = userinfo.gender;
              let res = await api.register(this.info);
              uni.hideLoading();
              helper.toast(res.msg);
              if (res.code == 1) {
                setTimeout(() => {
                  uni.navigateBack({
                    url: '/pages/login/login'
                  });
                }, 1500);
              }
            },
            fail: () => {
              uni.hideLoading();
              helper.toast('获取用户信息失败');
            }
          });
        },
        fail: () => {
          uni.hideLoading();
          helper.toast('获取用户信息失败');
        }
      });
    }
  }
};
</script>

<style lang="scss">
.icon {
  color: #acacac;
}
.index {
  padding: 0 10%;
}
.logo {
  margin-top: 10%;
  margin-bottom: 10%;
  .top {
    display: flex;
    align-items: flex-end;
    font-size: 48upx;
    margin-bottom: 20upx;
    image {
      width: 200upx;
      height: 160upx;
    }
  }
}
.input {
  display: flex;
  align-items: center;
  border-bottom: 1px #f8f8f8 solid;
  padding: 26upx 0;
  text {
    width: 10%;
  }
  input {
    width: 90%;
  }
}
.mobile {
  input {
    width: 60%;
  }
  .right {
    width: 30%;
    text-align: right;
    font-size: 28upx;
  }
}
.active {
  border-bottom: #4cd964 1px solid;
}
.link {
  margin-top: 40upx;
  font-size: 26upx;
  width: 100%;
  display: flex;
  justify-content: space-between;
}
.button {
  width: 100%;
  border-radius: 50upx;
  margin-top: 20%;
  font-size: 30upx;
  background-color: #32c45e;
  border: none;
  box-shadow: 0px 5px 10px 1px #c1d6f2;
}
</style>
