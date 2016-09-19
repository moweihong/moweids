
#author:王彬
#date:20160721
#desc:销售工具终端表
CREATE TABLE `allwood_store_terminal_inf` (
  `terminal_usr_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `usr_name` varchar(48) DEFAULT NULL COMMENT '登录账户',
  `usr_pass` char(32) DEFAULT NULL COMMENT '密码',
  `terminal_id` varchar(16) DEFAULT NULL COMMENT '设备id',
  `store_id` bigint(20) DEFAULT NULL COMMENT '店铺id',
  `possessor` varchar(16) DEFAULT NULL COMMENT '持有人',
  `physical_store` varchar(48) DEFAULT NULL COMMENT '实体店名称',
  `add_time` int(13) DEFAULT NULL COMMENT '添加时间',
  `state` tinyint(4) DEFAULT NULL COMMENT '状态：0=可用,1=禁用',
  `login_ip` varchar(16) DEFAULT NULL COMMENT '最后登录ip',
  `login_time` datetime DEFAULT NULL COMMENT '最后登录时间',
  `boot_update_flag` tinyint(4) DEFAULT NULL COMMENT '启动图片是否更新，0：未更新，1：已更新',
  PRIMARY KEY (`terminal_usr_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;

