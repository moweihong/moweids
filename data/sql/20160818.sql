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
#desc:工厂设置 经销商是否可以查看自己的店铺 
#allwood_store 表 增加字段 `dealer_verify` tinyint(1) DEFAULT '0' COMMENT '经销商审核 0 需要审核 1不需要审核',

#author:包摇玲
#desc:添加表 经销商和工厂建立友好关系
#CREATE TABLE `allwood_factory_friend` (
#  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
#  `dealer_store_id` int(11) NOT NULL COMMENT '经销商商铺id',
#  `factory_store_id` int(11) NOT NULL COMMENT '工厂商铺id',
#  `c_type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '审核状态 0申请，1通过 2拒绝',
#  `is_look` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '默认0可以查看首页 1不能查看',
#  `is_del` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '默认0，删除1',
#  `description` varchar(255) DEFAULT NULL COMMENT '描述',
#  `created_at` int(11) DEFAULT '0' COMMENT '创建时间',
#  `pass_at` int(11) DEFAULT '0' COMMENT '通过时间',
#  `updated_at` int(11) DEFAULT '0' COMMENT '更新时间',
#  PRIMARY KEY (`id`),
#  KEY `dealer_store_id` (`dealer_store_id`),
#  KEY `factory_store_id` (`factory_store_id`)
#) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='厂家与经销商关系表'

#鲁仕鑫
#desc:自检操作表
CREATE TABLE `allwood_self_correction_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` int(11) NOT NULL COMMENT '类型',
  `operation_desc` varchar(255) NOT NULL COMMENT '操作描述',
  `time` varchar(255) NOT NULL COMMENT '操作时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT "自检日志表";
