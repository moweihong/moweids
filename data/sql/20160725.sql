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
#
#4.只允许追加和编辑当前日期对应的sql文件，例如如今天是20160725，今天对数据库的操作应该写到
#20160725.sql文件中，编辑20160724及其以前的文件都是不允许的。
#
##---------------------------------------------------------------------
#


#author:王超洪
#date:20160723
#desc  装修公司
CREATE TABLE `allwood_decorate_company` (
  `de_com_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int(11) unsigned NOT NULL COMMENT '店铺id',
  `company_name` varchar(30) DEFAULT NULL COMMENT '公司名',
  `logo` varchar(100) DEFAULT NULL COMMENT 'logo',
  `province_id` int(11) unsigned DEFAULT NULL COMMENT '省id',
  `city_id` int(11) unsigned DEFAULT NULL COMMENT '市id',
  `area_id` int(11) unsigned DEFAULT NULL COMMENT '区id',
  `mobile` char(11) DEFAULT NULL COMMENT '手机号',
  `phone` char(20) DEFAULT NULL COMMENT '电话',
  `address` varchar(200) DEFAULT NULL COMMENT '地址',
  `description` text COMMENT '描述',
  PRIMARY KEY (`de_com_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;




CREATE TABLE `allwood_decorate_effectdraw` (
  `draw_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `com_id` int(11) unsigned DEFAULT NULL COMMENT 'decorate_company表de_com_id',
  `store_id` int(11) unsigned NOT NULL COMMENT '商店id',
  `title` varchar(50) DEFAULT NULL COMMENT '标题',
  `pic` varchar(250) DEFAULT NULL COMMENT '图片',
  `tesu_deleted` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1:已删除 0：未删除',
  PRIMARY KEY (`draw_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;


CREATE TABLE `allwood_decorate_plan` (
  `de_plan_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int(11) unsigned NOT NULL COMMENT '店铺id',
  `title` varchar(50) DEFAULT NULL COMMENT '标题',
  `house_type` varchar(20) DEFAULT NULL COMMENT '户型',
  `house_address` varchar(50) DEFAULT NULL COMMENT '户型地址',
  `cost` decimal(10,2) DEFAULT '0.00' COMMENT '造价',
  `visit_pwd` varchar(40) DEFAULT NULL COMMENT '访问密码',
  `coverpage` varchar(500) DEFAULT NULL COMMENT '封面',
  `contract_pic` varchar(500) DEFAULT NULL COMMENT '合同',
  `description` text COMMENT '描述',
  `tesu_deleted` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1:已删除 0：未删除',
  `com_id` int(3) NOT NULL DEFAULT '0' COMMENT '公司id',
  `area` decimal(11,2) NOT NULL DEFAULT '0.00' COMMENT '面积',
  PRIMARY KEY (`de_plan_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;




CREATE TABLE `allwood_offline_provisional_customer` (
  `provisional_customer_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `usr_name` varchar(36) DEFAULT NULL COMMENT '客户姓名',
  `usr_tel` varchar(16) DEFAULT NULL COMMENT '客户电话',
  `add_time` datetime DEFAULT NULL COMMENT '增加时间',
  `remark` varchar(128) DEFAULT NULL COMMENT '备注',
  PRIMARY KEY (`provisional_customer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=88 DEFAULT CHARSET=utf8;

#author:鲁仕鑫
#desc:临时订单表
CREATE TABLE `allwood_offline_provisional_order` (
  `provisional_order_id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '临时订单id',
  `order_name` varchar(48) DEFAULT NULL COMMENT '清单名称',
  `customer_id` bigint(20) DEFAULT NULL COMMENT '如果是清单则为临时客户id，如果为预订单则为买家id',
  `store_id` bigint(20) DEFAULT NULL COMMENT '商家id',
  `order_type` tinyint(4) DEFAULT NULL COMMENT '订单标志,0:清单，1：预订单',
  `add_time` datetime DEFAULT NULL COMMENT '添加到预订单时间',
  `terminal_usr_id` bigint(20) DEFAULT NULL COMMENT '终端用户id',
  `is_deal` tinyint(4) DEFAULT NULL COMMENT '订单状态：0：未成交，1：已成交。',
  `remark` varchar(128) DEFAULT NULL COMMENT '备注',
  `create_time` datetime DEFAULT NULL COMMENT '创建清单时间',
  PRIMARY KEY (`provisional_order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=92 DEFAULT CHARSET=utf8;

#author : 刘满红
#desc: 品牌类型

CREATE TABLE `allwood_brand_type` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `brandtypename` varchar(30) NOT NULL COMMENT '品牌类型名称',
  `sort` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '排序',
  PRIMARY KEY (`int`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='品牌类型';


#author:鲁仕鑫
#desc:临时订单goods表
CREATE TABLE `allwood_offline_provisional_order_goods` (
  `order_goods_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `provisional_order_id` bigint(20) DEFAULT NULL COMMENT '清单id',
  `goods_id` bigint(20) DEFAULT NULL COMMENT '商品id',
  `goods_name` varchar(48) DEFAULT NULL COMMENT '商品名称',
  `goods_price` decimal(10,0) DEFAULT NULL COMMENT '商品价格',
  `goods_num` int(11) DEFAULT NULL COMMENT '商品数量',
  `remark` varchar(48) DEFAULT NULL COMMENT '备注',
  PRIMARY KEY (`order_goods_id`)
) ENGINE=InnoDB AUTO_INCREMENT=100 DEFAULT CHARSET=utf8;




#author:鲁仕鑫
#desc:销售工具开机广告
CREATE TABLE `allwood_terminal_boot_image` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `boot_image` varchar(48) DEFAULT NULL COMMENT '启动图片',
  `add_time` datetime DEFAULT NULL COMMENT '添加日期',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;




#author:王超洪
#desc:装修效果图
CREATE TABLE `allwood_decorate_effectdraw` (
  `draw_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `com_id` int(11) unsigned DEFAULT NULL COMMENT 'decorate_company表de_com_id',
  `store_id` int(11) unsigned NOT NULL COMMENT '商店id',
  `title` varchar(50) DEFAULT NULL COMMENT '标题',
  `pic` varchar(250) DEFAULT NULL COMMENT '图片',
  `tesu_deleted` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1:已删除 0：未删除',
  PRIMARY KEY (`draw_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;