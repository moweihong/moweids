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
#author:鲁仕鑫
#desc:乐装募资流水写入表
CREATE TABLE `allwood_mem_decoratefund` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `userid` int(11) NOT NULL COMMENT 'java数据库user表id',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '入账还是入账 1 入账 0 出账',
  `plan_id` int(11) NOT NULL COMMENT '装修方案id',
  `amount` decimal(11,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
  `descr` varchar(255) DEFAULT NULL COMMENT '描述',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT  '乐装募资流水写入表';
#author:包摇玲
#desc:allwood_daddress 商品公共内容表
#修改 字段 company 默认为空   mobilephone 为varchar(11)

#author:包摇玲
#desc:allwood_goods_common 
#修改 字段 goods_specname 默认不为空  改为  默认可以为空

#author:鲁仕鑫
#desc:order state enum 变 tinyint


#author:鲁仕鑫
#desc: daddress 退货地址表 tel int 改 varchar
