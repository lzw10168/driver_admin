<template>
  <view>
    <view class="input">
      <text>姓名</text>
      <input type="text" v-model="info.name" placeholder="请输入姓名" />
    </view>
    <view class="input">
      <text>手机号</text>
      <input type="number" v-model="info.mobile" placeholder="请输入手机号" />
    </view>
    <view class="input">
      <text>驾龄</text>
      <input type="number" v-model="info.driving_age" placeholder="请输入驾龄(年)" />
    </view>
    <view class="input">
      <text>身份证号</text>
      <input type="idcard" v-model="info.card_id" placeholder="请输入身份证" placeholder-class="input-placeholder" />
    </view>
    <view class="input">
      <text>上传身份证照片</text>
      <view class="img">
        <image v-if="!info.card_image" src="../../static/sfz1.png" @tap="uploadImage('card_image')" mode=""></image>
        <image v-if="info.card_image" :src="host + info.card_image" @tap="uploadImage('card_image')" mode=""></image>
        <image v-if="!info.card_back_image" src="../../static/sfz2.png" @tap="uploadImage('card_back_image')" mode=""></image>
        <image v-if="info.card_back_image" :src="host + info.card_back_image" @tap="uploadImage('card_back_image')" mode=""></image>
      </view>
    </view>
    <view class="input">
      <text>驾驶照</text>
      <view class="img">
        <image v-if="!info.driver_image" src="../../static/jsz.png" @tap="uploadImage('driver_image')" mode=""></image>
        <image v-if="info.driver_image" :src="host + info.driver_image" mode="" @tap="uploadImage('driver_image')"></image>
      </view>
    </view>
    <view class="input" style="border: none;">
      <text>个人照片</text>
      <view class="img">
        <image v-if="!info.image" src="../../static/zp.png" @tap="uploadImage('image')" mode=""></image>
        <image v-if="info.image" :src="host + info.image" mode="" @tap="uploadImage('image')"></image>
      </view>
    </view>
    <button class="button" v-if="!info.id" type="primary" @tap="post">立即申请</button>
    <view v-if="info.status == '0'" class="desc">审核中</view>
    <view v-if="info.status == '-1'" class="desc">审核未通过，请修改信息后重新提交{{ info.remark ? '。审核备注：' + info.remark : '' }}</view>
    <button class="button" v-if="info.status == '-1'" type="primary" @tap="post">重新提交</button>
  </view>
</template>

<script>
import helper from '../../common/helper.js';
import api from '../../common/api.js';
export default {
  data() {
    return {
      host: helper.host,
      info: {
        card_image: '',
        card_back_image: '',
        driver_image: '',
        image:''
      }
    };
  },
  onLoad() {
    this.getInfo();
  },
  methods: {
    async getInfo() {
      let res = await api.getApplyInfo();
      if (res.data && res.data.id) {
        this.info = res.data;
      }
    },
    async post() {
      if (!this.info.name) {
        helper.toast('请输入姓名');
        return false;
      }
      if (!this.info.card_id) {
        helper.toast('请输入身份证号');
        return false;
      }
      if (!this.info.card_image) {
        helper.toast('请上传身份证正面照片');
        return false;
      }
      if (!this.info.card_back_image) {
        helper.toast('请上传身份证背面照片');
        return false;
      }
      if (!this.info.driver_image) {
        helper.toast('请上传驾驶证');
        return false;
      }
      let res = await api.addApply(this.info);
      if (res.code == 1) {
        uni.showModal({
          title: '提示',
          content: res.msg,
          success: res => {
            if (res.confirm) {
              uni.navigateBack({
                url: '/pages/mine/mine'
              });
            }
          }
        });
      } else {
        helper.toast(res.msg);
      }
    },
    removeImage(key) {
      this.info[key] = '';
    },
    uploadImage(key) {
      uni.chooseImage({
        success: async chooseImageRes => {
          let res = await api.upload(chooseImageRes.tempFilePaths[0]);
          if (res.code != 1) {
            helper.toast(res.msg);
            return false;
          }
          this.info[key] = res.data.url
          helper.toast(res.msg);
        }
      });
    }
  }
};
</script>

<style lang="scss">
.desc {
  text-align: center;
  font-size: 26upx;
  color: #dd524d;
  margin-top: 20upx;
  padding: 20upx 5%;
}
.input {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  padding: 20upx 5%;
  border-bottom: 1px solid #eeeeee;
  text {
    width: 30%;
    font-size: 28upx;
    font-weight: 800;
  }
  input {
    width: 70%;
    font-size: 28upx;
  }
  .input-placeholder {
    font-size: 28upx;
  }
  .img {
    width: 100%;
    margin: 40upx 0 30upx 0;
    display: flex;
    image {
      width: 48%;
      height: 166upx;
      margin-right: 4%;
    }
  }
}
.button {
  border-radius: 0px;
  background-color: #32c45e;
  font-size: 28upx;
  width: 90%;
}
</style>
