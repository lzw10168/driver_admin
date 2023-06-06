<template>
  <view>
    <map
      v-if="order.status == 2 || order.status == 1 || order.status == 4"
      class="map"
      :latitude="map.latitude"
      :longitude="map.longitude"
      :scale="map.scale"
      :polyline="map.polyline"
      :markers="map.covers"
    ></map>
    <view class="new" v-if="order.status == 1">
      <view class="user">
        <image :src="order.user.avatar" mode=""></image>
        <view class="name">
          <text class="item1">{{ order.user.nickname }}</text>
        </view>
        <view class="phone" @tap="call(order.user.mobile)">
          <text class="icon">&#xe657;</text>
          {{ order.user.mobile }}
        </view>
      </view>
      <view class="money">
        <text>预计费用{{ order.estimated_price }}元</text>
      </view>
      <button class="start" type="primary" @tap="reach">我已到达，等待客户</button>
    </view>
    <view class="new" v-if="order.status == 4">
      <view class="user">
        <image :src="order.user.avatar" mode=""></image>
        <view class="name">
          <text class="item1">{{ order.user.nickname }}</text>
        </view>
        <view class="phone" @tap="call(order.user.mobile)">
          <text class="icon">&#xe657;</text>
          {{ order.user.mobile }}
        </view>
      </view>
      <view class="money">
        <text>预计费用{{ order.estimated_price }}元</text>
      </view>
      <button class="start" type="primary" @tap="start">立即出发</button>
    </view>
    <view class="processing" v-if="order.status == 2">
      <view class="user">
        <view class="item">实时费用{{ actual.price }}元</view>
        <view class="item1">已行驶{{ actual.distance | kilometer }} km，预计还需要{{ route.duration }}分钟到达</view>
      </view>
      <button type="primary" @tap="done">已到达终点</button>
    </view>
    <view class="done" v-if="order.status == -1 || order.status == 3 || order.status == 99">
      <!--待支付或已完成-->
      <view class="index">
        <view class="top">
          <view class="user">
            <image :src="order.user.avatar" mode=""></image>
            <text>{{ order.user.nickname }}</text>
          </view>
          <text>{{ order.end_datetime }}</text>
        </view>
        <view class="center">
          <view class="left">
            <view class="input">
              <text class="icon green">&#xe608;</text>
              <text class="gray">从</text>
              {{ order.start_address }}
            </view>
            <view class="input">
              <text class="icon orange">&#xe608;</text>
              <text class="gray">到</text>
              {{ order.end_address }}
            </view>
          </view>
          <!--          <text class="right icon gray">&#xe60f;</text> -->
        </view>
        <view class="money" v-if="order.status != -1">
          <view class="t1">
            {{ order.price }}
            <text class="yuan">元</text>
          </view>
          <text class="t2">{{ order.distance | kilometer }}公里 {{ order.duration | duration }}</text>
          <text class="t3">{{ order.status_text }}</text>
        </view>
        <view class="qrimg"><tki-qrcode ref="qrcode" :onval="true" :size="400" :val="paystr" /></view>
      </view>
    </view>
  </view>
</template>

