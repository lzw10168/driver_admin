<template>
  <view class="index">
    <view class="input mobile" :class="{ active: focus == 'mobile' }">
      <text class="icon">&#xe605;</text>
      <input type="number" value="" maxlength="11" id="mobile" v-model="info.mobile" @focus="onInputFocus" placeholder="请输入手机号" />
      <view class="right" @tap="getCaptcha">{{ isSend ? second + '秒后获取' : '发送验证码' }}</view>
    </view>
    <view class="input" :class="{ active: focus == 'captcha' }">
      <text class="icon">&#xe623;</text>
      <input type="text" value="" maxlength="4" id="captcha" v-model="info.captcha" @focus="onInputFocus" placeholder="请输入验证码" />
    </view>
    <view class="input" :class="{ active: focus == 'password' }">
      <text class="icon">&#xe60d;</text>
      <input type="password" value="" id="password" v-model="info.newpassword" @focus="onInputFocus" placeholder="请输入新密码" />
    </view>
    <view class="link"><view class="right" @tap="back()">去登陆</view></view>
    <button type="primary" class="button" @tap="reset">重置密码</button>
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
    onInputFocus(e) {
      let id = e.target.id;
      this.focus = id;
    },
    async getCaptcha() {
      if (!this.info.mobile || this.info.mobile.length != 11) {
        return false;
      }
      if(this.isSend){
        return false;
      }
      let res = await api.getCaptcha(this.info.mobile, 'resetpwd');
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
    async reset() {
      if (!this.info.mobile) {
        helper.toast('请输入手机号');
        return false;
      }
      if (!this.info.newpassword) {
        helper.toast('请输入密码');
        return false;
      }
      let res = await api.resetpwd(this.info);
      helper.toast(res.msg);
      if (res.code == 1) {
        setTimeout(() => {
          this.back();
        }, 1500);
      }
    },
    back() {
      uni.navigateBack({
        url: '/pages/login/login'
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
    font-size: 60upx;
    image {
      width: 60upx;
      height: 60upx;
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
  text-align: right;
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
