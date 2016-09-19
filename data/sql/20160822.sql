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


#author:包摇玲
#desc:上架工厂商品到自己店铺记录表
#CREATE TABLE `allwood_up_goods_record` (
#  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'id',
#  `dealer_store_id` INT(11) NOT NULL COMMENT '经销商商铺id',
#  `factory_store_id` INT(11) NOT NULL COMMENT '工厂商铺id',
#  `goods_commonid` INT(10) UNSIGNED NOT NULL COMMENT 'goods_commonid',
#  `created_at` INT(11) DEFAULT '0' COMMENT '创建时间',
#  PRIMARY KEY (`id`),
#  KEY `dealer` (`dealer_store_id`)
#) ENGINE=INNODB DEFAULT CHARSET=utf8 COMMENT='上架工厂商品到自己店铺记录表'

#author:mowei
#desc:汇出账号表
CREATE TABLE `allwood_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account` varchar(100) DEFAULT NULL COMMENT '账户名称',
  `bank_name` varchar(100) DEFAULT NULL COMMENT '银行名称',
  `bank_num` varchar(50) DEFAULT NULL COMMENT '银行卡号',
  `user_name` varchar(50) DEFAULT NULL COMMENT '开户人',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8

#author:包摇玲
#desc:收支明细   model  money_record
#CREATE TABLE `allwood_money_record` (
#  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
#  `member_id` int(11) NOT NULL COMMENT '用户id',
#  `store_id` int(11) NOT NULL DEFAULT '0' COMMENT 'store_id',
#  `order_sn` bigint(20) unsigned DEFAULT '0' COMMENT '订单编号，如果是订单就写入',
#  `unique_sn` bigint(20) unsigned NOT NULL COMMENT '唯一编号',
#  `m_type` tinyint(4) unsigned DEFAULT '0' COMMENT '金额类型:0:订单出账 1:订单入账 2:充值 3:提现 4:交易服务费 5:分期贴息 6:转账',
#  `is_pay` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0：收入 1：支出',
#  `business_type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0个人 1经销商 2家装设计公司 3工厂',
#  `pay_class` varchar(50) DEFAULT NULL COMMENT '记录支付类型',
#  `des` varchar(100) DEFAULT NULL COMMENT '描述记录内容',
#  `money` decimal(10,2) DEFAULT '0.00' COMMENT '记录金额',
#  `yu_e` decimal(10,2) DEFAULT '0.00' COMMENT '余额',
#  `created_at` int(11) DEFAULT '0' COMMENT '创建时间',
#  PRIMARY KEY (`id`),
#  KEY `member_id` (`member_id`),
#  KEY `store_id` (`store_id`)
#) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='收支明细'


#author:包摇铃
#desc:修改收支明细表
#CREATE TABLE `allwood_money_record` (
#  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'id',
#  `member_id` INT(11) NOT NULL COMMENT '用户id',
#  `member_name` VARCHAR(50) NOT NULL COMMENT '会员账号',//新增加
#  `store_id` INT(11) NOT NULL DEFAULT '0' COMMENT 'store_id',
#  `store_name` VARCHAR(50) DEFAULT 'NULL' COMMENT '卖家店铺名称',//新增加 去掉unique_sn
#  `order_sn` BIGINT(20) UNSIGNED DEFAULT '0' COMMENT '订单编号，如果是订单就写入',
#  `m_type` TINYINT(4) UNSIGNED DEFAULT '0' COMMENT '金额类型:0:订单出账 1:订单入账 2:充值 3:提现 4:交易服务费 5:分期贴息 6:转账',
#  `is_pay` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0：收入 1：支出',
#  `business_type` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0个人 1经销商 2家装设计公司 3工厂',
#  `pay_class` VARCHAR(50) DEFAULT NULL COMMENT '记录支付类型',
#  `des` VARCHAR(100) DEFAULT NULL COMMENT '描述记录内容',
#  `money` DECIMAL(10,2) DEFAULT '0.00' COMMENT '记录金额',
#  `yu_e` DECIMAL(10,2) DEFAULT '0.00' COMMENT '余额',
#  `created_at` INT(11) DEFAULT '0' COMMENT '创建时间',
#  PRIMARY KEY (`id`),
#  KEY `member_id` (`member_id`),
#  KEY `store_id` (`store_id`)
#) ENGINE=INNODB AUTO_INCREMENT=1000000 DEFAULT CHARSET=utf8 COMMENT='收支明细'

#author:包摇铃
#desc:修改表结构 allwood_pd_cash
# 修改字段pdc_payment_state 状态 提现支付状态 0默认1审核通过 2审核失败 3汇款成功 4汇款失败
# 添加字段  pdc_store_name(店铺名称)      pdc_check_time(审核时间)  pdc_check_admin(审核管理员)   account_id(汇出账户id)  


