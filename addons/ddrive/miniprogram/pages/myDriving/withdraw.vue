<template>
  <view class="index">
    <view class="top">
      <view class="t1"  @tap="navigateTo('/pages/myDriving/recording')">提现记录</view>
      <view class="icon">&#xe61c;</view>
      <view class="t2">账户余额</view>
      <view class="t3">
        {{ userinfo.money }}
        <text>元</text>
      </view>
      <button class="button" type="primary" @tap="navigateTo('/pages/myDriving/withdraws')">提现</button>
    </view>

    <view class="bottom">
      <view class="item" @tap="navigateTo('/pages/myDriving/myDriving')">
        <text class="icon">&#xe606;</text>
        首页
      </view>
      <view class="item" @tap="navigateTo('/pages/myDriving/order')">
        <text class="icon">&#xe616;</text>
        订单
      </view>
      <view class="item active" @tap="navigateTo('/pages/myDriving/withdraw')">
        <text class="icon">&#xe61c;</text>
        提现
      </view>
    </view>
  </view>
</template>
<script>
import helper from '../../common/helper.js';
import api from '../../common/api.js';
export default {
  data() {
    return {
      userinfo: {},
      info:{}
    };
  },
  onShow() {
    this.getUserInfo();
   
  },
  methods: {

    async getUserInfo() {
      let res = await api.getUserInfo();
      this.userinfo = res.data;
    },
    navigateTo(url) {
      uni.navigateTo({
        url: url
      });
    }
  }
};
</script>

<style lang="scss" scoped>
page,
.index {
  background-color: #fbfbfb;
}
.top {
  text-align: center;
  padding: 30upx;
  background-color: #32c45e;
  color: #ffffff;
  .t1 {
    width: 100%;
    text-align: right;
    font-size: 26upx;
  }
  .icon {
    font-size: 160upx;
  }
  .t2 {
    padding: 10upx 0;
    font-size: 28upx;
  }
  .t3 {
    font-size: 50upx;
    text {
      font-size: 28upx;
    }
  }
  .button {
    width: 25%;
    background-color: #ffffff;
    color: #101010;
    font-size: 28upx;
    border-radius: 50upx;
    margin-top: 5%;
  }
}
.bottom {
  position: absolute;
  width: 100%;
  bottom: 0;
  height: 120upx;
  display: flex;
  align-items: center;
  justify-content: space-around;
  .item {
    width: 33.33%;
    display: flex;
    flex-direction: column;
    align-items: center;
    font-size: 24upx;
    .icon {
      font-size: 56upx;
      color: #999999;
    }
  }
}
.active {
  color: #32c45e !important;
  .icon {
    color: #32c45e !important;
  }
}
</style>
