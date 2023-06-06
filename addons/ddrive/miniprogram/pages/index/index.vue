<template>
  <view class="content">
    <!--轮播图-->
    <uni-swiper-dot :info="banner" field="content">
      <swiper class="swiper-box">
        <swiper-item v-for="(item, index) in banner" :key="index">
          <view class="swiper-item"><image :src="item.image" mode="aspectFill" /></view>
        </swiper-item>
      </swiper>
    </uni-swiper-dot>
    <!--轮播图-->

    <view class="taxi" v-if="order.total == 0">
      <view class="top">
        <text class="icon">&#xe6e2;</text>
        <text class="t1">呼叫代驾</text>
      </view>
      <view class="input" style="border-bottom: 1px solid #F0F0F0;">
        <text class="icon">&#xe608;</text>
		<!-- 修改颜色 -->
        <input type="text"  style="color: #4D4D4D;" @tap="navigateTo('../taxiService/taxiService?action=call&type=start')" placeholder="当前位置" disabled="true" :value="currAddress.formatted_addresses.recommend" />
      </view>
      <view class="input">
        <text class="icon color_2">&#xe608;</text>
        <input type="text" placeholder="你要去哪" @tap="navigateTo('../taxiService/taxiService?action=call&type=end')" disabled="true" />
      </view>
    </view>

    <view class="orders" v-if="order.total > 0" v-for="(v, i) in order.data" :key="i" @tap="navigateTo('../taxiService/taxiService?action=order&order_id=' + v.id)">
      <view class="top">
        <text class="icon">&#xe616;</text>
        <text class="t1">进行中的订单</text>
        <text class="more">查看详情</text>
      </view>
      <view class="left">
        <view class="input">
          <text class="icon color_1">&#xe77c;</text>
          <text class="time">{{ v.createtime | date }}</text>
        </view>
        <view class="input">
          <text class="icon">&#xe608;</text>
          <text class="address">{{ v.start }}</text>
        </view>
        <view class="input">
          <text class="icon color_2">&#xe608;</text>
          <text class="address">{{ v.end }}</text>
        </view>
      </view>
      <view class="right">{{ v.status_text }}</view>
    </view>
  </view>
</template>

<script>
import QQMapWX from '../../libs/qqmap-wx-jssdk/qqmap-wx-jssdk.js';
import uniSwiperDot from '@/components/uni-swiper-dot/uni-swiper-dot.vue';
import helper from '../../common/helper.js';
import api from '../../common/api.js';
export default {
  components: {
    uniSwiperDot
  },
  data() {
    return {
      banner: [],
      host: helper.host,
      qqmapsdk: {},
      currLocation: {},
      currAddress: {},
      suggestion: [],
      region: '',
      order: {
        total: 0,
        data: []
      }
    };
  },
  onShow() {
    this.initSdk();
    this.getBanner();
    this.getLocation();
    if (helper.hasLogin()) {
      this.getOrderList();
    }
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
    async getOrderList() {
      let res = await api.getOrderList({ status: '0,1,2,3,4' });
      this.order = res.data;
    },
    openPopup(key) {
      let popup = key || 'popup';
      this.$refs['popup'].open();
    },
    closePopup(key) {
      let popup = key || 'popup';
      this.$refs['popup'].close();
    },
    initSdk() {
      this.qqmapsdk = new QQMapWX({
        key: helper.config.qqmapsdk.key
      });
    },
    getLocation() {
      uni.getLocation({
        type: 'gcj02',
        success: res => {
          console.log(res);
          this.currLocation = res;
          this.qqmapsdk.reverseGeocoder({
            location: {
              latitude: res.latitude,
              longitude: res.longitude
            },
            complete: res => {
              console.log(res);
              if (res.status == 0) {
                this.currAddress = res.result;
              }
            }
          });
        }
      });
    },
    async getBanner() {
      const res = await api.getBanner();
      const banner=[];
      for(let i in res.data){
        if(res.data[i].image.indexOf('://')===-1){
          res.data[i].image=this.host+res.data[i].image;
        }
        banner.push(res.data[i]);
      }
      this.banner = banner;
    },
    navigateTo(url) {
      if (!helper.hasLogin()) {
        uni.navigateTo({
          url: '../login/login?redirectTo=1&url=' + encodeURIComponent(url)
        });
        return false;
      } else {
        uni.navigateTo({
          url: url
        });
      }
    }
  }
};
</script>

<style lang="scss" scoped>
.color_1 {
  color: #979797 !important;
}
.color_2 {
  color: #ed965f !important;
}
.icon {
  text-align: center;
  font-size: 56upx;
  color: #32c45e;
}
.content {
  width: 100%;
  height: 100%;
  background-color: #fbfbfb;
}
.swiper-box {
  height: 430upx;
  .swiper-item image {
    width: 100%;
  }
}
.taxi {
  margin-top: 40upx;
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding-bottom: 20upx;
  border-bottom: 2px #eaeaea solid;
  background-color: #ffffff;
  .top {
    display: flex;
    align-items: center;
    width: 90%;
    padding: 40upx 0;
    .icon {
      width: 10%;
    }
    .t1 {
      font-size: 34upx;
      width: 80%;
    }
    .more {
      width: 20%;
      text-align: right;
      font-size: 22upx;
      float: right;
    }
  }
  .input {
    width: 90%;
    padding: 20upx 0;
    display: flex;
    align-items: center;
	/* 修改颜色*/
	.text{
		color: #4D4D4D;
	}
	/* 修改颜色 */
    text {
      width: 10%;
    }
    input {
      width: 90%;
    }
  }
}
.orders {
  width: 100%;
  margin-bottom: 30upx;
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  align-items: center;
  justify-content: center;
  border-bottom: 2px #eaeaea solid;
  background-color: #ffffff;
  .top {
    display: flex;
    align-items: center;
    width: 100%;
    padding: 40upx;
    background-color: #fbfbfb;
    text {
      width: 10%;
    }
    .t1 {
      font-size: 34upx;
      width: 80%;
    }
    .more {
      width: 20%;
      text-align: right;
      font-size: 22upx;
      float: right;
    }
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
    text-align: left;
    color: #32c45e;
    font-size: 28upx;
  }
}
</style>
