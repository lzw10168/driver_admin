<template>
  <view class="index">
    <view class="search">
      <view class="item">
        <text class="icon">&#xe628;</text>
        <input type="text" v-model="map.keywords" confirm-type="search" @confirm="search" placeholder="输入文章标题或用户昵称" placeholder-class="inupt_p" />
      </view>
      <text class="icon" @tap="navigateTo('/pages/forum/addForum', true)">&#xe60e;</text>
    </view>

    <view class="new" v-for="(v, i) in info.data" @tap="navigateTo('/pages/forum/articleDetails?message_id=' + v.id)" :key="i">
      <view class="title">{{ v.title }}</view>
      <view class="user">
        <image :src="v.user.avatar" mode=""></image>
        <text class="t1">{{ v.user.nickname }}</text>
        <text class="t2">{{ v.createtime | date }}</text>
      </view>
      <view class="info">{{ v.content }}</view>
      <view class="images" v-if="v.images"><image mode="widthFix" v-for="(val, index) in v.images" :src="host + val" :key="index"></image></view>
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
      map: {
        keywords: '',
        page: 1
      },
      messageSwitch: false,
      info: {
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
  onShow() {
    this.getList();
  },
  methods: {
    search() {
      this.map.page = 1;
      this.getList();
    },
    async getList() {
      let res = await api.getMessageList(this.map);
      if (this.map.page == 1) {
        this.info = res.data;
      } else {
        for (let i in res.data.data) {
          this.info.data.push(res.data.data[i]);
        }
      }
      uni.stopPullDownRefresh();
    },
    onPullDownRefresh() {
      this.map.page = 1;
      this.getList();
    },
    onReachBottom() {
      if (this.info.last_page > this.info.current_page) {
        this.getList();
      }
    },
    navigateTo(url, isAuth) {
      if (isAuth && !helper.hasLogin()) {
        uni.navigateTo({
          url: '../login/login?url=' + url
        });
        return false;
      }
      uni.navigateTo({
        url: url
      });
    }
  }
};
</script>

<style lang="scss" scoped>
.icon {
  font-size: 32upx;
}
.index {
  background-color: #f6f6f6;
}
.search {
  width: 100%;
  padding: 0 5%;
  background-color: #ffffff;
  display: flex;
  padding: 30upx 0;
  align-items: center;
  justify-content: center;
  input{
    width: 100%;
  }
  .item {
    display: flex;
    width: 76%;
    align-items: center;
    background-color: #f7f7f7;
    border: 1px solid #e8e8e8;
    border-radius: 40upx;
    padding: 10upx;
    padding-left: 20upx;
    .icon {
      margin-right: 10upx;
    }
    .inupt_p {
      color: #101010;
      font-size: 28upx;
    }
  }
  text {
    width: 10%;
    text-align: center;
  }
}
.new {
  padding: 30upx 5%;
  background-color: #ffffff;
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
  .info {
    font-size: 22upx;
    color: #555555;
    line-height: 48upx;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    overflow: hidden;
    text-overflow: ellipsis;
    -webkit-box-orient: vertical;
  }
}
.images {
  text-align: center;
  image {
    max-width: 100%;
  }
}
</style>
