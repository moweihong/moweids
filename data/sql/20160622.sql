
#author:鲁仕鑫
#date:20160622
#desc:微信登录支持
insert  into `allwood_setting`(`name`,`value`) values ('weixin_appid','0'),('weixin_secret','0'),('weixin_isuse','0');


#author:梅俊
#data:20160622
#desc: 商品分享
CREATE TABLE `allwood_goods_share` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `shop_id` INT(11) NOT NULL DEFAULT '0' COMMENT '店铺ID',
    `goods_id` INT(11) NOT NULL DEFAULT '0' COMMENT '商品的ID',
    `add_time` INT(11) NOT NULL DEFAULT '0' COMMENT '建立表时间',
    `start_time` INT(11) NOT NULL DEFAULT '0' COMMENT '活动起始时间',
    `end_time` INT(11) NOT NULL DEFAULT '0' COMMENT '活动结束时间',
    `money` DECIMAL(10,2) NOT NULL DEFAULT '0.00' COMMENT '单价',
    `share_money` DECIMAL(6,4) NOT NULL DEFAULT '0.0000' COMMENT '佣金比例',
    `goods_status` INT(11) NULL DEFAULT '1' COMMENT '0:下架 1：上架',
    `tesu_deleted` TINYINT(1) NULL DEFAULT '0' COMMENT '是否删除',
    `tesu_description` VARCHAR(50) NULL DEFAULT NULL COMMENT '描述',
    `tesu_created` INT(11) NULL DEFAULT '0' COMMENT '创建时间',
    PRIMARY KEY (`id`),
    UNIQUE INDEX `goods_id` (`goods_id`),
    INDEX `shop_id_goods_satus` (`shop_id`, `goods_status`)
)
COMMENT='用于记录参与分销活动的产品'
COLLATE='utf8_general_ci'
ENGINE=MyISAM;

