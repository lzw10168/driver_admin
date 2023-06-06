<template>
  <view>
    <view class="index">
      <view class="input">
        <text class="left">客服热线</text>
        <view class="right" @tap="call">{{ info.phone }}</view>
        <text class="icon">&#xe60f;</text>
      </view>
      <view class="input" @tap="navigateTo('/pages/mine/opinion')">
        <text class="left">意见反馈</text>
        <view class="right"></view>
        <text class="icon">&#xe60f;</text>
      </view>
      <view class="input" @tap="navigateTo('/pages/mine/aboutUs')">
        <text class="left">关于我们</text>
        <view class="right"></view>
        <text class="icon">&#xe60f;</text>
      </view>
      <view class="input" style="border: none;" @tap="navigateTo('/pages/mine/agree')">
        <text class="left">用户协议</text>
        <view class="right"></view>
        <text class="icon">&#xe60f;</text>
      </view>
    </view>
    <button class="button" type="primary" @tap="logout">退出当帐号</button>
  </view>
</template>

<script>
import helper from '../../common/helper.js';
import api from '../../common/api.js';
export default {
  data() {
    return {
      info: {}
    };
  },
  onLoad() {
    this.getInfo();
  },
  methods: {
    call() {
      uni.makePhoneCall({
        phoneNumber: this.info.phone
      });
    },
    navigateTo(url) {
      uni.navigateTo({
        url: url
      });
    },
    async getInfo() {
      let res = await api.getSetting();
      this.info = res.data;
    },
    logout() {
      api.logout();
      helper.clearUsetToken();
      uni.reLaunch({
        url: '/pages/login/login?switchTab=1&url=/pages/mine/mine'
      });
    }
  }
};
</script>

<style lang="scss">
page {
  background-color: #f7f7f7;
}
.index {
  border-top: 1px solid #e6e6e6;
  padding: 20upx 5%;
  background-color: #ffffff;
}
.input {
  display: flex;
  align-items: center;
  font-size: 26upx;
  padding: 30upx 0;
  border-bottom: 1px solid #e6e6e6;
  .left {
    width: 20%;
  }
  .icon {
    width: 10%;
    text-align: right;
    color: #b5b5b5;
    font-size: 26upx;
  }
  .right {
    width: 80%;
    text-align: right;
    color: #919191;
    image {
      width: 50upx;
      height: 50upx;
      border-radius: 50%;
    }
  }
}
.button {
  border-radius: 0px;
  margin-top: 20upx;
  font-size: 32upx;
  background-color: #ffffff;
  color: #101010;
  width: 90%;
}
.popup {
  height: 100%;
  width: 100%;
}
</style>
