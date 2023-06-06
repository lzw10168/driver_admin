<template>
  <view class="index">
    <view class="m_list">
      <view class="item" v-for="(v, i) in list.data" :key="i">
        <view class="top">
          <text>微信提现</text>
          <view class="right">
            <text>{{ v.money }}</text>
            元
          </view>
        </view>
        <view class="bottom">
          <text>{{v.createdate}}</text>
        </view>
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
        page: 1,
        pageSize:20
      },
      list: {}
    };
  },
  onShow() {
    this.getList();
  },
  methods: {
    async getList() {
      let res = await api.getWithdrawList(this.map);
      this.list = res.data;
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
    }
  }
};
</script>

<style lang="scss">
page,
.index {
  background-color: #f5f5f5;
}
.m_list {
  padding: 0 30upx;
  border-top: #f5f5f5 1px solid;
  background-color: #ffffff;
}
.item {
  padding: 20upx 0;
  border-bottom: #f5f5f5 1px solid;
  .top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 28upx;
    .right {
      text {
        font-size: 40upx;
        color: #e51c23;
      }
    }
  }
  .bottom {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 24upx;
    color: #9f9f9f;
    margin-top: 10upx;
  }
}
</style>
