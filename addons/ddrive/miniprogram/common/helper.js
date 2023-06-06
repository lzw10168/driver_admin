// 相关配置
const config = {
  development: {
    // 开发环境服务端地址，本地调试域名
    host: "http://ddcar.com/"
  },
  production: {
    // 生产环境服务端地址，编译后自动启用该地址
    host: "http://ddcar.brt999.com/"
  },
  // 身份认证状态码
  authErrorStatusCode: 401,
  // 登录页面路径
  loginPage: "/pages/login/login",
  // 跳转登录页面方式
  navigateLoginPageType: "navigateTo",
  // 接口异常提醒
  errorMessage: "服务器异常",
  // 认证token
  tokenField: '__token__',
  // 腾讯地图，请更换为自己的 key 否则有次数限制
  qqmapsdk: {
    key: 'QUSBZ-FUFLU-X2SV5-2UXFE-KVWRJ-C5FBE'
  }
}



// 开发环境
const env = process.env.NODE_ENV;

console.log('当前环境：' + env);
// 平台
const platform = uni.getSystemInfoSync().platform;
// 封装消息提示方法
const toast = (message) => {
  uni.showToast({
    title: message,
    duration: 2000,
    icon: 'none'
  });
}
// 对象转url参数
const parseParam = (param) => {
  let paramArray = [];
  for (let [key, value] of Object.entries(param)) {
    paramArray.push(key + '=' + value);
  }
  return paramArray.join('&');
}
// 设置用户表示符
const setUserToken = (token) => {
  return uni.setStorageSync(config.tokenField, token);
}

const clearUsetToken = () => {
  return uni.clearStorage(config.tokenField);
}

// 获取用户身份标示
const getUserToken = () => {
  return uni.getStorageSync(config.tokenField);
}
//判断是否登录
const hasLogin = () => {
  let token = uni.getStorageSync(config.tokenField);
  return token ? true : false;
}
// 封装get方法
const get = (url, data) => {
  let token = uni.getStorageSync(config.tokenField);
  if(data){
    data.token=token
  }else{
    data={token}
  }
  return new Promise((resolve, reject) => {
    uni.request({
      url: config[env].host + url,
      data: data,
      header: {
        'accept-language':'zh-CN,zh;q=0.9,en;q=0.8'
      },
      complete: (res) => {
        if (res.statusCode == config.authErrorStatusCode) {
          uni.removeStorageSync(config.tokenField);
          uni[config.navigateLoginPageType]({
            url: config.loginPage
          });
          return false;
        }
        if (res.statusCode == 200) {
          resolve(res.data);
        } else {
          toast(config.errorMessage);
          reject(res);
        }
      }
    })
  })
}

// 封装post方法
const post = (url, data) => {
  let token = uni.getStorageSync(config.tokenField);
  if(data){
    data.token=token
  }else{
    data={token}
  }
  return new Promise((resolve, reject) => {
    uni.request({
      url: config[env].host + url,
      header: {
        'content-type': 'application/x-www-form-urlencoded',
        'accept-language':'zh-CN,zh;q=0.9,en;q=0.8'
      },
      data: data,
      method: 'POST',
      complete: (res) => {
        if (res.statusCode == config.authErrorStatusCode) {
          uni.removeStorageSync(config.tokenField);
          uni[config.navigateLoginPageType]({
            url: config.loginPage
          });
          return false;
        }
        if (res.statusCode == 200) {
          resolve(res.data);
        } else {
          toast(config.errorMessage);
          reject(res);
        }
      }
    })
  })
}

const date = (timestamp) => {
  // 获取当前日期
  const date = timestamp ? new Date(timestamp) : new Date();

  // 获取当前月份
  const nowMonth = date.getMonth() + 1;

  // 获取当前是几号
  let strDate = date.getDate();

  // 添加分隔符“-”
  const seperator = "-";

  // 对月份进行处理，1-9月在前面添加一个“0”
  if (nowMonth >= 1 && nowMonth <= 9) {
    nowMonth = "0" + nowMonth;
  }

  // 对月份进行处理，1-9号在前面添加一个“0”
  if (strDate >= 0 && strDate <= 9) {
    strDate = "0" + strDate;
  }

  // 最后拼接字符串，得到一个格式为(yyyy-MM-dd)的日期
  return date.getFullYear() + seperator + nowMonth + seperator + strDate;
}

export default {
  host: config[env].host,
  env,
  platform,
  get,
  post,
  parseParam,
  hasLogin,
  toast,
  setUserToken,
  date,
  config,
  getUserToken,
  clearUsetToken
}
