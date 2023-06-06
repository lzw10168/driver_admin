<template>
	<view>
		<uni-swiper-dot :info="info" :current="current" :mode="mode" :dots-styles="dotsStyles" field="content">
			<swiper class="swiper-box" @change="change">
				<swiper-item v-for="(item, index) in info" :key="index">
					<view class="swiper-item"><image :src="item.url" mode="aspectFill" /></view>
				</swiper-item>
			</swiper>
		</uni-swiper-dot>
		<view class="user">
			<view class="name">
				<text>郑州豫满楼饭店</text>
				豪华型
			</view>
			<view class="address">二七区德化街39号（近德化步行街、郑州长 途汽车站）</view>
			<view class="phone">
				<text></text>
				12345645595
			</view>
		</view>
		<view class="img">
			<text>详情</text>
			<image src="../../static/logo.png" mode=""></image>
		</view>
	</view>
</template>

<script>
import uniSwiperDot from '@/components/uni-swiper-dot/uni-swiper-dot.vue';
export default {
	components: {
		uniSwiperDot
	},
	data() {
		return {
			info: [
				{
					url: 'https://img-cdn-qiniu.dcloud.net.cn/uniapp/images/shuijiao.jpg'
				},
				{
					url: 'https://img-cdn-qiniu.dcloud.net.cn/uniapp/images/muwu.jpg'
				},
				{
					url: 'https://img-cdn-qiniu.dcloud.net.cn/uniapp/images/cbd.jpg'
				}
			]
		};
	}
};
</script>

<style lang="scss">
.swiper-box {
	height: 430upx;
	.swiper-item image {
		width: 100%;
	}
}
.user {
	font-size: 28upx;
	.name{
		font-size: 24upx;
		text{
			font-size: 38upx;
			font-weight: 800;
			margin-right: 20upx;
		}
	}
	.address,.phone{
		padding: 10upx 0;
	}
	
}
.img{
	border-top:1px #DFDFDF solid ;
	display: flex;
	margin-top: 40upx;
	flex-direction: column;
	align-items: center;
	text{
		margin-top: -25upx;
		width: 20%;
		text-align: center;
		background-color: #FFFFFF;
		margin-bottom: 50upx;
	}
	image{
		width: 100%;
	}
}
</style>
