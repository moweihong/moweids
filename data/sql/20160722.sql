

#author:刘满红
#date:20160722
#desc  发标添加记录每天的发标期数
CREATE TABLE `allwood_tender` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `date` char(20) NOT NULL COMMENT '发标日期',
  `count` int(10) unsigned NOT NULL COMMENT '当日发标数量',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='记录每日发标的期数';
