<template>
  <view>
    <view class="top">
      <view class="title">
        <text></text>
        请选择你的意见类型
      </view>
      <view class="content">
        <radio-group @change="radioChange">
          <label class="radio" color="#000000" v-for="(item, index) in list" :key="item.value">
            <radio color="#4CD964" style="transform:scale(0.7)" :value="item.name" />
            <text>{{ item.name }}</text>
          </label>
        </radio-group>
      </view>
      <view class="textarea"><textarea v-model="info.content" placeholder="请详细描述你的问题" class="text" placeholder-class="pl" /></view>
      <button type="primary" class="button" @tap="post">立即提交</button>
    </view>
  </view>
</template>

<script>
import helper from '../../common/helper.js';
import api from '../../common/api.js';
export default {
  data() {
    return {
      list: [{
        name:'客户体验差',
      },
      {
        name:'路线不清楚',
      },
      {
        name:'广告太多',
      },
      {
        name:'找不到需要的',
      },
      {
        name:'推送太频繁',
      },
      {
        name:'其他',
      }],
      info: {
        type: '',
        content: ''
      }
    };
  },
  methods: {
    radioChange(e) {
      this.info.type = e.detail.value;
    },
    async post() {
      if (!this.info.type) {
        helper.toast('请选择意见类型');
        return false;
      }
      if (!this.info.content) {
        helper.toast('请详细描述问题');
        return false;
      }
      let res = await api.addFeedback(this.info);
      if (res.code == 1) {
        uni.showModal({
          title: '提示',
          content: res.msg,
          success: res => {
            if (res.confirm) {
              uni.navigateBack({
                url:'/pages/mine/setting'
              })
            }
          }
        });
      } else {
        helper.toast(res.msg);
      }
    }
  }
};
</script>

<style lang="scss" scoped>
.top {
  border-top: 10upx solid #f7f8fa;
  padding: 20upx 5%;
  font-size: 34upx;
  .title {
    font-weight: 800;
    text {
      margin-right: 10upx;
      border: 2px solid #32c45e;
    }
  }
}
.content {
  margin-top: 20upx;
}
.textarea {
  padding: 20upx;
}
.radio {
  display: block;
  padding: 20upx;
  font-size: 28upx;
}
.text {
  width: 93%;
  padding: 20upx;
  background-color: #f7f8fa;
  border-radius: 10upx;
  font-size: 26upx;
}
.pl {
  font-size: 24upx;
}
.button {
  width: 60%;
  border-radius: 50upx;
  margin-top: 40upx;
  background-color: #32c45e;
}
</style>
