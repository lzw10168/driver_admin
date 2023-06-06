<template>
  <view class="index">
    <view class="nav">
      <text class="t1">取消</text>
      <text class="t2" @tap="post">发布话题</text>
    </view>
    <view class="content">
      <view class="item">
        <input
          class="input1"
          v-model="info.title"
          type="text"
          value=""
          placeholder="#输入您想创建的新话题#"
          placeholder-style="color: #999999;font-weight: 800;font-size: 30upx;"
        />
      </view>
      <view class="item"><textarea maxlength="-1" placeholder="对话题补充说明，可以获得更多解答" v-model="info.content" placeholder-style="color: #CCCCCC;" /></view>
    </view>

    <view class="img">
      <image :src="host + v" @tap="remove(i)" mode="" v-for="(v, i) in info.images" :key="i"></image>
      <image src="../../static/add.png" @tap="uploadImage" mode=""></image>
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
      info: {
        images: []
      },
      msg: ''
    };
  },
  methods: {
    remove(index) {
      uni.showModal({
        title: '操作提示',
        content: '确认要移除该图片吗',
        success: res => {
          if (res.confirm) {
            this.info.images.splice(this.index, 1);
          }
        }
      });
    },
    async post() {
      if (!this.info.title) {
        helper.toast('请输入话题标题');
        return false;
      }
      if (!this.info.content) {
        this.info.content = '';
      }
      let info = {
        title: this.info.title,
        content: this.info.content,
        images: this.info.images.join(',')
      };
      let res = await api.addMessage(info);
      helper.toast(res.msg);
      if (res.code == 1) {
        setTimeout(() => {
          uni.navigateBack({
            url: '/pages/forum/forum'
          });
        }, 1000);
      }
    },
    uploadImage() {
      uni.chooseImage({
        success: chooseImageRes => {
          const tempFilePaths = chooseImageRes.tempFilePaths;
          uni.uploadFile({
            url: helper.host + '/api/common/upload',
            filePath: tempFilePaths[0],
            name: 'file',
            formData: {
              token: helper.getUserToken()
            },
            success: uploadFileRes => {
              let res = JSON.parse(uploadFileRes.data);
              helper.toast(res.msg);
              if (res.code == 1) {
                this.info.images.push(res.data.url);
              }
            }
          });
        }
      });
    }
  }
};
</script>

<style lang="scss" scoped>
.pl {
  color: #999999;
  font-weight: 800;
}

.nav {
  padding: 30upx;
  display: flex;
  justify-content: space-between;
  .t1 {
    color: #dbdbdb;
    font-size: 30upx;
  }
  .t2 {
    color: #67ca58;
    font-size: 30upx;
  }
}
.item {
  border-bottom: 1px solid #eeeeee;
  padding: 30upx;
}
textarea {
  font-size: 26upx;
}
input {
  padding: 20upx 0;
}
// .pl {
//   color: #999999;
//   font-size: 28upx;
//   font-weight: 800;
// }
// .pl2 {
//   color: #cccccc;
//   font-size: 28upx;
// }
.img {
  padding: 30upx;
  image {
    width: 100upx;
    height: 100upx;
    margin-right: 20upx;
  }
}
</style>
