<script>
	export default {
		onLaunch: function() {
			console.log('App Launch')
		},
		onShow: function() {
			console.log('App Show')
		},
		onHide: function() {
			console.log('App Hide')
		}
	}
</script>

<style>
  page{
    height: 100%;
  }
	/*每个页面公共css */
	@font-face {
	  font-family: 'iconfont';  /* project id 1488403 */
	  src: url('//at.alicdn.com/t/font_1488403_6jvjdwpn319.eot');
	  src: url('//at.alicdn.com/t/font_1488403_6jvjdwpn319.eot?#iefix') format('embedded-opentype'),
	  url('//at.alicdn.com/t/font_1488403_6jvjdwpn319.woff2') format('woff2'),
	  url('//at.alicdn.com/t/font_1488403_6jvjdwpn319.woff') format('woff'),
	  url('//at.alicdn.com/t/font_1488403_6jvjdwpn319.ttf') format('truetype'),
	  url('//at.alicdn.com/t/font_1488403_6jvjdwpn319.svg#iconfont') format('svg');
	}
	uni-button:after{
		border: none;
	}
	.green {
		color: #32c45e;
	}
	.yellow {
		color: #f8d448;
	}
	.blue {
		color: #227aff;
	}
	.orange {
		color: #ed965f;
	}
	.gray{
		color: #979797;
	}
	.input-placeholder{
		font-size: 26upx;
	}
  input {
    font-size: 26upx;
  }
	.icon {
	  font-size: 32upx;
	  font-family: iconfont;
	}
</style>
