<template>
  <view>
    <map class="map" @tap="onMapTap" :latitude="map.latitude" :longitude="map.longitude" :markers="map.covers" :scale="map.scale" :polyline="map.polyline"></map>

    <!--地址输入-->
    <uni-popup class="popup" ref="popup" type="top">
      <view class="destination">
        <input type="text" v-model="input" :focus="focus" @input="onInputAddress" :placeholder="'请输入' + (selectType == 'end' ? '目的地' : '出发地')" />
        <text @tap="closePopup">取消</text>
      </view>
      <view class="addressHistory" v-for="(v, i) in suggestion" :key="i" @tap="setAddress(v)">
        <text>{{ v.title }}</text>
        {{ v.addr }}
      </view>
    </uni-popup>
    <!--地址输入-->

    <view class="taxi">
      <!-- 等待司机接单 -->
      <view class="order" v-if="status == -1">
        <view class="money">
          <text>预计费用{{ order.estimated_price }}元</text>
          {{ order.start }} - {{ order.end }}
        </view>
        <button class="xiadan" type="primary" disabled>订单已取消</button>
      </view>

      <!--输入位置-->
      <view class="call" v-if="status == 1">
        <view class="input" style="border-bottom: 1px solid #F0F0F0;">
          <text class="icon green">&#xe608;</text>
          <input type="text" :value="currAddress.formatted_addresses.recommend" disabled="disabled" @tap="selectAddress('start')" placeholder="出发位置" />
          <button type="primary" @tap="call">呼叫司机</button>
        </view>
        <view class="input">
          <text class="icon orange">&#xe608;</text>
          <input type="text" :value="toAddress.title" placeholder="你要去哪" disabled="disabled" @tap="selectAddress('end')" />
        </view>
        <view class="input">
          <text class="icon blue">&#xe608;</text>
          <input type="mobile" v-model="mobile" placeholder="联系电话(不填则使用注册手机号)" />
        </view>
      </view>

      <!-- 预估车费 -->
      <view class="theFare" v-if="status == 2">
        <view class="money">
          <text>预计费用{{ price }}元</text>
          作为参考不作为最终付款
        </view>
        <button class="xiadan" type="primary" @tap="createOrder()">立即下单</button>
      </view>

      <!-- 等待司机接单 -->
      <view class="order" v-if="status == 3">
        <view class="cancel"><text class="text" @tap="cancel('确定要取消订单吗？')">取消订单</text></view>
        <view class="money">
          <text>预计费用{{ price }}元</text>
          作为参考不作为最终付款
        </view>
        <button class="xiadan" type="primary" disabled>等待司机接单{{ cancelData.time }}</button>
      </view>
      <!-- 已接单 -->
      <view class="new" v-if="status == 4">
        <view class="cancel"><text class="text" @tap="cancel('确定要取消订单吗？')">取消订单</text></view>
        <view class="user">
          <image :src="order.driver.avatar" mode=""></image>
          <view class="name">
            <text class="item1">{{ order.driver.nickname }}</text>
            <!-- <text class="icon yellow">&#xe642;</text> -->
            <text class="item2">{{ order.driver.total_order }}单</text>
            <text class="item2">驾龄{{ order.driver.driving_age }}年</text>
            <text class="item2">总里程{{ order.driver.total_distance }}km</text>
          </view>
          <view class="phone" @tap="callPhone(order.driver.mobile)">
            <text class="icon blue">&#xe657;</text>
            {{ order.driver.mobile }}
          </view>
        </view>
        <view class="money">
          <text>预计费用{{ order.estimated_price }}元</text>
          <view v-if="order.status==4">司机等待10分钟内免费，超出后每分钟1元</view>
        </view>
        <button class="xiadan" type="primary" disabled>{{order.status==1?'司机已接单':'司机等待中'}}</button>
      </view>
      <!--进行中-->
      <view class="new" v-if="status == 5">
        <view class="user">
          <image :src="order.driver.avatar" mode=""></image>
          <view class="name">
            <text class="item1">{{ order.driver.nickname }}</text>
            <!-- 						<text class="icon yellow">&#xe642;</text>
						<text class="item2">5.0</text> -->
            <text class="item2">{{ order.driver.total_order }}单</text>
            <text class="item2">驾龄{{ order.driver.driving_age }}年</text>
            <text class="item2">总里程{{ order.driver.total_distance }}km</text>
          </view>
          <view class="phone" @tap="callPhone(order.driver.mobile)">
            <text class="icon blue">&#xe657;</text>
            {{ order.driver.mobile }}
          </view>
        </view>
        <view class="money tips">
          <view class="item" style="width: 100%; font-size: 40upx; font-weight: 800;text-align: center;">实时费用{{ actual.price }}元</view>
          <view class="item" style="width: 100%; font-size: 22upx;color: #D2D2D2; margin-top:20upx;text-align: center;">
            已行驶{{ actual.distance | kilometer }} km，预计还需要{{ route.duration }}分钟到达
          </view>
        </view>
      </view>
      <!-- 付款 -->
      <view class="payment" v-if="status == 6">
        <view class="user">
          <image :src="order.driver.avatar" mode=""></image>
          <view class="name">
            <text class="item1">{{ order.driver.nickname }}</text>
            <!-- 						<text class="icon yellow">&#xe642;</text>
						<text class="item2">5.0</text> -->
            <text class="item2">{{ order.driver.total_order }}单</text>
            <text class="item2">驾龄{{ order.driver.driving_age }}年</text>
            <text class="item2">总里程{{ order.driver.total_distance }}km</text>
          </view>
          <view class="phone" @tap="callPhone(order.driver.mobile)">
            <text class="icon blue">&#xe657;</text>
            {{ order.driver.mobile }}
          </view>
        </view>
        <view class="money">
          <text style="width: 100%; font-size: 40upx; font-weight: 800;text-align: center;">实时费用{{ order.price }}元</text>
          <text style="width: 100%; font-size: 22upx;color: #D2D2D2; margin-top:20upx;text-align: center;">到达时间{{ order.end_time | date }}</text>
        </view>
        <button class="xiadan" type="primary" @tap="pay">确认支付{{ order.price }}元</button>
      </view>
      <!-- 付款后，待评价 -->
      <view class="comment" v-if="status == 7">
        <view class="user">
          <image :src="order.driver.avatar" mode=""></image>
          <view class="name">
            <text class="item1">{{ order.driver.nickname }}</text>
            <!--            <text class="icon"></text>
            <text class="item2">5.0</text> -->
            <text class="item2">{{ order.driver.total_order }}单</text>
            <text class="item2">驾龄{{ order.driver.driving_age }}年</text>
            <text class="item2">总里程{{ order.driver.total_distance }}km</text>
          </view>
          <view class="phone">
            <text></text>
            {{ order.driver.mobile }}
          </view>
        </view>
        <view class="money" v-if="order.comment == 1">
          <view>订单已完成，到达时间{{ order.end_time | date }}</view>
          <view>已付{{ order.price }}元</view>
        </view>
        <view class="comments" v-if="order.comment == 0">
          <my-issue :score="score" :headPicShow="false" :submitShow="false" @scoreChange="scoreChange" :headTitleShow="false" />
        </view>
        <button class="comment-btn" type="primary" v-if="order.comment == 0" @tap="comment">评价本次服务</button>
        <button class="done" type="primary" v-if="order.comment == 1" @tap="done">订单已完成</button>
      </view>
    </view>
  </view>