<script>
import QQMapWX from '../../libs/qqmap-wx-jssdk/qqmap-wx-jssdk.js';
import tkiQrcode from '@/components/tki-qrcode/tki-qrcode.vue';
import helper from '../../common/helper.js';
import api from '../../common/api.js';
export default {
  components: { tkiQrcode },
  data() {
    return {
      map: {
        latitude: '',
        longitude: '',
        covers: [],
        scale: 16,
        polyline: []
      },
      focus: false,
      price: '',
      order: {},
      orderId: '',
      timer: '',
      isDirection: false,
      actual: {
        price: 0,
        distance: 0
      },
      route: {
        duration: '--'
      },
      paystr: ''
    };
  },
  filters: {
    kilometer: value => {
      let distance = parseFloat(value / 1000);
      return distance.toFixed(2);
    },
    duration: value => {
      if (value > 3600) {
        let h = parseInt(value / 3600);
        let s = value % 3600;
        if (s > 0) {
          return h + '小时' + parseInt(s / 60) + '分钟';
        } else {
          return h + '小时';
        }
      } else {
        return parseInt(value / 60) + '分钟';
      }
    }
  },
  onLoad(option) {
    this.initSdk();
    this.orderId = option.order_id;
    this.getOrderInfo();
  },
  onShow() {
    // 如果存在订单则更新订单信息
    if (this.orderId) {
      this.getOrderInfo();
    }
  },
  onHide() {
    // 隐藏时关闭定时器
    if (this.timer) {
      console.log('清除定时器');
      clearInterval(this.timer);
      this.timer = '';
    }
  },
  methods: {
    call(mobile) {
      uni.makePhoneCall({
        phoneNumber: mobile
      });
    },
    reach() {
      uni.showModal({
        title: '操作提示',
        content: '请确认已到达出发地点等待用户',
        success: async e => {
          if (e.confirm) {
            let res = await api.orderReach(this.orderId);
            if (res.code == 1) {
              this.getOrderInfo();
            } else {
              helper.toast(res.msg);
            }
          }
        }
      });
    },
    done() {
      uni.showModal({
        title: '操作提示',
        content: '请确认已经达到目的地',
        success: async e => {
          if (e.confirm) {
            uni.getLocation({
              type: 'gcj02',
              success: async ret => {
                console.log(2);
                // 更新一次结束位置
                await this.updateOrderInfo();
                // 结束订单
                let res = await api.doneOrder({
                  order_id: this.orderId,
                  latitude: ret.latitude,
                  longitude: ret.longitude
                });
                // 更新订单信息
                this.getOrderInfo();
              },
              fail: e => {
                helper.toast('获取位置失败');
              }
            });
          }
        }
      });
    },
    start() {
      uni.showModal({
        title: '操作提示',
        content: '请确认已接到用户并马上出发吗？',
        success: async e => {
          if (e.confirm) {
            console.log(1);
            uni.getLocation({
              type: 'gcj02',
              success: async ret => {
                console.log(2);
                // 当前地址
                let res = await api.startOrder({
                  order_id: this.orderId,
                  latitude: ret.latitude,
                  longitude: ret.longitude
                });
                this.getOrderInfo();
              },
              fail: e => {
                helper.toast('获取位置失败');
              }
            });
          }
        }
      });
    },
    initSdk() {
      this.qqmapsdk = new QQMapWX({
        key: helper.config.qqmapsdk.key
      });
    },
    async getOrderPayScanInfo() {
      let res = await api.getOrderPayScanInfo(this.orderId);
      this.paystr = res;
      console.log(this.paystr);
    },
    async getOrderInfo() {
      console.log('获取订单信息');
      let res = await api.getOrderInfo(this.orderId);
      this.order = res.data;
      // 如果订单未开始，以用户位置未起始点
      if (this.order.status == 1 || this.order.status == 4) {
        this.map.latitude = res.data.start_latitude;
        this.map.longitude = res.data.start_longitude;
        // 设置用户坐标点
        this.map.covers.push({
          latitude: res.data.start_latitude,
          longitude: res.data.start_longitude,
          iconPath: '../../static/marker.png'
        });
      }
      // 如果订单为进行中，则实时更新费用及到达时间
      if (this.order.status == 2) {
        this.updateOrderInfo();
      }
      // 获取支付订单信息
      if (this.order.status == 3 && !this.paystr) {
        this.getOrderPayScanInfo();
      }
      // 如果订单为未完成状态，则定时更新
      if (this.order.status != 99 && !this.timer) {
        console.log('注册定时器');
        this.timer = setInterval(() => {
          this.getOrderInfo();
        }, 10 * 1000);
      }
      // 如果订单状态为已完成，切存在定时器，则关闭
      if (this.order.status == 99 && this.timer) {
        console.log('清除定时器');
        clearInterval(this.timer);
        this.timer = '';
      }
    },
    updateOrderInfo() {
      // 更新坐标信息
      uni.getLocation({
        type: 'gcj02',
        success: async res => {
          // 当前地址
          this.currLocation = res;
          this.map.latitude = res.latitude;
          this.map.longitude = res.longitude;
          // 设置当前坐标点
          this.map.covers = [
            {
              latitude: res.latitude,
              longitude: res.longitude,
              iconPath: '../../static/marker.png'
            }
          ];
          // 更新用户位置
          res.order_id = this.orderId;
          let data = await api.updateOrderLocation(res);
          this.actual = data.data;
          this.direction();
        }
      });
    },
    // 路径规划
    direction() {
      this.qqmapsdk.direction({
        mode: 'driving',
        from: this.currLocation.latitude + ',' + this.currLocation.longitude,
        to: this.order.end_latitude + ',' + this.order.end_longitude,
        complete: res => {
          if (!res.result || !res.result.routes || res.result.routes.length == 0) {
            helper.toast('未获取到有效路线');
            return false;
          }
          let coors = res.result.routes[0].polyline,
            pl = []; //坐标解压（返回的点串坐标，通过前向差分进行压缩）
          this.route = res.result.routes[0];
          let kr = 1000000;
          // 处理坐标
          for (let i = 2; i < coors.length; i++) {
            coors[i] = Number(coors[i - 2]) + Number(coors[i]) / kr;
          }
          for (let i = 0; i < coors.length; i += 2) {
            pl.push({ latitude: coors[i], longitude: coors[i + 1] });
          }
          this.map.latitude = pl[0].latitude;
          this.map.longitude = pl[0].longitude;
          this.map.polyline = [
            {
              points: pl,
              color: '#227AFF',
              width: 4
            }
          ];
          this.isDirection = true;
        }
      });
    }
  }
};
</script>

