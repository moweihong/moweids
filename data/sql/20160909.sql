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
#author:鲁仕鑫
#desc:给tender表增加type字段，区分 乐购标 和乐装标
alter table allwood_tender add column type int(11) not null default 0 COMMENT '标的类型 0 为乐购标的，1 为乐装标的';

#author:鲁仕鑫
#desc:给adv_postion 表中 ap_intro 增加默认值

alter table allwood_adv_position modify column ap_intro VARCHAR(255) not null default "" COMMENT "广告位简介";
alter table allwood_adv_position modify column ap_price int(11) not null default 0 COMMENT "广告位单价";
alter table allwood_adv_position modify column adv_num int(11) not null default 0 COMMENT "拥有的广告数";
alter table allwood_adv_position modify column click_num int(11) not null default 0 COMMENT "广告点击数";
alter table allwood_adv_position modify column default_content VARCHAR(256) not null  COMMENT "广告位默认内容";

#author：鲁仕鑫
#desc:增加补充资料表中 房产照片  装修照片 和其它照片字段的长度
alter table allwood_more_material modify column housing_pic_list VARCHAR(1000) not null COMMENT "房产证合同照片";
alter table allwood_more_material modify column fitment_pic_list VARCHAR(1000) not null COMMENT "装修合同照片";
alter table allwood_more_material modify column other_pic_list VARCHAR(1000)   COMMENT "其它征信照片";

#author:刘晨
#desc:给order表增加wx_pay_sn字段，用于微信支付
alter table allwood_order add column wx_pay_sn int(11)  COMMENT '微信支付订单号';

#desc:order_pay表增加recharge_money字段，记录充值金额
alter table allwood_order_pay add column recharge_money DECIMAL(10, 2) not null default 0 COMMENT '充值金额';





