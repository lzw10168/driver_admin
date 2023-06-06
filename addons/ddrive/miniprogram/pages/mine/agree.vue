<template>
  <view class="content">
    <rich-text :nodes="info.agree"></rich-text>
  </view>
</template>

<script>
import helper from '../../common/helper.js';
import api from '../../common/api.js';
export default {
  data() {
    return {
      info: {}
    };
  },
  onLoad() {
    this.getInfo();
  },
  methods: {
    async getInfo() {
      let res = await api.getSetting();
      this.info = res.data;
    }
  }
};
</script>

<style scoped="">
  .content{
    padding: 20upx;
    font-size: 26upx;
  }
</style>
