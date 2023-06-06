<template>
  <view>
    <view class="top">
      <text class="icon" @tap="openPopup">积分规则</text>
      <view class="jf">
        <text>积分</text>
        <view class="score">{{ userinfo.score }}</view>
      </view>
      <view class="rank">
        <text class="text">{{ userinfo.score | rank }}</text>
      </view>
    </view>
    <view class="title"><view class="text">积分明细</view></view>

    <view class="m_list">
      <view class="item" v-for="(v, i) in info.data" :key="i">
        <view class="left">
          <text class="t1">{{ v.memo }}</text>
          <text class="t2">{{ v.createdate }}</text>
        </view>
        <text class="right">+{{ v.score }}</text>
      </view>
    </view>

    <view class="empty" v-if="info.total == 0">暂无积分获取记录</view>

    <uni-popup ref="popup" type="center" class="popup" padding="0">
      <view class="popup_top">积分规则</view>
      <view class="info">
        <view class="item">1.每消费一笔订单，可得到支付金额相等的积分。</view>
        <view class="item">2.每发起一条论坛话题可增加5积分。</view>
        <view class="item">3.收到一条评论可增加1积分。</view>
        <text class="t3">积分使用</text>
        <view class="item1">1.500积分支付时95折</view>
        <view class="item1">2.1000积分支付时93折</view>
        <view class="item1">3.1500积分支付时9折</view>
      </view>
      <view class="bottom" @tap="closePopup()">知道了</view>
    </uni-popup>
  </view>
</template>

<script>
import helper from '../../common/helper.js';
import api from '../../common/api.js';
import uniPopup from '@/components/uni-popup/uni-popup.vue';
export default {
  components: { uniPopup },
  filters: {
    rank: value => {
      if (value < 500) {
        return '银卡';
      }
      if (value < 1000) {
        return '金卡';
      }
      return '钻石卡';
    }
  },
  data() {
    return {
      map: {
        page: 1
      },
      info: {
        total: 0,
        data: []
      },
      userinfo: {}
    };
  },
  onShow() {
    this.getList();
    this.getUserInfo();
  },
  methods: {
    async getList() {
      let res = await api.getScoreLog(this.map);
      this.info = res.data;
      uni.stopPullDownRefresh();
    },
    async getUserInfo() {
      let res = await api.getUserInfo();
      this.userinfo = res.data;
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
    openPopup() {
      this.$refs.popup.open();
    },
    closePopup() {
      this.$refs.popup.close();
    }
  }
};
</script>

<style lang="scss" scoped>
.empty {
  text-align: center;
  font-size: 26upx;
  padding: 60upx;
}
.top {
  background-color: #32c45e;
  color: #ffffff;
  height: 300upx;
  padding: 20upx;

  .jf {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 100%;
    font-size: 26upx;
  }
  .icon {
    float: right;
    text-align: right;
    font-size: 26upx;
  }
  .score{
    font-size: 64upx;
	
  }
}
.title {
  width: 100%;
  margin-top: 10%;
  border-top: 1px solid #e1e1e1;
  display: flex;
  justify-content: center;
  .text {
    text-align: center;
    background-color: #ffffff;
    margin-top: -19upx;
    color: #9b9b9b;
    font-size: 24upx;
    padding: 0 20upx;
  }
}
.m_list {
  padding: 0 30upx;
  .item {
    display: flex;
    padding: 25upx 0;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #eeeeee;
    .left {
      display: flex;
      flex-direction: column;
      .t1 {
        font-size: 28upx;
        font-weight: 800;
      }
      .t2 {
        font-size: 22upx;
		color: #999999;
      }
    }
    .right {
      color: #ea1010;
      font-size: 28upx;
    }
  }
}
.popup {
  font-size: 24upx;
  .popup_top {
    text-align: center;
    background-color: #32c45e;
    color: #ffffff;
    padding: 20upx;
    font-size: 30upx;
  }
  .info {
    padding: 20upx;
    .t3 {
      font-size: 30upx;
      width: 100%;
      line-height: 80upx;
    }
    .item,
    .item1 {
      margin-top: 10upx;
    }
  }
  .bottom {
    text-align: center;
    line-height: 80upx;
    margin-top: 20upx;
    box-shadow: 0px -2upx 2upx 2upx #ececec;
  }
}
.rank{
  text-align: center;
  margin-top: 20upx;
  .text{
    background-color: #FFFFFF;
    border: 1px solid #F0AD4E;
    color: #F0AD4E;
    border-radius: 20upx;
    font-size: 24upx;
    padding: 4upx 16upx;
  }
}
</style>
