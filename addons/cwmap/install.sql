--
-- 表的结构 `__PREFIX__cwmap_location`
--
--DROP TABLE IF EXISTS `__PREFIX__cwmap_location`;
CREATE TABLE IF NOT EXISTS `__PREFIX__cwmap_location` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `locationname` varchar(100) NOT NULL COMMENT '位置名称',
  `detailaddress` varchar(500) DEFAULT NULL COMMENT '详细地址',
  `longitude` varchar(100) DEFAULT NULL COMMENT '经度',
  `latitude` varchar(100) DEFAULT NULL COMMENT '纬度',
  `phone` varchar(50) DEFAULT NULL COMMENT '电话',
  `email` varchar(50) DEFAULT NULL COMMENT '邮箱',
  `fax` varchar(20) DEFAULT NULL COMMENT '传真',
  `qq` varchar(20) DEFAULT NULL COMMENT 'QQ',
  `website` varchar(50) DEFAULT NULL COMMENT '网址',
  `picture` varchar(255) DEFAULT NULL COMMENT '展示图片',
  `province` varchar(20) DEFAULT NULL COMMENT '省份',
  `createtime` int(11) DEFAULT NULL COMMENT '创建时间',
  `updatetime` int(11) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='地图位置表' ROW_FORMAT=COMPACT;
