<template>
  <view class="index">
    <view class="bg"></view>
    <view class="user" v-if="isLogin">
      <image :src="userinfo.avatar" mode=""></image>
      <view class="right">
        <text class="t1">{{ userinfo.nickname }}</text>
        <text class="t2">积分：{{ userinfo.score }}</text>
        <text class="t2 bio">{{ userinfo.bio }}</text>
      </view>
    </view>
    <view class="user" v-if="!isLogin">
      <view class="to-login" @tap="navigateTo('/pages/login/login')">请登录</view>
    </view>
    <view class="m_list">
      <view class="item" @tap="navigateTo('/pages/mine/updataUser')">
        <image src="../../static/yh.png" mode=""></image>
        <text class="t3">个人资料</text>
        <text class="icon right">&#xe60f;</text>
      </view>
      <view class="item" @tap="navigateTo('/pages/mine/order')">
        <image src="../../static/order.png" mode=""></image>
        <text class="t3">我的订单</text>
        <text class="icon right">&#xe60f;</text>
      </view>
      <view class="item" @tap="navigateTo(userinfo.dirver == 0 ? '/pages/mine/applyDriving' : '/pages/myDriving/myDriving')">
        <image src="../../static/dj.png" mode=""></image>
        <text class="t3">我是代驾</text>
        <text class="icon right">&#xe60f;</text>
      </view>
      <view class="item" @tap="navigateTo('/pages/mine/integral')">
        <image src="../../static/jf.png" mode=""></image>
        <text class="t3">积分</text>
        <text class="icon right">&#xe60f;</text>
      </view>
      <view class="item" @tap="navigateTo('/pages/mine/myForum')">
        <image src="../../static/wdcl.png" mode=""></image>
        <text class="t3">我的畅聊</text>
        <text class="icon right">&#xe60f;</text>
      </view>
      <view class="item" @tap="navigateTo('/pages/mine/setting')">
        <image src="../../static/sz.png" mode=""></image>
        <text class="t3">设置</text>
        <text class="icon right">&#xe60f;</text>
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
      userinfo: {
        group_id: 1,
        nickname: '',
        score: 0,
        dirver:0
      },
      isLogin:false
    };
  },
  onShow() {
    if (!helper.hasLogin()) {
      this.isLogin=false;
    } else {
      this.isLogin=true;
      this.getUserInfo();
    }
  },
  methods: {
    async getUserInfo() {
      let res = await api.getUserInfo();
      this.userinfo = res.data;
    },
    navigateTo(url) {
      if(!helper.hasLogin()){
        url='/pages/login/login'
      }
      uni.navigateTo({
        url: url
      });
    }
  }
};
</script>

<style lang="scss" scoped>
page {
  background-color: #f7f7f7;
}
.bg {
  width: 100%;
  height: 260upx;
  background-color: #32c45e;
}
.user {
  width: 90%;
  display: flex;
  align-items: center;
  border-radius: 20upx;
  margin-left: 5%;
  margin-top: -110upx;
  background-color: #ffffff;
  box-shadow: 1px 1px 4px 0px #dfdfdf;
  height: 240upx;
  .to-login{
    text-align: center;
    color: #32c45e;
    width: 100%;
  }
  image {
    width: 140upx;
    height: 140upx;
    margin: 40upx;
    border-radius: 50%;
  }
  .right {
    display: flex;
    flex-direction: column;
    .t1 {
      font-size: 28upx;
      font-weight: 800;
      margin-bottom: 10upx;
    }
    .t2 {
      font-size: 24upx;
	  font-weight: 800;
    }
    .bio{
      margin-top: 30upx;
      color: #555555;
    }
  }
}
.m_list {
  width: 90%;
  margin-left: 5%;
  background-color: #ffffff;
  border-radius: 20upx;
  margin-top: 10%;
  box-shadow: 1px 1px 4px 0px #dfdfdf;
  .item {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40upx 20upx;

    image {
      width: 50upx;
      height: 50upx;
    }
    .icon {
      text-align: right;
      width: 10%;
      font-size: 28upx;
      color: #d7d7d7;
    }
    .t3 {
      width: 75%;
      margin-left: 5%;
      font-size: 28upx;
    }
  }
}
</style>
