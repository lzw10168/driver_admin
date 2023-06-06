<template>
  <view>
    <view class="new">
      <view class="title">{{ info.title }}</view>
      <view class="user">
        <image :src="info.user.avatar" mode=""></image>
        <text class="t1">{{ info.user.nickname }}</text>
        <text class="t2">{{ info.createtime | date }}</text>
      </view>
    </view>
    <view class="info" v-html="info.content"></view>
    <view class="images" v-if="info.images"><image mode="widthFix" v-for="(v, i) in info.images" :src="host + v" :key="i"></image></view>

    <view>
      <view class="more"><text class="t1">评论</text></view>

      <view class="pinlun" v-for="(v, i) in comments.data" v-if="comments.total > 0" :key="i">
        <view class="user">
          <image :src="v.user.avatar" mode=""></image>
          <text class="t1">{{ v.user.nickname }}</text>
        </view>
        <view class="p_info">{{ v.comment }}</view>
      </view>

      <view class="readmore" @tap="more" v-if="comments.last_page > map.page">查看更多</view>

      <view class="empty" v-if="comments.total == 0">暂无评论</view>

      <view class="add_p">
        <image src="../../static/imgs/st_pic.png" mode=""></image>
        <input type="text" confirm-type="send" @confirm="sendComment" v-model="comment" placeholder="添加评论..." />
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
      host: helper.host,
      message_id: '',
      info: {
        user: {}
      },
      map: {
        pageSize: 2,
        page: 1
      },
      comment: '',
      comments: {
        data: []
      }
    };
  },
  filters: {
    date: value => {
      let date = new Date(value * 1000);
      let y = date.getFullYear();
      let m = date.getMonth() + 1;
      let d = date.getDate();
      return y + '-' + (m > 9 ? m : '0' + m) + '-' + (d > 9 ? d : '0' + d);
    }
  },
  onLoad(option) {
    this.message_id = option.message_id;
    this.map.message_id = this.message_id;
    this.getInfo();
    this.getComment();
  },
  methods: {
    async getInfo() {
      let res = await api.getMessageInfo(this.message_id);
      if (res.data.images) {
        res.data.images = res.data.images.split(',');
      }
      this.info = res.data;
    },
    async getComment() {
      let res = await api.getMessageComments(this.map);
      if (this.map.page == 1) {
        this.comments = res.data;
      } else {
        this.comments.last_page = res.data.last_page;
        for (let i in res.data.data) {
          this.comments.data.push(res.data.data[i]);
        }
      }
    },
    more() {
      this.map.page++;
      this.getComment();
    },
    async sendComment() {
      if (!this.comment) {
        return false;
      }
      if (!helper.hasLogin()) {
        uni.navigateTo({
          url: '../login/login?url=/pages/forum/articleDetails?message_id=' + this.message_id
        });
        return false;
      }
      let res = await api.addMessageComment(this.message_id, this.comment);
      helper.toast(res.msg);
      if (res.code == 1) {
        this.comment = '';
        uni.hideKeyboard();
        this.map.page = 1;
        this.getComment();
      }
    }
  }
};
</script>

<style lang="scss" scoped>
.images {
  text-align: center;
  padding: 20upx;
  image {
    max-width: 100%;
    width: 100%;
  }
}
.readmore {
  text-align: center;
  font-size: 26upx;
}
.new {
  padding: 30upx 5%;
  background-color: #ffffff;
  border-bottom: 20upx solid #f6f6f6;
  margin-bottom: 20upx;
  .title {
    font-size: 32upx;
    font-weight: 800;
  }
  .user {
    display: flex;
    padding: 22upx 0;
    align-items: center;
    image {
      width: 60upx;
      height: 60upx;
      border-radius: 50%;
    }
    .t1 {
      margin-left: 20upx;
      font-size: 28upx;
      color: #666666;
	  font-weight: 800;
    }
    .t2 {
      margin-left: 20upx;
      font-size: 22upx;
      color: #999999;
    }
  }
}
.info {
  padding: 30upx 5%;
  font-size: 22upx;
  color: #101010;
  color: #555555;
  line-height: 48rpx;
}
.more {
  padding: 20upx 5%;
  display: flex;
  justify-content: space-between;
  .t1 {
    font-size: 32upx;
	font-weight: 800;
  }
  .t2 {
    font-size: 28upx;
  }
}
.empty {
  text-align: center;
  font-size: 26upx;
  height: 100upx;
  line-height: 100upx;
  color: #3a3a3a;
}
.pinlun {
  padding: 20upx 5%;
  .user {
    display: flex;
    padding: 22upx 0;
    align-items: center;
    image {
      width: 60upx;
      height: 60upx;
      border-radius: 50%;
    }
    .t1 {
      margin-left: 20upx;
      font-size: 28upx;
	  font-weight: 800;
      color: #666666;
    }
  }
  .p_info {
    text-indent: 2em;
    font-size: 22upx;
    width: 100%;
  }
}
.add_p {
  display: flex;
  align-items: center;
  padding: 20upx 5%;
  image {
    width: 60upx;
    height: 60upx;
    border-radius: 50%;
  }
  input {
    width: 70%;
    margin-left: 20upx;
    border: 1px #eaeaea solid;
    border-radius: 50upx;
    font-size: 24upx;
    padding: 10upx 30upx;
  }
}
</style>
