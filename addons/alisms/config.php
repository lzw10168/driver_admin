<?php

return [
    [
        'name' => 'key',
        'title' => '应用key',
        'type' => 'string',
        'content' => [],
        'value' => 'LTAI5t8DAaoy8vEouED1oFrf',
        'rule' => 'required',
        'msg' => '',
        'tip' => '',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'secret',
        'title' => '密钥secret',
        'type' => 'string',
        'content' => [],
        'value' => 'hUCYJiIiTDj54tJ4NKqNkvma9sL5JY',
        'rule' => 'required',
        'msg' => '',
        'tip' => '',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'sign',
        'title' => '签名',
        'type' => 'string',
        'content' => [],
        'value' => '保城代驾',
        'rule' => 'required',
        'msg' => '',
        'tip' => '',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'template',
        'title' => '短信模板',
        'type' => 'array',
        'content' => [],
        'value' => [
            'register' => 'SMS_461555146',
            'resetpwd' => 'SMS_461565151',
            'changepwd' => 'SMS_461550141',
            'changemobile' => 'SMS_461495134',
            'profile' => 'SMS_228018245',
            'notice' => 'SMS_228118125',
            'mobilelogin' => 'SMS_461545132',
            'createorder' => 'SMS_461490137',
            'received' => 'SMS_461555147',
        ],
        'rule' => 'required',
        'msg' => '',
        'tip' => '',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => '__tips__',
        'title' => '温馨提示',
        'type' => 'string',
        'content' => [],
        'value' => '应用key和密钥你可以通过 https://ak-console.aliyun.com/?spm=a2c4g.11186623.2.13.fd315777PX3tjy#/accesskey 获取',
        'rule' => 'required',
        'msg' => '',
        'tip' => '',
        'ok' => '',
        'extend' => '',
    ],
];
