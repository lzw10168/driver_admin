
-- ----------------------------
-- Table structure for __PREFIX__ddrive_apply
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__ddrive_apply` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '申请用户',
  `name` varchar(255) NOT NULL COMMENT '姓名',
  `image` varchar(255) NOT NULL COMMENT '照片',
  `driving_age` int(11) NOT NULL COMMENT '驾龄/年',
  `mobile` varchar(11) NOT NULL COMMENT '手机号',
  `card_id` varchar(20) NOT NULL COMMENT '身份证号',
  `card_image` varchar(255) NOT NULL COMMENT '身份证正面',
  `card_back_image` varchar(255) NOT NULL COMMENT '身份证背面',
  `driver_image` varchar(255) NOT NULL COMMENT '驾驶证',
  `status` enum('-1','0','1') NOT NULL DEFAULT '0' COMMENT '状态:-1=拒绝,0=待审核,1=审核通过',
  `createtime` int(11) NOT NULL COMMENT '申请时间',
  `remark` text COMMENT '备注',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='代驾申请表';

-- ----------------------------
-- Table structure for __PREFIX__ddrive_banner
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__ddrive_banner` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT '名称',
  `image` varchar(255) DEFAULT NULL COMMENT '图片',
  `weigh` int(11) NOT NULL DEFAULT '0' COMMENT '权重',
  `createtime` int(10) NOT NULL COMMENT '创建时间',
  `updatetime` int(10) NOT NULL COMMENT '更新时间',
  `effectime` int(10) NOT NULL COMMENT '生效时间',
  `expiretime` int(10) NOT NULL COMMENT '到期时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='轮播图表';

-- ----------------------------
-- Records of __PREFIX__ddrive_banner
-- ----------------------------
BEGIN;
INSERT INTO `__PREFIX__ddrive_banner` VALUES (1, '轮播图', 'https://iph.href.lu/500x278?text=%E8%AF%B7%E5%9C%A8%E5%90%8E%E5%8F%B0%E6%B7%BB%E5%8A%A0%E8%BD%AE%E6%92%AD%E5%9B%BE', 1, 1580966306, 1580966306, 1580966262, 1648703862);
COMMIT;

-- ----------------------------
-- Table structure for __PREFIX__ddrive_feedback
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__ddrive_feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '用户',
  `contact` varchar(150) DEFAULT NULL COMMENT '联系方式',
  `type` varchar(255) NOT NULL COMMENT '意见类型',
  `content` text NOT NULL COMMENT '详细描述',
  `createtime` int(11) NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='意见反馈表';

-- ----------------------------
-- Table structure for __PREFIX__ddrive_message
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__ddrive_message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL COMMENT '用户',
  `title` varchar(255) NOT NULL COMMENT '标题',
  `content` text COMMENT '内容',
  `images` varchar(1000) DEFAULT NULL COMMENT '图片',
  `createtime` int(11) NOT NULL COMMENT '发布时间',
  `weigh` int(11) NOT NULL DEFAULT '0' COMMENT '权重',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='话题表';

-- ----------------------------
-- Table structure for __PREFIX__ddrive_message_comment
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__ddrive_message_comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '用户',
  `message_id` int(11) NOT NULL COMMENT '话题',
  `comment` text NOT NULL COMMENT '评论内容',
  `createtime` int(11) NOT NULL COMMENT '评论时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='话题评论';

-- ----------------------------
-- Table structure for __PREFIX__ddrive_order
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__ddrive_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '用户',
  `mobile` varchar(20) NOT NULL COMMENT '联系电话',
  `driver_id` int(11) DEFAULT NULL COMMENT '代驾人',
  `start` varchar(255) NOT NULL COMMENT '出发位置',
  `start_city` varchar(255) NOT NULL COMMENT '出发城市',
  `start_address` varchar(255) NOT NULL COMMENT '出发地址',
  `start_latitude` varchar(255) NOT NULL COMMENT '出发维度',
  `start_longitude` varchar(255) NOT NULL COMMENT '出发经度',
  `end` varchar(255) NOT NULL COMMENT '目的地位置',
  `end_city` varchar(255) NOT NULL COMMENT '目的地城市',
  `end_address` varchar(255) NOT NULL COMMENT '目的地地址',
  `end_latitude` varchar(255) NOT NULL COMMENT '目的地维度',
  `end_longitude` varchar(255) NOT NULL COMMENT '目的地经度',
  `distance` decimal(10,2) DEFAULT '0.00' COMMENT '距离',
  `duration` decimal(20,2) DEFAULT '0.00' COMMENT '时间',
  `estimated_price` decimal(10,2) DEFAULT '0.00' COMMENT '预计费用',
  `price` decimal(10,2) DEFAULT '0.00' COMMENT '实际费用',
  `status` enum('0','1','2','3','-1','99','4') NOT NULL DEFAULT '0' COMMENT '状态:-1=已取消,0=呼叫中,1=已接单,2=进行中,3=待支付,4=司机已到达,99=已完成',
  `comment` enum('0','1') NOT NULL DEFAULT '0' COMMENT '评价:0=未评价,1=已评价',
  `reachtime` int(10) NOT NULL COMMENT '司机到达时间',
  `createtime` int(10) NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='订单表';

