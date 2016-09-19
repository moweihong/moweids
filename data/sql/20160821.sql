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
#author:聊兆彬
#desc:拓展乐购基本信息表
ALTER TABLE allwood_easypay_application ADD COLUMN  `graduate_school` varchar(100) DEFAULT NULL COMMENT '毕业学校';
ALTER TABLE allwood_easypay_application ADD COLUMN  `graduate_time` datetime DEFAULT NULL COMMENT '毕业时间';
ALTER TABLE allwood_easypay_application ADD COLUMN  `hiredate` datetime DEFAULT NULL COMMENT '入职时间';

#author:张小龙
#desc:工厂风格表
CREATE TABLE `allwood_brand_style` (
  `id` int(11) NOT NULL COMMENT 'id',
  `style_name` varchar(255) NOT NULL COMMENT '工厂风格名',
  `addtime` varchar(255) NOT NULL COMMENT '添加时间',
  `status` int(11) DEFAULT 1 COMMENT '状态 1 为开启 0 为禁用',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='工厂风格';
