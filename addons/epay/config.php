<?php

return [
    [
        'name' => 'wechat',
        'title' => '微信',
        'type' => 'array',
        'content' => [],
        'value' => [
            'appid' => 'wxfd40071192dd9dc1',
            'app_id' => 'wxfd40071192dd9dc1',
            'app_secret' => '618fde560c77ebc4e2db1eefec041efe',
            'miniapp_id' => 'wx1dc3cc089b330163',
            'mch_id' => '1621729151',
            'key' => 'c651fcbe10fe8146ed4adfbbd1b377d7',
            'mode' => 'normal',
            'sub_mch_id' => '',
            'sub_appid' => '',
            'sub_app_id' => '',
            'sub_miniapp_id' => 'wx4a875d9edb67e0e7',
            'notify_url' => '/addons/epay/api/notifyx/type/wechat',
            'cert_client' => '/addons/epay/certs/apiclient_cert.pem',
            'cert_key' => '/addons/epay/certs/apiclient_key.pem',
            'log' => '1',
        ],
        'rule' => '',
        'msg' => '',
        'tip' => '微信参数配置',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'alipay',
        'title' => '支付宝',
        'type' => 'array',
        'content' => [],
        'value' => [
            'app_id' => '',
            'mode' => 'normal',
            'notify_url' => '/addons/epay/api/notifyx/type/alipay',
            'return_url' => '/addons/epay/api/returnx/type/alipay',
            'ali_public_key' => '',
            'private_key' => '',
            'log' => '1',
            'scanpay' => '1',
        ],
        'rule' => 'required',
        'msg' => '',
        'tip' => '支付宝参数配置',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => '__tips__',
        'title' => '温馨提示',
        'type' => 'array',
        'content' => [],
        'value' => '请注意微信支付证书路径位于/addons/epay/certs目录下，请替换成你自己的证书<br>appid：APP的appid<br>app_id：公众号的appid<br>app_secret：公众号的secret<br>miniapp_id：小程序ID<br>mch_id：微信商户ID<br>key：微信商户支付的密钥',
        'rule' => '',
        'msg' => '',
        'tip' => '微信参数配置',
        'ok' => '',
        'extend' => '',
    ],
];
