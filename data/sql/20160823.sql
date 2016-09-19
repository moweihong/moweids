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
#desc:装修公司，工厂和家居经销商都整合到了 store表中，原来的decorate_complay 要删除掉
DROP TABLE allwood_decorate_company;

#author:鲁仕鑫
#desc:入驻的商家需要关联地域信息，目前没有，需要新增字段
ALTER TABLE allwood_store ADD COLUMN  `province_id` int DEFAULT 1 COMMENT '商家注册的省id';
ALTER TABLE allwood_store ADD COLUMN  `city_id` int DEFAULT 1 COMMENT '商家注册的市id';


#author:鲁仕鑫
#desc:为表格增加注释
#ALTER TABLE test_comments COMMENT = 'This is just to test how to alter comments';

#author:鲁仕鑫
#desc 补充资料表
CREATE TABLE `allwood_more_material` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `addr_province` int(11) NOT NULL COMMENT '省id',
  `addr_city` int(11) NOT NULL COMMENT '市id',
  `addr_county` int(11) NOT NULL COMMENT '区id',
  `addr_street` int(11) NOT NULL COMMENT '详细地址',
  `property_owner` varchar(255) NOT NULL COMMENT '房屋产权人',
  `house_price` decimal(11,2) NOT NULL DEFAULT '0.00' COMMENT '房屋购买价格',
  `design_price` decimal(11,2) NOT NULL DEFAULT '0.00' COMMENT '装修合同价格',
  `design_date` varchar(255) NOT NULL COMMENT '装修合同工期',
  `house_area` decimal(11,2) NOT NULL COMMENT '建筑面积',
  `house_case` int(11) NOT NULL COMMENT '房产情况  1是一次性购买 2按揭购买',
  `pay_way` int(11) NOT NULL COMMENT '装修付款方式  1 一次性付款  2 进度付款 3 其它',
  `plan_id` int(11) DEFAULT NULL COMMENT '方案id',
  `period` int(11) DEFAULT NULL COMMENT '期数',
  `housing_pic_list` varchar(255) NOT NULL COMMENT '房产证合同照片',
  `fitment_pic_list` varchar(255) NOT NULL COMMENT '装修合同照片',
  `other_pic_list` varchar(255) DEFAULT NULL COMMENT '其它征信照片',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COMMENT "补充资料记录表";