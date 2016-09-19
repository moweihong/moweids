

#author:鲁仕鑫
#data:20160624
#desc:卖家财务表
CREATE TABLE `allwood_seller_account_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `time` int(11) NOT NULL COMMENT '时间',
  `member_id` int(11) NOT NULL COMMENT '户主',
  `operation_type` int(11) NOT NULL COMMENT '操作类型',
  `amount` decimal(11,3) DEFAULT '0.000' COMMENT '金额',
  `remaining` decimal(11,3) DEFAULT '0.000' COMMENT '剩余金额',
  `order_sn` bigint(20) unsigned NOT NULL COMMENT '订单单号',
  `bank_ser` int(11) DEFAULT '0' COMMENT '银行流水号',
  `tesu_deleted` int(10) NOT NULL DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(300) DEFAULT NULL COMMENT '字段描述',
  `tesu_created` varchar(300) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='卖家财务表';

#author:鲁仕鑫
#data:20160624
#desc:卖家财务操作类型表
CREATE TABLE `allwood_seller_account_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `name` varchar(300) NOT NULL COMMENT '操作名称',
  `in_or_out` int(11) NOT NULL COMMENT '入账还是出账，1为入账操作，0为出账操作',
  `tesu_deleted` int(11) DEFAULT NULL COMMENT '逻辑删除字段',
  `tesu_description` varchar(300) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