</template>

<script>
import QQMapWX from '../../libs/qqmap-wx-jssdk/qqmap-wx-jssdk.js';
import uniPopup from '@/components/uni-popup/uni-popup.vue';
import myIssue from '@/components/myIssue.vue';
import helper from '../../common/helper.js';
import api from '../../common/api.js';
export default {
  components: {
    uniPopup,
    myIssue
  },
  filters: {
    date: value => {
      let date = new Date(value * 1000);
      let h = date.getHours();
      let m = date.getMinutes();
      return (h > 9 ? h : '0' + h) + ':' + (m > 9 ? m : '0' + m);
    },
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
  data() {
    return {
      input: '',
      score: 5,
      host: helper.host,
      qqmapsdk: {},
      city: '',
      currLocation: {},
      currAddress: {},
      suggestion: [],
      region: '',
      toAddress: {},
      covers: [],
      result: {},
      order: {},
      status: 0,
      show: false,
      msg: '',
      action: '',
      orderId: '',
      mobile: '',
      map: {
        latitude: '',
        longitude: '',
        covers: [],
        scale: 16
      },
      focus: false,
      price: '',
      timer: '',
      actual: {
        price: 0,
        distance: 0
      },
      route: {
        duration: '--'
      },
      cancelData: {
        timer: '',
        time: ''
      },
      selectType: 'end'
    };
  },
  onLoad(option) {
    this.initSdk();
    if (option.action == 'call') {
      // 不存在则获取当前位置
      this.getLocation();
      this.status = 1;
      this.selectAddress(option.type);
    }
    // 用户端订单
    if (option.action == 'order') {
      this.orderId = option.order_id;
    }
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
    if(this.cancelData.timer){
      clearInterval(this.cancelData.timer);
      this.cancelData.timer = '';
    }
  },
  methods: {
    // 点击地图
    onMapTap(e) {
      let res = e.detail;
      // 设置地图信息
      this.map.latitude = res.latitude;
      this.map.longitude = res.longitude;
      // 坐标点
      this.map.covers = [];
      this.map.covers.push({
        latitude: res.latitude,
        longitude: res.longitude,
        iconPath: '../../static/marker.png'
      });
      // 坐标转地址信息
      this.qqmapsdk.reverseGeocoder({
        location: {
          latitude: res.latitude,
          longitude: res.longitude
        },
        complete: e => {
          console.log(e);
          if (e.status == 0) {
            this.currAddress = e.result;
          }
        }
      });
    },
    selectAddress(type) {
      this.focus = false;
      this.selectType = type;
      this.suggestion = [];
      this.input = '';
      this.openPopup();
    },
    scoreChange(score) {
      this.score = score;
    },
    async comment() {
      let res = await api.orderComment(this.orderId, this.score);
      helper.toast(res.msg);
      this.getOrderInfo();
    },
    async pay() {
      let res = await api.getOrderPayData(this.orderId);
      if (res.msg) {
        helper.toast(res.msg);
        return false;
      }
      // 仅作为示例，非真实参数信息。
      uni.requestPayment({
        provider: 'wxpay',
        timeStamp: res.timeStamp,
        nonceStr: res.nonceStr,
        package: res.package,
        signType: res.signType,
        paySign: res.paySign,
        success: res => {
          this.getOrderInfo();
        },
        fail: err => {
          console.log('fail:' + JSON.stringify(err));
        }
      });
    },
    callPhone(mobile) {
      uni.makePhoneCall({
        phoneNumber: mobile
      });
    },
    closeModal() {
      this.show = false;
    },
    async getOrderInfo() {
      console.log('获取订单信息');
      let res = await api.getOrderInfo(this.orderId);
      this.order = res.data;
      // 设置状态
      this.setStatus(res.data.status, res.data);
      // 设置地图
      this.map.latitude = res.data.start_latitude;
      this.map.longitude = res.data.start_longitude;
      // 坐标点
      this.map.covers.push({
        latitude: res.data.start_latitude,
        longitude: res.data.start_longitude,
        iconPath: '../../static/marker.png'
      });
      // 路径规划
      // 如果订单为进行中，则实时更新费用及到达时间
      if (this.order.status == 2) {
        this.updateOrderInfo();
      }
      // 如果订单为未完成状态，则定时更新
      if (this.order.status != 99 && this.order.status != -1 && !this.timer) {
        console.log('注册定时器');
        this.timer = setInterval(() => {
          this.getOrderInfo();
        }, 3 * 1000);
      }
      // 如果订单状态为已完成，切存在定时器，则关闭
      if (this.order.status == 99 && this.timer) {
        console.log('清除定时器');
        clearInterval(this.timer);
        this.timer = '';
      }
      // 订单为等待接单状态
      if (this.order.status == 0) {
        this.registerCancelTimer();
      }
    },
    registerCancelTimer() {
      if (!this.cancelData.timer) {
        this.cancelData.timer = setInterval(() => {
          let timestamp = Date.parse(new Date()) / 1000;
          let s = this.order.createtime + 5 * 60 - timestamp;
          // 订单超时
          if (s <= 0) {
            clearInterval(this.cancelData.timer);
            this.cancelData.timer = '';
            this.cancelData.time = '';
            api.cancelOrder(this.orderId);
            uni.showModal({
              title: '订单提示',
              content: '订单已超时并取消，请重新呼叫',
              showCancel: false,
              success: res => {
                if (res.confirm) {
                  uni.navigateBack({
                    url: '/pages/index/index'
                  });
                }
              }
            });
            return false;
          }
          // 倒计时
          if (s < 60) {
            this.cancelData.time = s + '秒';
          }
          if (s > 60) {
            this.cancelData.time = parseInt(s / 60) + '分' + parseInt(s % 60) + '秒';
          }
        }, 1000);
      }
    },
    setStatus(orderStatus, order) {
      let status = 0;
      switch (orderStatus) {
        case '-1':
          // 已取消
          status = -1;
          break;
        case '0':
          // 未接单
          status = 3;
          this.price = order.estimated_price;
          break;
        case '1':
          // 已接单
          status = 4;
          break;
        case '2':
          // 进行中
          status = 5;
          break;
        case '3':
          // 待支付
          status = 6;
          break;
        case '4':
          // 出发
          status = 4;
          break;
        case '99':
          // 订单已完成
          status = 7;
          break;
      }
      this.status = status;
    },
    // 取消订单
    async cancel(tips) {
      if (!tips) {
        tips = '确定要取消订单吗';
      }
      uni.showModal({
        title: '操作提示',
        content: tips,
        success: async res => {
          if (res.confirm) {
            let res = await api.cancelOrder(this.orderId);
            helper.toast(res.msg);
            if (res.code == 1) {
              uni.navigateBack({
                url: '/pages/index/index'
              });
            }
          }
        }
      });
    },
    // 创建订单
    async createOrder() {
      let data = {
        start: this.currAddress.formatted_addresses.recommend,
        start_city: this.currAddress.ad_info.city,
        start_address: this.currAddress.address,
        start_latitude: this.currAddress.location.lat,
        start_longitude: this.currAddress.location.lng,
        end: this.toAddress.title,
        end_city: this.toAddress.city,
        end_address: this.toAddress.addr,
        end_latitude: this.toAddress.latitude,
        end_longitude: this.toAddress.longitude,
        distance: this.result.distance,
        duration: this.result.duration,
        mobile: this.mobile
      };
      let res = await api.createOrder(data);
      helper.toast(res.msg);
      if (res.code != 0) {
        this.orderId = res.data.order_id;
        this.getOrderInfo();
      }
    },
    // 呼叫司机
    call() {
      if (!this.toAddress.title) {
        helper.toast('请输入目的地');
        return false;
      }
      let from = this.currAddress.location.lat + ',' + this.currAddress.location.lng;
      let to = this.toAddress.latitude + ',' + this.toAddress.longitude;
      // 计算驾车距离
      this.qqmapsdk.calculateDistance({
        mode: 'driving',
        from: from,
        to: to,
        complete: async res => {
          this.result = res.result.elements[0];
          let data = await api.getPrice(this.result.distance, 0);
          this.price = data.data;
          this.status = 2;
        }
      });
    },
    onInputAddress(e) {
      let address = e.detail.value;
      //调用关键词提示接口
      this.qqmapsdk.getSuggestion({
        //获取输入框值并设置keyword参数
        keyword: address,
        region: this.city,
        success: res => {
          //搜索成功后的回调
          console.log(res);
          let sug = [];
          for (let i = 0; i < res.data.length; i++) {
            sug.push({
              // 获取返回结果，放到sug数组中
              title: res.data[i].title,
              id: res.data[i].id,
              addr: res.data[i].address,
              city: res.data[i].city,
              district: res.data[i].district,
              latitude: res.data[i].location.lat,
              longitude: res.data[i].location.lng
            });
          }
          this.suggestion = sug;
        },
        fail: function(error) {
          console.error(error);
        },
        complete: function(res) {
          console.log(res);
        }
      });
    },
    setAddress(address) {
      if (this.selectType == 'end') {
        this.toAddress = address;
      } else {
        this.currAddress = {
          formatted_addresses: {
            recommend: address.title
          },
          ad_info: {
            city: address.city
          },
          address: address.addr,
          location: {
            lat: address.latitude,
            lng: address.longitude
          }
        };
      }
      this.closePopup();
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
          // 当前地址
          this.currLocation = res;

          // 设置地图信息
          this.map.latitude = res.latitude;
          this.map.longitude = res.longitude;
          // 坐标点
          this.map.covers.push({
            latitude: res.latitude,
            longitude: res.longitude,
            iconPath: '../../static/marker.png'
          });
          // 坐标转地址信息
          this.qqmapsdk.reverseGeocoder({
            location: {
              latitude: res.latitude,
              longitude: res.longitude
            },
            complete: res => {
              console.log(res);
              if (res.status == 0) {
                this.currAddress = res.result;
                this.city = res.result.address_component.city;
              }
            }
          });
        }
      });
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
          let covers = [
            {
              latitude: res.latitude,
              longitude: res.longitude,
              iconPath: '../../static/marker.png'
            }
          ];
          this.map.covers=covers;
          // 更新订单位置
          res.order_id = this.orderId;
          let data = await api.updateOrderLocation(res);
          this.actual = data.data;
          // 更新路径
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
        }
      });
    },
    address() {
      this.$refs.popup.open();
    },
    appraisal() {
      uni.navigateTo({
        url: 'appraisal'
      });
    },
    done() {
      uni.navigateBack({
        url: '/pages/index/index'
      });
    }
  }
};
</script>