-- ----------------------------
-- Table structure for __PREFIX__ddrive_order_comment
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__ddrive_order_comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `order_id` int(11) NOT NULL COMMENT '订单id',
  `driver_id` int(11) NOT NULL COMMENT '司机id',
  `score` int(11) NOT NULL COMMENT '评分',
  `createtime` int(11) NOT NULL COMMENT '时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='订单评分表';

-- ----------------------------
-- Table structure for __PREFIX__ddrive_order_location
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__ddrive_order_location` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL COMMENT '订单id',
  `latitude` varchar(255) NOT NULL COMMENT '维度',
  `longitude` varchar(255) NOT NULL COMMENT '经度',
  `distance` decimal(10,2) NOT NULL COMMENT '路程',
  `type` enum('1','2') NOT NULL COMMENT '类型:1=起始位置,2=最后位置',
  `createtime` int(11) NOT NULL COMMENT '时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='订单位置表';

-- ----------------------------
-- Table structure for __PREFIX__ddrive_user_token
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__ddrive_user_token` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `openid` varchar(50) NOT NULL COMMENT 'openid',
  `wx_name` varchar(150) DEFAULT NULL COMMENT '微信名称',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='用户openid';

-- ----------------------------
-- Table structure for __PREFIX__ddrive_withdraw
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__ddrive_withdraw` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL COMMENT '用户id',
  `money` decimal(10,2) NOT NULL COMMENT '提现金额',
  `createtime` int(10) NOT NULL COMMENT '提现时间',
  `status` enum('0','1','-1') NOT NULL DEFAULT '0' COMMENT '状态:0=待处理,1=提现成功',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='提现表';

-- ----------------------------
-- Table structure for __PREFIX__cash
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__cash` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '序列ID',
  `user_id` int(10) DEFAULT NULL COMMENT '会员Id',
  `pay_type` tinyint(1) DEFAULT '1' COMMENT '支付方式:1=微信,2=支付宝',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态:1=待审核,2=已打款,3=已退回',
  `money` decimal(10,2) DEFAULT '0.00' COMMENT '提现金额',
  `actual_payment` decimal(10,2) DEFAULT '0.00' COMMENT '实付金额',
  `commission` decimal(10,2) DEFAULT '0.00' COMMENT '手续费',
  `account_number` varchar(50) DEFAULT NULL COMMENT '收款账号',
  `payee` varchar(50) DEFAULT NULL COMMENT '收款人',
  `admin_id` int(10) DEFAULT '0' COMMENT '操作员',
  `failure_cause` varchar(255) DEFAULT NULL COMMENT '失败原因',
  `createtime` int(10) DEFAULT NULL COMMENT '创建时间',
  `updatetime` int(10) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='APP端提现表';

