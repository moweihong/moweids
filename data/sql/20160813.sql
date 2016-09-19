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
#desc：卖家申请提现
#表：allwood_pd_cash
#参加字段：pdc_bank_id（银行卡表id ） pdc_company（开卡公司名称） pdc_tx_type(提现类型0个人提现，1商家提现) pdc_store_id(商家id)
#增加了一个类型： pdc_payment_state(提现支付状态 0默认1支付完成 2审核失败 )
#
#author:包摇玲
#desc：新增绑卡表
#CREATE TABLE `allwood_bank` (
#  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
#  `member_id` int(11) NOT NULL COMMENT '用户id',
#  `username` varchar(30) NOT NULL COMMENT '用户名',
#  `bankname` varchar(30) NOT NULL COMMENT '开户行',
#  `bank_type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '银行类型',
#  `banknum` varchar(30) NOT NULL COMMENT '银行卡号',
#  `company` varchar(30) NOT NULL COMMENT '开卡公司名称',
#  `subbranch` varchar(50) DEFAULT NULL COMMENT '开户支行',
#  `province` varchar(50) DEFAULT NULL COMMENT '持卡人身份证',
#  `mobile` varchar(11) DEFAULT NULL COMMENT '手机号',
#  `addr` varchar(50) DEFAULT NULL COMMENT '地址',
#  `addr_detail` varchar(50) DEFAULT NULL COMMENT '详细地址',
#  `is_default` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0不是默认 1是默认',
#  `is_del` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '默认0，删除1',
#  `created_at` int(11) DEFAULT '0' COMMENT '创建时间',
#  `updated_at` int(11) DEFAULT '0' COMMENT '更新时间',
#  PRIMARY KEY (`id`),
#  KEY `member_id` (`member_id`)
#) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='银行卡'
