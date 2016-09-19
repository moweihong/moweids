
#author:鲁仕鑫
#date:20160625
#desc:提现表
CREATE TABLE `allwood_seller_withdraw` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `withdraw_sn` bigint(20) unsigned NOT NULL COMMENT '单号',
  `member_id` int(11) NOT NULL COMMENT '用户id',
  `time` int(11) NOT NULL COMMENT '操作时间',
  `amount` decimal(11,2) NOT NULL COMMENT '操作金额',
  `tesu_deleted` int(11) DEFAULT NULL COMMENT '是否删除',
  `tesu_created` int(11) DEFAULT NULL COMMENT '创建时间',
  `tesu_description` varchar(300) DEFAULT NULL COMMENT '描述',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='提现表';
