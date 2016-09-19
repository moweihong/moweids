
#author:鲁仕鑫
#date:20160607
#desc:退货地址表 daddress 没有省id
ALTER TABLE allwood_daddress
ADD COLUMN `receiver` varchar(300) DEFAULT NULL COMMENT '收货人姓名',
ADD COLUMN `zip_code` varchar(300) DEFAULT NULL COMMENT '邮编',
ADD COLUMN  `province_id` int(11) DEFAULT '1' COMMENT '省ID',
ADD COLUMN  `mobile_phone` int(15) DEFAULT '0' COMMENT '手机号码';


#author:鲁仕鑫
#data:20160607
#desc:商家入驻省，市，区id 记录下来
ALTER TABLE allwood_store_joinin
ADD COLUMN  `province_id` int(11) DEFAULT '1' COMMENT '省ID',
ADD COLUMN  `city_id` int(11) DEFAULT '1' COMMENT '市ID',
ADD COLUMN  `area_id2` int(11) DEFAULT '1' COMMENT '区ID',
ADD COLUMN  `is_discount` tinyint(1) DEFAULT '0' COMMENT '0：不贴息 1：贴息',
ADD COLUMN  `ser_charge` decimal(4,2) DEFAULT '0.00' COMMENT '服务费',
ADD COLUMN  `downpayment` decimal(4,2) DEFAULT '0.00' COMMENT '首期款比例',
ADD COLUMN  `major_business` varchar(300) DEFAULT NULL COMMENT '主营类目',
ADD COLUMN  `hongmu_business` int(3) DEFAULT '0' COMMENT '经营材质是否是红木';

