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



#author:唐哲
#desc:修改attr_id 自增长

ALTER TABLE `allwood_attribute`
MODIFY COLUMN `attr_id`  int(10) NOT NULL AUTO_INCREMENT COMMENT '属性id' FIRST ;

ALTER TABLE `allwood_brand_style`
MODIFY COLUMN `id`  int(11) NOT NULL AUTO_INCREMENT COMMENT 'id' FIRST ;

ALTER TABLE `allwood_goods_class`
ADD COLUMN `brand`  text NULL COMMENT '关联品牌' AFTER `gc_url`,
ADD COLUMN `spec`  text NULL COMMENT '关联规格' AFTER `brand`,
ADD COLUMN `attr`  text NULL COMMENT '关联属性' AFTER `spec`;


