
#author:林吉
#data:20160604
#desc:物流模块表修改
ALTER TABLE allwood_transport
ADD COLUMN `area_id` int(11) DEFAULT NULL COMMENT '区域id',
ADD COLUMN `city_id` int(11) DEFAULT NULL,
ADD COLUMN `freightage_type` varchar(255) DEFAULT NULL COMMENT '运费类型 0自定义 1卖家承担',
ADD COLUMN `cash_type` varchar(255) DEFAULT '0' COMMENT '计价方式 0按件数，1按体积，2按重量',
ADD COLUMN `province_id` int(11) DEFAULT NULL COMMENT '省id';

ALTER TABLE allwood_transport_extend
ADD COLUMN `transport_type` varchar(11) DEFAULT '0' COMMENT '运送方式 0按件 1物流，2，快递 ';

