<template>
  <view class="index">
    <view class="nav">
      <text class="t2" :class="{ t1: map.status == 'all' }" @tap="setMap('status', 'all')">全部</text>
      <text class="t2" :class="{ t1: map.status == 2 }" @tap="setMap('status', 2)">进行中</text>
      <text class="t2" :class="{ t1: map.status == 3 }" @tap="setMap('status', 3)">待支付</text>
      <text class="t2" :class="{ t1: map.status == 99 }" @tap="setMap('status', 99)">已完成</text>
    </view>
    <view class="center">
      <view class="orders" v-if="order.total > 0" v-for="(v, i) in order.data" :key="i" @tap="navigateTo('/pages/taxiService/taxiService?action=order&order_id=' + v.id)">
        <view class="top">
          <view class="input">
            <text class="icon gray">&#xe77c;</text>
            <text class="time">{{ v.createtime | date }}</text>
          </view>
          <text class="more icon gray">{{ v.status_text }}&#xe60f;</text>
        </view>
        <view class="left">
          <view class="input">
            <text class="icon green">&#xe608;</text>
            <text class="address">{{ v.start }}</text>
          </view>
          <view class="input">
            <text class="icon orange">&#xe608;</text>
            <text class="address">{{ v.end }}</text>
          </view>
        </view>
        <view class="right">￥{{ v.price }}元</view>
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
      map: {
        status: 'all',
        page: 1
      },
      order: {}
    };
  },
  filters: {
    date: value => {
      let date = new Date(value * 1000);
      let h = date.getHours();
      let m = date.getMinutes();
      return (h > 9 ? h : '0' + h) + ':' + (m > 9 ? m : '0' + m);
    }
  },
  onShow() {
    this.getList();
  },
  methods: {
    async getList() {
      let res = await api.getOrderList(this.map);
      this.order = res.data;
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
    navigateTo(url) {
      uni.navigateTo({
        url: url
      });
    },
    setMap(key, value) {
      this.map[key] = value;
      this.map.page = 1;
      this.getList();
    }
  }
};
</script>

<style lang="scss" scoped>
page,
.index {
  height: 100%;
  background-color: #fbfbfb;
}
.center {
  padding: 30upx;
}
.center1 {
  padding: 30upx;
}
.nav {
  display: flex;
  padding: 20upx;
  font-size: 28upx;
  background-color: #ffffff;
  .t1 {
    width: 48%;
    text-align: center;
    color: #32c45e;
  }
  .t2 {
    width: 48%;
    text-align: center;
  }
  .icon {
    font-size: 30upx;
  }
}
.orders {
  margin-bottom: 30upx;
  display: flex;
  padding: 20upx 20upx;
  border-radius: 10upx;
  box-shadow: 1px 1px 1px 1px #eaeaea;
  align-items: center;
  flex-wrap: wrap;
  align-items: center;
  justify-content: center;
  background-color: #ffffff;
  .icon {
    font-size: 48upx;
  }
  .top {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 24upx;
    .input {
      display: flex;
      align-items: center;
      color: #999999;
    }
    .more {
      font-size: 28upx;
    }
  }
  .left {
    width: 80%;
    .input {
      width: 100%;
      padding: 6upx 0;
      display: flex;
      align-items: center;
      .time {
        width: 70%;
        font-size: 25upx;
        color: #979797;
      }
      .address {
        width: 70%;
        font-size: 26upx;
      }
    }
  }
  .right {
    width: 20%;
    font-size: 26upx;
    color: #e51c23;
    button {
      width: 60%;
      background-color: #32c45e;
      margin-top: 20upx;
      border-radius: 50upx;
      padding: 0 10upx;
      font-size: 22upx;
    }
  }
}

.bottom {
  background-color: #ffffff;
  position: absolute;
  width: 100%;
  bottom: 0;
  height: 120upx;
  display: flex;
  align-items: center;
  justify-content: space-around;
  .item {
    display: flex;
    flex-direction: column;
    width: 33.33%;
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
