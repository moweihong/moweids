






#author:刘满红
#date:20160708
#desc:添加体验店
CREATE TABLE `allwood_brick_store_goods` (
  `goods_id` varchar(1000) NOT NULL COMMENT '体验店商品编号',
  `brick_store_id` int(11) NOT NULL COMMENT '体验店编号',
  `brick_store_name` varchar(60) DEFAULT NULL COMMENT '体验店名称',
  `brick_store_phone` varchar(20) DEFAULT NULL COMMENT '体验店手机号',
  `brick_store_address` varchar(140) DEFAULT NULL COMMENT '体验店地址',
  `goodsbrick_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`goodsbrick_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

