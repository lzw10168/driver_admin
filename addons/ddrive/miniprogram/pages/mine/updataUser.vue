<template>
  <view>
    <view class="index">
      <view class="input" @tap="upload">
        <text class="left">头像</text>
        <view class="right"><image :src="userinfo.avatar" mode=""></image></view>
        <text class="icon">&#xe60f;</text>
      </view>
      <view class="input">
        <text class="left">手机号</text>
        <view class="right">{{ userinfo.mobile }}</view>
        <!-- <text class="icon">&#xe60f;</text> -->
      </view>
      <view class="input" style="border: none;" @tap="update('bio', userinfo.bio, '请输入个性签名')">
        <text class="left">个性签名</text>
        <view class="right">{{ userinfo.bio }}</view>
        <text class="icon">&#xe60f;</text>
      </view>
    </view>

    <prompt :visible.sync="promptVisible" :placeholder="placeholder" :defaultValue="defaultValue" @confirm="clickPromptConfirm" mainColor="#32c45e"></prompt>
  </view>
</template>

<script>
import Prompt from '@/components/zz-prompt/index.vue';
import helper from '../../common/helper.js';
import api from '../../common/api.js';
export default {
  components: {
    Prompt
  },
  data() {
    return {
      userinfo: {},
      promptVisible: false,
      placeholder: '',
      defaultValue: '',
      key: ''
    };
  },
  onLoad() {
    this.getUserInfo();
  },
  methods: {
    upload() {
      uni.chooseImage({
        success: async chooseImageRes => {
          let res = await api.upload(chooseImageRes.tempFilePaths[0]);
          if (res.code != 1) {
            helper.toast(res.msg);
            return false;
          }
          this.userinfo.avatar = helper.host + res.data.url;
          res = await api.updateUserInfo({ avatar: this.userinfo.avatar });
          helper.toast(res.msg);
        }
      });
    },
    async update(key, defaultValue, placeholder) {
      this.placeholder = placeholder;
      this.defaultValue = defaultValue;
      this.key = key;
      this.promptVisible = true;
    },
    async clickPromptConfirm(value) {
      this.promptVisible = false;
      let res = await api.updateUserInfo({ [this.key]: value });
      helper.toast(res.msg);
      if (res.code == 1) {
        this.userinfo[this.key] = value;
      }
    },
    async getUserInfo() {
      let res = await api.getUserInfo();
      this.userinfo = res.data;
    }
  }
};
</script>

<style lang="scss" scoped>
.index {
  border-top: 1px solid #e6e6e6;
  padding: 20upx 5%;
}
.input {
  display: flex;
  align-items: center;
  font-size: 28upx;
  padding: 30upx 0;
  border-bottom: 1px solid #eeeeee;
  .left {
    width: 20%;
  }
  .icon {
    width: 20upx;
    text-align: right;
    color: #b5b5b5;
    font-size: 28upx;
  }
  .right {
    width: 80%;
    text-align: right;
    color: #555555;
    image {
      width: 50upx;
      height: 50upx;
      border-radius: 50%;
    }
  }
}
</style>
