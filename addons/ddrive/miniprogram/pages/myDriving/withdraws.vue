<template>
  <view>
    <view class="top">
      <text class="left">金额</text>
      <input v-model.number="numData" type="number" step="1" placeholder="请输入提现金额" />
      <text class="right">手续费{{info.withdraw_rate}}%</text>
    </view>
    <view class="title">提现方式</view>
    <view class="payment">
      <image src="../../static/wx.png" mode=""></image>
      <text>微信提现</text>
    </view>
    <button class="button" type="primary" @tap="post">确认提现</button>
  </view>
</template>

<script>
import helper from '../../common/helper.js';
import api from '../../common/api.js';
export default {
  data() {
    return {
      numData: '',
      info:{}
    };
  },
  onShow() {
    this.getInfo();
  },
  methods: {
    async getInfo() {
      let res = await api.getSetting();
      this.info = res.data;
    },
    navigateTo(url) {
      uni.navigateTo({
        url: url
      });
    },
    async post() {
      if(this.numData<=0){
        helper.toast('请输入正确金额');
        return false;
      }
      let res = await api.withdraw(this.numData);
      uni.showModal({
        title: '操作提示',
        content: res.msg,
        showCancel: false,
        success(res) {
          if(res.confirm){
            uni.navigateBack({
              
            });
          }
        }
      });
    }
  }
};
</script>

<style lang="scss">
.top {
  display: flex;
  align-items: center;
  font-size: 28upx;
  padding: 30upx;
  input {
    width: 65%;
  }
  .left {
    width: 10%;
  }
  .right {
    width: 25%;
    font-size: 20upx;
    color: #e51c23;
  }
}
.title {
  padding: 30upx;
  font-size: 28upx;
  background-color: #f5f5f5;
}
.payment {
  display: flex;
  width: 90%;
  margin-left: 30upx;
  padding: 30upx 0;
  align-items: center;
  font-size: 28upx;
  border-bottom: 1px solid #f5f5f5;
  image {
    width: 50upx;
    height: 50upx;
    margin-right: 20upx;
  }
}
.button {
  background-color: #32c45e;
  width: 90%;
  font-size: 28upx;
  margin-top: 20%;
}
</style>
