<template>
  <view>
    <map class="map" :latitude="map.latitude" :longitude="map.longitude" :scale="map.scale" :polyline="map.polyline" :markers="map.covers"></map>

    <view class="bottom" v-show="status == 1">
      <view class="input">
        <text class="icon green">&#xe608;</text>
        <input type="text" :value="start ? start.title : ''" disabled="true" @tap="selectAddress('start')" placeholder="输入您的起点位置" placeholder-class="input-placeholder" />
      </view>
      <view class="input">
        <text class="icon orange">&#xe608;</text>
        <input type="text" :value="end ? end.title : ''" disabled="true" @tap="selectAddress('end')" placeholder="输入您的终点位置" placeholder-class="input-placeholder" />
      </view>
      <button type="primary" @tap="direction">查看路线</button>
    </view>

    <view class="bottom1" v-show="status == 2">
      <view class="input">
        <text class="icon green">&#xe608;</text>
        <input type="text" :value="start ? start.title : ''" disabled="true" placeholder="输入您的起点位置" placeholder-class="input-placeholder" />
      </view>
      <view class="input">
        <text class="icon orange">&#xe608;</text>
        <input type="text" :value="end ? end.title : ''" disabled="true" placeholder="输入您的终点位置" placeholder-class="input-placeholder" />
      </view>
      <view class="money">
        <text>预计{{ route.duration }}分钟</text>
        <text>{{ route.distance | kilometer }}公里</text>
        <text>大约费用{{ price }}元</text>
      </view>
    </view>

    <!--地址输入-->
    <uni-popup class="popup" ref="popup" type="top">
      <view class="destination">
        <input type="text" v-model="input" :focus="focus" @input="onInputAddress" placeholder="请输入地址" />
        <text @tap="closePopup">取消</text>
      </view>
      <view class="addressHistory" v-for="(v, i) in suggestion" :key="i" @tap="setAddress(v)">
        <text>{{ v.title }}</text>
        {{ v.addr }}
      </view>
    </uni-popup>
    <!--地址输入-->
  </view>
</template>

<script>
import QQMapWX from '../../libs/qqmap-wx-jssdk/qqmap-wx-jssdk.js';
import uniPopup from '@/components/uni-popup/uni-popup.vue';
import helper from '../../common/helper.js';
import api from '../../common/api.js';

export default {
  components: {
    uniPopup
  },
  filters: {
    kilometer: value => {
      let distance = parseFloat(value/1000);
      return distance.toFixed(2);
    }
  },
  data() {
    return {
      status: 1,
      currLocation: {},
      currAddress: {},
      toAddress: {},
      map: {
        latitude: '',
        longitude: '',
        covers: [],
        scale: 16,
        polyline: []
      },
      suggestion: [],
      selectType: '',
      start: {},
      end: {},
      input: '',
      focus: false,
      route: {},
      price: ''
    };
  },
  onShow() {
    this.initSdk();
    this.getLocation();
  },
  methods: {
    async getPrice() {
      let res = await api.getPrice(this.route.distance,0);
      this.price = res.data;
      this.status = 2;
    },
    // 路径规划
    direction() {
      this.qqmapsdk.direction({
        mode: 'driving',
        from: this.start.latitude + ',' + this.start.longitude,
        to: this.end.latitude + ',' + this.end.longitude,
        complete: res => {
          console.log(res);
          if (!res.result || !res.result.routes || res.result.routes.length == 0) {
            helper.toast('未获取到有效路线');
            return false;
          }
          let coors = res.result.routes[0].polyline,
            pl = []; //坐标解压（返回的点串坐标，通过前向差分进行压缩）
          let kr = 1000000;
          // 处理坐标
          for (let i = 2; i < coors.length; i++) {
            coors[i] = Number(coors[i - 2]) + Number(coors[i]) / kr;
          }
          for (let i = 0; i < coors.length; i += 2) {
            pl.push({ latitude: coors[i], longitude: coors[i + 1] });
          }
          // 计算缩放级别
          let zoom = [50, 100, 200, 500, 1900, 2000, 5000, 10000, 20000, 25000, 50000, 100000, 200000, 500000];
          let scale = 18;
          let distance = res.result.routes[0].distance;
          for (let i = 0; i < zoom.length; i++) {
            if (zoom[i] - distance > 0) {
              scale = 18 - i + 4;
              break;
            }
          }
          this.map = {
            latitude: pl[0].latitude,
            longitude: pl[0].longitude,
            scale: scale,
            polyline: [
              {
                points: pl,
                color: '#227AFF',
                width: 4
              }
            ]
          };
          this.route = res.result.routes[0];
          this.getPrice();
        }
      });
    },
    selectAddress(selectType) {
      this.selectType = selectType;
      this.$refs.popup.open();
      this.focus = true;
    },
    setAddress(data) {
      if (this.selectType == 'start') {
        this.start = data;
      } else {
        this.end = data;
      }
      this.closePopup();
    },
    closePopup() {
      this.focus = false;
      this.input = '';
      this.suggestion = [];
      this.$refs.popup.close();
    },
    onInputAddress(e) {
      let address = e.detail.value;
      //调用关键词提示接口
      this.qqmapsdk.getSuggestion({
        //获取输入框值并设置keyword参数
        keyword: address,
        region: this.currAddress.address_component.city,
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
              }
            }
          });
        },
        complete: res => {
          console.log(res);
        }
      });
    }
  }
};
</script>

<style lang="scss" scoped>
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
.map {
  width: 100%;
  height: calc(100vh - 400upx);
}
.icon {
  font-size: 48upx;
}
.bottom1 {
  width: 100%;
  padding: 30upx;
  .input {
    width: 90%;
    display: flex;
    align-items: center;
    text {
      width: 10%;
    }
    input {
      width: 90%;
      padding: 20upx 0upx;
      border-bottom: 1px solid #f0f0f0;
    }
  }
  .money {
    width: 90%;
    display: flex;
    justify-content: space-evenly;
    font-size: 26upx;
    padding: 30upx;
  }
  button {
    border-radius: 0;
    background-color: #32c45e;
    font-size: 28upx;
  }
}
.bottom {
  width: 100%;
  padding: 30upx;
  .input {
    width: 90%;
    display: flex;
    align-items: center;
    text {
      width: 10%;
    }
    input {
      width: 90%;
      padding: 20upx 0upx;
      border-bottom: 1px solid #f0f0f0;
    }
  }
  button {
    width: 50%;
    margin-top: 50upx;
    border-radius: 0;
    background-color: #32c45e;
    font-size: 28upx;
    margin-bottom: 20upx;
    border-radius: 50upx;
  }
}
</style>
