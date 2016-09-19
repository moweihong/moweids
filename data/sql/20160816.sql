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
#author:王超洪
#desc  装修添加方案添加风格字段
ALTER TABLE allwood_decorate_plan
ADD COLUMN  `decorate_type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '方案风格，对应allwood_effectdraw_type表id';

#author:鲁仕鑫
#desc:store_joinin 和 store 表中修改 com_type 的枚举值 增加3 
alter table allwood_store modify column com_type enum('1','2', '3') DEFAULT '1' COMMENT '企业类型 1 家居经销商 2 家装设计公司 3 工厂';
alter table allwood_store_joinin modify column com_type enum('1','2', '3') DEFAULT '1' COMMENT '企业类型 1 家居经销商 2 家装设计公司 3 工厂';

#author:鲁仕鑫
#desc:兼容 线下商品，云端商品 经销商发布的商品，修改goods.is_offline goods_common.is_offlien 
alter table allwood_goods modify column is_offline enum('0','1', '2') DEFAULT '0' COMMENT '商品类型 0 经销商发布的商品 1 平板发布的商品 2 工厂发布的商品';
alter table allwood_goods_common modify column is_offline enum('0','1', '2') DEFAULT '0' COMMENT '商品类型 0 经销商发布的商品 1 平板发布的商品 2 工厂发布的商品';

#author:鲁仕鑫
#desc:兼容 普通订单，乐购订单，乐装订单，工厂订单，修改订单order.order_type 字段
alter table allwood_order modify column order_type int(11) DEFAULT '1' COMMENT '订单类型 1 普通订单 2 分期购订单 3 乐装订单 4 工厂订单';

