
#author:鲁仕鑫
#date:20160610
#desc:店家可用账户 和保证金账户
ALTER TABLE allwood_store
ADD COLUMN  `deposit_avaiable` decimal(11,2) DEFAULT '0.00' COMMENT '可用账户',
ADD COLUMN  `deposit_freeze` decimal(11,2) DEFAULT '0.00' COMMENT '保证金账户',
ADD COLUMN  `is_modify_name` tinyint(1) DEFAULT '0' COMMENT '是否修改过店铺名',
ADD COLUMN  `is_discount` tinyint(1) DEFAULT '0' COMMENT '0：不贴息 1：贴息',
ADD COLUMN  `ser_charge` decimal(4,2) DEFAULT '0.00' COMMENT '服务费',
ADD COLUMN  `downpayment` decimal(4,2) DEFAULT '0.00' COMMENT '首期款比例';
