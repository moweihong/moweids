#
#---------------------------------------------------------------------
#
#自我介绍：
#  我一个数据库模板文件，请把数据库的任何更改（create table, alter table )
#用sql的形式写到对应日期的sql文件中。
#
#说明：
#1.创建数据库的目的是实现数据库迭代版本的兼容，所以不允许删除表(drop table)
#2.sql必须添加注释，注释以#开头
#3.sql的书写遵循一定的格式规范，范例如下
#
#author:鲁仕鑫
#desc:后台登陆权限角色表
#CREATE TABLE `allwood_role` (
#  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '索引ID',
#   `name` VARCHAR(100) NOT NULL COMMENT '分类名称',
#  PRIMARY KEY (`id`)
#) ENGINE=INNODB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='后台登陆权限角色表';
#author:包摇玲
#desc:免息分期 添加分类
#CREATE TABLE `allwood_theme_nav` (
#  `type_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '类型id',
#  `type_name` VARCHAR(100) NOT NULL COMMENT '类型名称',
#  `type_sort` TINYINT(1) UNSIGNED NOT NULL COMMENT '排序',
#  `class_id` INT(10) UNSIGNED DEFAULT '0' COMMENT '所属分类id',
#  `class_name` VARCHAR(100) NOT NULL COMMENT '所属分类名称',
#  `tesu_type` TINYINT(1) DEFAULT '0' COMMENT '单独专题的类型',
#  `tesu_description` VARCHAR(50) DEFAULT NULL COMMENT '描述',
#  `tesu_created` INT(11) DEFAULT '0' COMMENT '创建时间',
#  PRIMARY KEY (`type_id`)
#) ENGINE=INNODB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='首页专题添加分类'
#
#
#4.只允许追加和编辑当前日期对应的sql文件，例如如今天是20160725，今天对数据库的操作应该写到
#20160725.sql文件中，编辑20160724及其以前的文件都是不允许的。
#
##---------------------------------------------------------------------
#author:包摇玲
#desc：乐装分期 转账记录表
#CREATE TABLE `allwood_transfer_record` (
#  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
#  `buyer_id` int(11) unsigned NOT NULL COMMENT '买家id',
#  `store_id` int(11) unsigned NOT NULL COMMENT '卖家店铺id',
#  `order_sn` bigint(20) unsigned NOT NULL COMMENT '订单编号',
#  `money` decimal(10,2) DEFAULT '0.00' COMMENT '转账给商家金额',
#  `balance` decimal(10,2) DEFAULT '0.00' COMMENT '剩余金额',
#  `create_time` int(11) NOT NULL COMMENT '转账时间',
#  PRIMARY KEY (`id`),
#  KEY `ordersn` (`order_sn`)
#) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='转账给商家记录'
