<template>
  <view class="index">
    <view class="top">
      <text class="t1" :class="{ active: active == 'jd' }" @tap="setActive('jd')">接单</text>
      <text class="t2" :class="{ active: active == 'dj' }" @tap="setActive('dj')">代驾须知</text>
      <text class="icon" @tap="navigateTo('createOrder')">&#xe60e;</text>
    </view>
    <view class="center" v-show="active == 'jd'">
      <view class="orders" v-if="order.total > 0" v-for="(v, i) in order.data" :key="i">
        <view class="left">
          <view class="input">
            <text class="icon gray">&#xe77c;</text>
            <text class="time">{{ v.createtime | date }}</text>
          </view>
          <view class="input">
            <text class="icon green">&#xe608;</text>
            <text class="address">{{ v.start }}</text>
          </view>
          <view class="input">
            <text class="icon orange">&#xe608;</text>
            <text class="address">{{ v.end }}</text>
          </view>
        </view>
        <view class="right">
          <text class="price">￥{{ v.estimated_price }}元</text>
          <button type="primary" @tap="taking(v.id)">立即接单</button>
        </view>
      </view>
      <view class="empty" v-if="order.total == 0">暂无订单</view>
    </view>

    <view class="center1" v-show="active == 'dj'"><rich-text :nodes="setting.notice"></rich-text></view>
    <view class="bottom">
      <view class="item active" @tap="navigateTo('/pages/myDriving/myDriving')">
        <text class="icon">&#xe606;</text>
        首页
      </view>
      <view class="item " @tap="navigateTo('/pages/myDriving/order')">
        <text class="icon">&#xe616;</text>
        订单
      </view>
      <view class="item " @tap="navigateTo('/pages/myDriving/withdraw')">
        <text class="icon">&#xe61c;</text>
        提现
      </view>
    </view>
  </view>
</template>

<script>
import helper from '../../common/helper.js';
import api from '../../common/api.js';
let timer = null;
export default {
  data() {
    return {
      active: 'jd',
      setting: {},
      map: {
        page: 1
      },
      order: {}
    };
  },
  onLoad() {
    this.getInfo();
  },
  onShow() {
    this.getList();
    timer = setInterval(() => {
      this.getList();
    }, 3000);
  },
  onHide() {
    clearInterval(timer);
  },
  filters: {
    date: value => {
      let date = new Date(value * 1000);
      let h = date.getHours();
      let m = date.getMinutes();
      return (h > 9 ? h : '0' + h) + ':' + (m > 9 ? m : '0' + m);
    }
  },
  methods: {
    async taking(orderId) {
      uni.showModal({
        title: '操作提示',
        content: '确定要接单吗？',
        success: async e => {
          if (e.confirm) {
            let res = await api.orderTaking(orderId);
            helper.toast(res.msg);
            if (res.code == 1) {
              uni.navigateTo({
                url: '/pages/myDriving/drivingDetails?order_id=' + orderId
              });
            }
          }
        }
      });
    },
    async getInfo() {
      let res = await api.getSetting();
      this.setting = res.data;
    },
    async getList() {
      let res = await api.getOrderTakingList(this.map);
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
    setActive(active) {
      this.active = active;
    },
    navigateTo(url) {
      uni.navigateTo({
        url: url
      });
    }
  }
};
</script>

<style lang="scss" scoped>
.empty {
  height: 100upx;
  line-height: 100upx;
  font-size: 26upx;
  text-align: center;
}
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
  font-size: 26upx;
  line-height: 48upx;
}
.top {
  display: flex;
  padding: 20upx;
  font-size: 28upx;
  background-color: #ffffff;
  .active {
    color: #32c45e;
  }
  .t1 {
    width: 48%;
    text-align: center;
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
  padding: 20upx 10upx;
  padding-right: 20upx;
  border-radius: 10upx;
  box-shadow: 1px 1px 1px 1px #eaeaea;
  align-items: center;
  flex-wrap: wrap;
  align-items: center;
  justify-content: center;
  background-color: #ffffff;
  .icon {
    text-align: center;
    font-size: 56upx;
  }
  .left {
    width: 80%;
    .input {
      width: 100%;
      padding: 6upx 0;
      display: flex;
      align-items: center;
      text {
        width: 20%;
      }
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
    text-align: right;
    font-size: 34upx;
    color: #e51c23;
    button {
      float: right;
      width: 140upx;
      background-color: #32c45e;
      margin-top: 20upx;
      border-radius: 50upx;
      padding: 0 10upx;
      font-size: 22upx;
    }
    .price {
      float: right;
      position: relative;
      right: 20upx;
      font-size: 28rpx;
      width: 250upx;
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
    align-items: center;
    font-size: 24upx;
    width: 33.33%;
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