CREATE TABLE `allwood_goods_share_seller` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `goods_share_id` INT(11) NOT NULL DEFAULT '0' COMMENT '与goods_share_id关联',
    `store_id` INT(11) NOT NULL DEFAULT '0' COMMENT '店铺ID',
    `goods_id` INT(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
    `order_id` INT(11) NOT NULL DEFAULT '0' COMMENT '订单号',
    `userid` INT(11) NOT NULL DEFAULT '0' COMMENT '分销者ID',
    `seller_id` INT(11) NOT NULL DEFAULT '0' COMMENT '购买者ID',
    `num` INT(11) NOT NULL DEFAULT '0' COMMENT '购买数量',
    `rec_id` INT(11) NOT NULL DEFAULT '0' COMMENT '订单详情',
    `goods_value` INT(11) NOT NULL DEFAULT '0' COMMENT '商品单价',
    `goods_pay_price` DECIMAL(11,2) NOT NULL DEFAULT '0.00' COMMENT '实际成交金额',
    `cps_percent` DECIMAL(4,2) NOT NULL DEFAULT '0.00' COMMENT '策略佣金比例',
    `percent` DECIMAL(4,2) NOT NULL DEFAULT '0.00' COMMENT '当前的分佣比例',
    `addtime` INT(11) NOT NULL DEFAULT '0' COMMENT '增加时间',
    `status` INT(11) NOT NULL DEFAULT '0' COMMENT '0： 已经付款 1：验收:2退款',
    `r_money` DECIMAL(10,2) NOT NULL DEFAULT '0.00' COMMENT '预计返佣',
    `tesu_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '是否删除',
    `tesu_description` VARCHAR(50) NOT NULL COMMENT '描述',
    `tesu_created` INT(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
    PRIMARY KEY (`id`),
    INDEX `goods_share_id` (`goods_share_id`),
    INDEX `show_user_id` (`userid`),
    INDEX `rec_id` (`rec_id`)
)
COMMENT='记录分销卖出的产品'
COLLATE='utf8_general_ci'
ENGINE=MyISAM
;

CREATE TABLE `allwood_goods_hit` (
    `hit_id` INT(11) NOT NULL AUTO_INCREMENT,
    `goods_id` INT(11) NULL DEFAULT '0' COMMENT '产品ID',
    `user_id` INT(11) NULL DEFAULT '0' COMMENT '分享者ID',
    `add_time` INT(11) NULL DEFAULT '0' COMMENT '添加的时间',
    `way1` INT(11) NULL DEFAULT '0' COMMENT '第三方分享',
    `way2` INT(11) NULL DEFAULT '0' COMMENT '链接分享',
    `way3` INT(11) NULL DEFAULT '0' COMMENT '二维码分享',
    `tesu_deleted` TINYINT(1) NULL DEFAULT '0' COMMENT '是否删除',
    `tesu_description` VARCHAR(50) NULL DEFAULT NULL COMMENT '描述',
    `tesu_created` INT(11) NULL DEFAULT '0' COMMENT '创建时间',
    PRIMARY KEY (`hit_id`),
    UNIQUE INDEX `goods_id_user_id_add_time` (`goods_id`, `user_id`, `add_time`)
)
COMMENT='分享点击数目'
COLLATE='utf8_general_ci'
ENGINE=MyISAM
;

CREATE TABLE `allwood_goods_hit_list` (
    `list_id` INT(11) NOT NULL AUTO_INCREMENT,
    `hit_id` INT(11) NOT NULL DEFAULT '0' COMMENT '点击对应的id',
    `order_id` INT(11) NOT NULL DEFAULT '0' COMMENT '订单号',
    `rec_id` INT(11) NOT NULL DEFAULT '0' COMMENT '订单明细',
    `orders_cnt` INT(11) NOT NULL DEFAULT '0' COMMENT '订单数',
    `goods_pay_price` DECIMAL(11,2) NOT NULL DEFAULT '0.00' COMMENT '有效订单金额',
    `cps_percent` DECIMAL(4,2) NOT NULL DEFAULT '0.00' COMMENT '策略比率',
    `percent` DECIMAL(4,2) NOT NULL DEFAULT '0.00' COMMENT '产品分佣比率',
    `r_money` DECIMAL(10,2) NOT NULL DEFAULT '0.00' COMMENT '预估收益',
    `addtime` INT(11) NOT NULL COMMENT '购买时间',
    `tesu_deleted` TINYINT(1) NULL DEFAULT '0' COMMENT '是否删除',
    `tesu_description` VARCHAR(50) NULL DEFAULT NULL COMMENT '描述',
    `tesu_created` INT(11) NULL DEFAULT '0' COMMENT '创建时间',
    PRIMARY KEY (`list_id`)
)
COMMENT='通过点击引进来的订单'
COLLATE='utf8_general_ci'
ENGINE=MyISAM
;
CREATE TABLE `allwood_order_store` (
    `order_store_id` INT(11) NOT NULL AUTO_INCREMENT,
    `order_id` INT(11) NOT NULL DEFAULT '0' COMMENT '订单号',
    `rec_id` INT(11) NOT NULL DEFAULT '0' COMMENT '明细索引',
    `user1_id` INT(11) NOT NULL DEFAULT '0' COMMENT '一级用户ID',
    `user2_id` INT(11) NOT NULL DEFAULT '0' COMMENT '二级用户ID',
    `orders_cnt` INT(11) NOT NULL DEFAULT '0' COMMENT '购买数量',
    `percent` DECIMAL(4,2) NOT NULL DEFAULT '0.00' COMMENT '当前店铺返佣比率',
    `cps_percent` DECIMAL(4,2) NOT NULL DEFAULT '0.00' COMMENT '策略返佣比率',
    `goods_pay_price` DECIMAL(10,2) NOT NULL DEFAULT '0.00' COMMENT '实际成交价格',
    `r_money` DECIMAL(10,2) NOT NULL DEFAULT '0.00' COMMENT '预计返佣',
    `addtime` INT(11) NOT NULL COMMENT '购买时间',
    `tesu_deleted` TINYINT(1) NULL DEFAULT '0' COMMENT '是否删除',
    `tesu_description` VARCHAR(50) NULL DEFAULT NULL COMMENT '描述',
    `tesu_created` INT(11) NULL DEFAULT '0' COMMENT '创建时间',
    PRIMARY KEY (`order_store_id`),
    INDEX `rec_id` (`rec_id`)
)
COMMENT='店铺卖出的产品'
COLLATE='utf8_general_ci'
ENGINE=MyISAM
;

CREATE TABLE `allwood_cps_policy` (
    `cps_id` INT(11) NOT NULL AUTO_INCREMENT,
    `cps_type` TINYINT(4) NULL DEFAULT '0' COMMENT '1：商户交易提成，0：商品佣金抽佣',
    `policy_name` VARCHAR(50) NULL DEFAULT '0' COMMENT '策略名称',
    `commission_rate` DECIMAL(6,4) NULL DEFAULT '0.0000' COMMENT '佣金比例',
    `is_permanent` TINYINT(4) NULL DEFAULT '0' COMMENT '永久标志，0:不是，1：是',
    `begin_time` INT(11) NULL DEFAULT '0' COMMENT '开始时间',
    `end_time` INT(11) NULL DEFAULT '0' COMMENT '结束时间',
    `check_status` TINYINT(4) NULL DEFAULT '0' COMMENT '状态：0：待审核，1：有效，2：无效',
    `tesu_deleted` TINYINT(1) NULL DEFAULT '0' COMMENT '是否删除',
    `tesu_description` VARCHAR(50) NULL DEFAULT NULL COMMENT '描述',
    `tesu_created` INT(11) NULL DEFAULT '0' COMMENT '创建时间',
    PRIMARY KEY (`cps_id`)
)
COMMENT='策略'
COLLATE='utf8_general_ci'
ENGINE=MyISAM
;