<template>
  <view class="index">
    <view class="logo">
      <view class="top">
        你好！
        <image src="../../static/djr.png" mode=""></image>
      </view>
      <view class="bottom">欢迎使用同城代驾</view>
    </view>
    <view class="input active">
      <text class="icon">&#xe605;</text>
      <input type="number" value="" maxlength="11" id="mobile" v-model="info.mobile" placeholder="请输入手机号" />
    </view>

    <view class="input active">
      <text class="icon">&#xe60d;</text>
      <input type="password" value="" id="password" v-model="info.password" placeholder="请确认密码" />
    </view>

    <view class="link">
      <view class="left" @tap="forgetThePassword()">忘记密码？</view>
      <view class="right" @tap="register()">去注册</view>
    </view>
    <button type="primary" class="button" open-type="getUserInfo" @tap="login">立即登录</button>
    <view class="to-index" @tap="toIndex()">暂不登录</view>
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
      url: '/pages/index/index',
      redirectTo: false
    };
  },
  onLoad(option) {
    if (option.url) {
      this.url = decodeURIComponent(option.url);
    }
    if (option.redirectTo) {
      this.redirectTo = true;
    }
  },
  methods: {
    toIndex(){
      uni.switchTab({
        url:"/pages/index/index"
      })
    },
    onInputFocus(e) {
      let id = e.target.id;
      this.focus = id;
    },
    async login() {
      console.log(this.info);
      if (!this.info.mobile) {
        helper.toast('请输入手机号');
        return false;
      }
      if (!this.info.password) {
        helper.toast('请输入密码');
        return false;
      }
      uni.showLoading({
        title: '登录中'
      });
      uni.login({
        provider: 'weixin',
        success: async loginRes => {
          let code = loginRes.code;
          // 用code换取openid
          const ret = await api.getOpenid(loginRes.code);
          if(ret.code==0){
            uni.hideLoading();
            uni.showModal({
              title: '操作提示',
              content: '获取openid失败，请检查相关配置',
              showCancel: false,
            });
            return false;
          }
          this.info.openid = ret.data.openid;
          let res = await api.login(this.info);
          uni.hideLoading();
          helper.toast(res.msg);
          if (res.code == 1) {
            helper.setUserToken(res.data.userinfo.token);
            setTimeout(() => {
              if (this.redirectTo) {
                // 声明为跳转到指定url
                uni.redirectTo({
                  url: this.url
                });
              } else {
                // 默认为打开tab页面
                uni.switchTab({
                  url: this.url
                });
              }
            }, 500);
          }
        }
      });
    },
    register() {
      uni.navigateTo({
        url: 'register'
      });
    },
    forgetThePassword() {
      uni.navigateTo({
        url: 'forgetThePassword'
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
    font-size: 80upx;
    margin-bottom: 20upx;
    image {
      width: 180upx;
      height: 160upx;
    }
  }
  .bottom{
	  font-weight: 800;
	  letter-spacing: 10upx;
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
.yzm {
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
.to-index{
  text-align: center;
  margin-top: 20upx;
}
</style>
