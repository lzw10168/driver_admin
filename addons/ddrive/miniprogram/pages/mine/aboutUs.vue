<template>
  <view class="index">
    <view class="top">
      <view class="logo"><image src="../../static/djs.png" mode=""></image></view>
      <text class="t1">{{ info.name }}</text>
      <!--      <text class="t2">版本号 1.0.0</text> -->
    </view>
    <view class="item" @tap="call">
      <text class="t1">客服电话</text>
      <text class="t2">{{ info.phone }}</text>
    </view>
    <view class="item">
      <text class="t1">工作时间</text>
      <text class="t2">{{ info.worktime }}</text>
    </view>
    <view class="item">
      <text class="t1">客服邮箱</text>
      <text class="t2">{{ info.email }}</text>
    </view>
  </view>
</template>

<script>
import helper from '../../common/helper.js';
import api from '../../common/api.js';
export default {
  data() {
    return {
      host: helper.host,
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
    async getInfo() {
      let res = await api.getSetting();
      this.info = res.data;
    }
  }
};
</script>

<style lang="scss">
.index {
  padding: 0 4%;
}
.top {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  margin-bottom: 10%;
  .logo {
    background-color: #32c45e;
    width: 200upx;
    height: 200upx;
    border-radius: 50%;
    image {
      width: 100%;
      height: 100%;
    }
  }
  .t1 {
    font-size: 46upx;
    font-weight: 800;
    margin-top: 10upx;
  }
  .t2 {
    font-size: 28upx;
    color: #999999;
  }
}
.item {
  font-size: 28upx;
  display: flex;
  justify-content: space-between;
  padding: 30upx 0;
  border-bottom: 1px solid #eeeeee;
  .t2{
	  color: #999999;
	  font-size: 24upx;
  }
}
</style>
