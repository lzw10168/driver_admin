import helper from "./helper.js"

export default {

  /**
   * 修改个人资料
   * @param {Object} data
   */
  updateUserInfo(data) {
    return helper.post('/addons/ddrive/user/profile', data);
  },
  
  /**
   * 修改个人资料
   * @param {Object} data
   */
  getServiceRule() {
    return helper.post('/addons/ddrive/user/service');
  },

  /**
   * 上传文件
   * @param {Object} file
   */
  upload(file) {
    return new Promise((resolve, reject) => {
      uni.uploadFile({
        url: helper.host + '/api/common/upload',
        filePath: file,
        name: 'file',
        formData: {
          token: helper.getUserToken()
        },
        success: uploadFileRes => {
          resolve(JSON.parse(uploadFileRes.data));
        },
        fail: err => {
          resolve({
            code: 0,
            msg: '上传失败'
          });
        }
      });
    });
  },

  /**
   * 根据code获取openid
   * @param {Object} code
   */
  getOpenid(code) {
    return helper.post('/addons/ddrive/wechat/getOpenid', {
      code
    });
  },

  /**
   * 获取广告位
   */
  getBanner() {
    return helper.get('/addons/ddrive/banner');
  },

  /**
   * 创建订单
   * @param {Object} data
   */
  createOrder(data) {
    return helper.post('/addons/ddrive/order/create', data);
  },


  /**
   * 获取订单列表
   * @param {Object} map
   */
  getOrderList(map) {
    return helper.get('/addons/ddrive/order', map);
  },

  /**
   * 获取待接单订单列表
   * @param {Object} map
   */
  getOrderTakingList(map) {
    return helper.get('/addons/ddrive/order/takingList', map);
  },

  /**
   * 获取订单详情
   * @param {Object} order_id
   */
  getOrderInfo(order_id) {
    return helper.get('/addons/ddrive/order/info', {
      order_id
    });
  },

  /**
   * 取消订单
   * @param {Object} order_id
   */
  cancelOrder(order_id) {
    return helper.get('/addons/ddrive/order/cancel', {
      order_id
    });
  },

  /**
   * 司机接单
   * @param {Object} order_id
   */
  orderTaking(order_id) {
    return helper.post('/addons/ddrive/order/taking', {
      order_id
    });
  },
  
  /**
   * 司机到达出发地
   * @param {Object} order_id
   */
  orderReach(order_id){
    return helper.post('/addons/ddrive/order/reach', {
      order_id
    });
  },

  /**
   * 立即出发
   * @param {Object} data
   */
  startOrder(data) {
    return helper.post('/addons/ddrive/order/start', data);
  },

  /**
   * 更新订单位置信息
   * @param {Object} data
   */
  updateOrderLocation(data) {
    return helper.post('/addons/ddrive/order/updateLocation', data);
  },

  /**
   * 结束订单
   * @param {Object} data
   */
  doneOrder(data) {
    return helper.post('/addons/ddrive/order/done', data);
  },

  /**
   * 订单评分
   * @param {Object} order_id
   * @param {Object} score
   */
  orderComment(order_id, score) {
    return helper.post('/addons/ddrive/order/comment', {
      order_id,
      score
    });
  },

  /**
   * 获取订单支付信息
   * @param {Object} order_id
   */
  getOrderPayData(order_id) {
    return helper.get('/addons/ddrive/order/pay', {
      order_id
    });
  },
  
  /**
   * 获取订单扫码支付信息
   * @param {Object} order_id
   */
  getOrderPayScanInfo(order_id){
    return helper.get('/addons/ddrive/order/pay', {
      order_id:order_id,
      method:'scan'
    });
  },

  /**
   * 获取验证码
   * @param {Object} mobile
   * @param {Object} event
   */
  getCaptcha(mobile, event) {
    return helper.get('/api/sms/send', {
      mobile,
      event
    });
  },

  /**
   * 注册用户
   * @param {Object} data
   */
  register(data) {
    return helper.post('/addons/ddrive/user/register', data);
  },

  /**
   * 用户登录
   * @param {Object} data
   */
  login(data) {
    return helper.post('/addons/ddrive/user/login', data);
  },

  /**
   * 退出登录
   */
  logout() {
    return helper.post('/addons/ddrive/user/logout');
  },

  /**
   * 重置密码
   * @param {Object} data
   */
  resetpwd(data) {
    return helper.post('/addons/ddrive/user/resetpwd', data);
  },

  /**
   * 发布话题
   * @param {Object} data
   */
  addMessage(data) {
    return helper.post('/addons/ddrive/message/add', data);
  },

  /**
   * 获取话题列表
   * @param {Object} map
   */
  getMessageList(map) {
    return helper.get('/addons/ddrive/message', map);
  },

  /**
   * 获取话题详情
   * @param {Object} message_id
   */
  getMessageInfo(message_id) {
    return helper.get('/addons/ddrive/message/info', {
      message_id
    });
  },

  /**
   * 获取我发布的话题详情
   * @param {Object} message_id
   */
  getMyMessageList(map) {
    return helper.get('/addons/ddrive/message/my', map);
  },

  /**
   * 删除话题
   * @param {Object} message_id
   */
  deleteMessage(message_id) {
    return helper.post('/addons/ddrive/message/delete', {
      message_id
    });
  },

  /**
   * 获取评论列表
   * @param {Object} map
   */
  getMessageComments(map) {
    return helper.get('/addons/ddrive/message/comments', map);
  },

  /**
   * 添加评论
   * @param {Object} message_id
   * @param {Object} comment
   */
  addMessageComment(message_id, comment) {
    return helper.post('/addons/ddrive/message/addComment', {
      message_id,
      comment
    });
  },

  /**
   * 获取用户信息
   */
  getUserInfo() {
    return helper.get('/addons/ddrive/user');
  },

  /**
   * 获取后台配置数据
   */
  getSetting() {
    return helper.get('/addons/ddrive/config');
  },

  /**
   * 获取分类列表
   * @param {Object} type
   */
  getCategoryList(type) {
    return helper.get('/addons/ddrive/category', {
      type
    });
  },

  /**
   * 意见反馈
   * @param {Object} info
   */
  addFeedback(info) {
    return helper.post('/addons/ddrive/feedback/add', info);
  },

  /**
   * 申请代驾
   * @param {Object} info
   */
  addApply(info) {
    return helper.post('/addons/ddrive/apply/add', info);
  },

  /**
   * 查询代驾申请信息
   */
  getApplyInfo() {
    return helper.get('/addons/ddrive/apply/info');
  },

  /**
   * 根据距离查询费用
   * @param {Object} distance
   * @param {Object} duration
   */
  getPrice(distance, duration) {
    return helper.get('/addons/ddrive/order/amount', {
      distance,
      duration
    });
  },

  /**
   * 提现
   * @param {Object} money
   */
  withdraw(money) {
    return helper.post('/addons/ddrive/money/withdraw', {
      money
    });
  },

  /**
   * 获取提现列表
   */
  getWithdrawList(map) {
    return helper.get('/addons/ddrive/money/withdrawList', map);
  },

  /**
   * 获取积分列表
   * @param {Object} map
   */
  getScoreLog(map) {
    return helper.get('/addons/ddrive/score', map);
  }
}