<style lang="scss" scoped>
input {
  font-size: 26upx;
}
.icon {
  font-size: 56upx;
}
.map {
  width: 100%;
  height: calc(100vh - 400upx);
}
.call {
  padding: 30upx;
}
.taxi {
  width: 100%;
  height: 400upx;
  flex-direction: column;
  align-items: center;
  background-color: #ffffff;
  .input {
    display: flex;
    align-items: center;
    width: 90%;
    padding: 20upx 0;
    text {
      width: 10%;
    }
    input {
      width: 60%;
    }
    button {
      font-size: 26upx;
      background-color: #ffffff;
      color: #333333;
      float: right;
    }
  }
}
.tips {
  .item {
    font-size: 28upx;
    color: #000000;
  }
}
.popup {
  height: 100%;
  .destination {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10upx 0;
    border-bottom: 1px solid #eeeeee;
    text {
      font-size: 28upx;
      color: #d2d2d2;
    }
  }
  .addressHistory {
    padding: 20upx 0;
    border-bottom: 1px solid #eeeeee;
    display: flex;
    flex-direction: column;
    font-size: 24upx;
    color: #d2d2d2;
    text {
      color: #101010;
      font-size: 28upx;
    }
  }
}
.theFare {
  width: 100%;
  height: 100%;
  .money {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    font-size: 22upx;
    height: 234upx;
    color: #d2d2d2;
    text {
      margin-bottom: 10upx;
      color: #101010;
      font-size: 38upx;
      font-weight: 800;
    }
  }
  .xiadan {
    width: 100%;
    border-radius: 0px;
    background-color: #32c45e;
    border: none;
    position: absolute;
    bottom: 0;
  }
  .xiadan:after {
    border: none;
  }
}
.order {
  width: 100%;
  height: 100%;
  .cancel {
    font-size: 28upx;
    text-align: right;
    width: 95%;
    .text {
      padding-top: 10px;
      display: inline-block;
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
      font-size: 38upx;
      font-weight: 800;
    }
  }
  .xiadan {
    width: 100%;
    border-radius: 0px;
    background-color: #e1e1e1;
    position: absolute;
    bottom: 0;
    color: #999999;
  }
  .xiadan:after {
    border: none;
  }
}
.new {
  width: 100%;
  height: 100%;
  .cancel {
    font-size: 28upx;
    text-align: right;
    width: 95%;
    .text {
      padding-top: 10px;
      display: inline-block;
    }
  }
  .user {
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 28upx;
    .icon {
      font-size: 28upx;
    }
    image {
      width: 80upx;
      height: 80upx;
      border-radius: 50%;
    }
    .name {
      width: 40%;
      margin-left: 20upx;
      .item2 {
        font-size: 24upx;
        padding-left: 20upx;
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
      font-size: 38upx;
      font-weight: 800;
    }
  }
  .xiadan {
    width: 100%;
    border-radius: 0px;
    background-color: #e1e1e1;
    position: absolute;
    bottom: 0;
    color: #999999;
  }
  .xiadan:after {
    border: none;
  }
}
.payment {
  height: 100%;
  .user {
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 28upx;
    padding: 20upx;
    image {
      width: 80upx;
      height: 80upx;
      border-radius: 50%;
    }
    .name {
      width: 40%;
      margin-left: 20upx;
      .item2 {
        font-size: 24upx;
        padding-left: 20upx;
      }
    }
    .phone {
      width: 40%;
      text-align: right;
    }
    .icon {
      font-size: 28upx;
    }
  }
  .money {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    flex-direction: column;
    font-size: 22upx;
    height: 100upx;
    color: #d2d2d2;
    padding: 20upx;
    text {
      width: 45%;
      margin-bottom: 10upx;
      color: #101010;
      font-size: 30upx;
    }
  }
  .xiadan {
    width: 100%;
    border-radius: 0px;
    background-color: #32c45e;
    position: absolute;
    bottom: 0;
  }
  .xiadan:after {
    border: none;
  }
}
.cancel-text {
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 16px;
  color: red;
}
.comment {
  width: 100%;
  height: 100%;
  background-color: #ffffff;
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
        padding-left: 20upx;
      }
    }
    .phone {
      width: 40%;
      text-align: right;
    }
  }
  .money {
    text-align: center;
    padding-top: 40upx;
    height: 120upx;
    font-size: 28upx;
    text {
      margin-bottom: 10upx;
      color: #101010;
      font-size: 30upx;
    }
  }
  .comment-btn {
    width: 100%;
    border-radius: 0px;
    background-color: #32c45e;
    height: 90upx;
    color: #ffffff;
    border-top: 1px solid #dddddd;
    position: absolute;
    bottom: 0;
  }
  .comment-btn:after {
    border: none;
  }
  .done {
    height: 90upx;
    width: 100%;
    border-radius: 0px;
    background-color: #32c45e;
    color: #ffffff;
    border-top: 1px solid #dddddd;
    position: absolute;
    bottom: 0;
  }
  .done:after {
    border: none;
  }
}
</style>