<style lang="scss" scoped>
.map {
  width: 100%;
  height: calc(100vh - 400upx);
}
.processing {
  position: absolute;
  width: 100%;
  bottom: 0;
  text-align: center;
  .user {
    margin-bottom: 10%;
    .item {
      margin-bottom: 10upx;
      font-size: 28upx;
      font-weight: 800;
    }
    .item1 {
      font-size: 24upx;
      color: #d2d2d2;
    }
  }
  button {
    border-radius: 0;
    background-color: #32c45e;
    font-size: 28upx;
  }
}
.new {
  width: 100%;
  height: 100%;
  .start {
    width: 100%;
    border-radius: 0px;
    background-color: #32c45e;
    border: none;
    position: absolute;
    bottom: 0;
  }
  .start:after {
    border: none;
  }
  .user {
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 28upx;
    image {
      width: 80upx;
      height: 80upx;
      border-radius: 50%;
    }
    .name {
      width: 40%;
      margin-left: 20upx;
      .item2 {
        font-size: 20upx;
      }
    }
    .phone {
      width: 40%;
      text-align: right;
    }
  }
  .money {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    font-size: 22upx;
    height: 200upx;
    color: #d2d2d2;
    text {
      margin-bottom: 10upx;
      color: #101010;
      font-size: 30upx;
    }
  }
}
.done {
  height: 100%;
  padding: 40upx;
  .top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #dfdfdf;
    font-size: 26upx;
    padding: 20upx;
    .user {
      display: flex;
      align-items: center;
      image {
        width: 100upx;
        height: 100upx;
        border-radius: 50%;
        margin-right: 20upx;
      }
    }
  }

  .center {
    padding: 30upx 20upx;
    border-bottom: 1px solid #dfdfdf;
    display: flex;
    align-items: center;
    justify-content: space-between;
    .input {
      font-size: 28upx;
      display: flex;
      align-items: center;
      .gray {
        margin: 20upx;
      }
    }
  }
  .money {
    display: flex;
    margin-top: 10%;
    width: 100%;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    .t1 {
      font-size: 60upx;
      .yuan {
        font-size: 28upx;
      }
    }
    .t2 {
      font-size: 24upx;
      margin-top: 20upx;
    }
    .t3 {
      font-size: 28upx;
      margin-top: 5%;
    }
  }
}
.qrimg {
  text-align: center;
  padding: 40upx;
  height: 400upx;
}
</style>