-- ----------------------------
-- Table structure for __PREFIX__details
-- ----------------------------
CREATE TABLE IF NOT EXISTS  `__PREFIX__details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `fluctuate_type` tinyint(2) NOT NULL DEFAULT '1' COMMENT '变动类型:1=增加,2减少',
  `msg` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '变动说明',
  `amount` int(10) DEFAULT NULL COMMENT '变动金额',
  `assets_type` tinyint(1) DEFAULT '1' COMMENT '资产类型:1=提现,2=接单',
  `source_type` tinyint(1) DEFAULT '1' COMMENT '来源类型:1=提现,2=接单',
  `createtime` int(11) DEFAULT NULL COMMENT '创建时间',
  `form_id` int(10) DEFAULT '0' COMMENT '来源id',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='会员资金明细表';

-- ----------------------------
-- Table structure for __PREFIX__driver_verified
-- ----------------------------
CREATE TABLE IF NOT EXISTS  `__PREFIX__driver_verified` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL COMMENT '用户id',
  `sign_province` int(10) NOT NULL COMMENT '报名省份',
  `sign_city` int(10) NOT NULL COMMENT '报名城市',
  `province` int(10) NOT NULL COMMENT '省',
  `city` int(10) NOT NULL COMMENT '市',
  `area` int(10) NOT NULL COMMENT '区',
  `driver_license` varchar(50) NOT NULL COMMENT '驾驶证',
  `driver_front_image` varchar(150) NOT NULL COMMENT '驾驶证主页',
  `driver_back_image` varchar(150) NOT NULL COMMENT '驾驶证副页',
  `status` enum('-1','0','1') NOT NULL DEFAULT '0' COMMENT '审核状态:-1=拒绝,0=待审核,1=已认证',
  `fail_reason` text COMMENT '失败理由',
  `createtime` int(10) DEFAULT NULL COMMENT '申请时间',
  `updatetime` int(10) DEFAULT NULL COMMENT '认证时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='驾驶证认证表';

-- ----------------------------
-- Table structure for __PREFIX__card_verified
-- ----------------------------
CREATE TABLE IF NOT EXISTS  `__PREFIX__card_verified` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL COMMENT '用户id',
  `sign_province` int(10) DEFAULT NULL COMMENT '报名省份',
  `sign_city` int(10) DEFAULT NULL COMMENT '报名城市',
  `province` int(10) DEFAULT NULL COMMENT '省',
  `city` int(10) DEFAULT NULL COMMENT '城市',
  `area` int(10) DEFAULT NULL COMMENT '区',
  `card_brand` varchar(150) DEFAULT NULL COMMENT '车辆品牌',
  `number_plate` varchar(80) DEFAULT NULL COMMENT '车牌号',
  `card_front_image` varchar(150) DEFAULT NULL COMMENT '行驶证主页',
  `card_back_image` varchar(150) DEFAULT NULL COMMENT '行驶证副页',
  `status` enum('-1','0','1') DEFAULT '0' COMMENT '状态:-1=拒绝,0=待审核,1=已认证',
  `fail_reason` text COMMENT '失败理由',
  `createtime` int(10) DEFAULT NULL COMMENT '申请时间',
  `updatetime` int(10) DEFAULT NULL COMMENT '认证时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='车主认证表';

-- ----------------------------
-- Table structure for __PREFIX__new_category
-- ----------------------------
CREATE TABLE IF NOT EXISTS  `__PREFIX__new_category` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT '分类名称',
  `createtime` int(11) DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COMMENT='新闻分类';

-- ----------------------------
-- Table structure for __PREFIX__new_info
-- ----------------------------
CREATE TABLE IF NOT EXISTS  `__PREFIX__new_info` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL COMMENT '标题',
  `keywords` varchar(255) DEFAULT NULL COMMENT '关键词',
  `abstract` varchar(255) DEFAULT NULL COMMENT '摘要',
  `author` varchar(255) DEFAULT NULL COMMENT '作者',
  `view_num` int(10) DEFAULT '0' COMMENT '浏览数',
  `new_info_img` varchar(255) DEFAULT NULL COMMENT '列表log',
  `content` text COMMENT '资讯内容',
  `category_id` int(10) DEFAULT NULL COMMENT '分类ID',
  `status` enum('0','1') DEFAULT '1' COMMENT '状态:0=隐藏,1=显示',
  `createtime` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COMMENT='新闻资讯表';

-- ----------------------------
-- Table structure for __PREFIX__real_verified
-- ----------------------------
CREATE TABLE IF NOT EXISTS  `__PREFIX__real_verified` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL COMMENT '用户id',
  `truename` varchar(80) DEFAULT NULL COMMENT '真实姓名',
  `idcard` varchar(100) DEFAULT NULL COMMENT '身份证号',
  `front_card_image` varchar(150) DEFAULT NULL COMMENT '身份证正面照',
  `back_card_image` varchar(150) DEFAULT NULL COMMENT '身份证反面照',
  `status` enum('-1','0','1') DEFAULT '0' COMMENT '认证状态:-1=拒绝,0=待审核,1=已认证',
  `fail_reason` text COMMENT '失败理由',
  `createtime` int(10) DEFAULT NULL COMMENT '申请时间',
  `updatetime` int(10) DEFAULT NULL COMMENT '认证时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='实名认证表';

-- ----------------------------
-- Table structure for __PREFIX__user_verified
-- ----------------------------
CREATE TABLE IF NOT EXISTS  `__PREFIX__user_verified` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL COMMENT '用户id',
  `real_verified` tinyint(1) NOT NULL DEFAULT '0' COMMENT '认证状态:-1=已拒绝,0=未认证,1=已认证,2=待审核',
  `driver_verified` tinyint(1) NOT NULL DEFAULT '0' COMMENT '认证状态:-1=已拒绝,0=未认证,1=已认证,2=待审核',
  `card_verified` tinyint(1) NOT NULL DEFAULT '0' COMMENT '认证状态:-1=已拒绝,0=未认证,1=已认证,2=待审核',
  `createtime` int(10) DEFAULT NULL COMMENT '创建时间',
  `updatetime` int(10) DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='用户认证状态表';
