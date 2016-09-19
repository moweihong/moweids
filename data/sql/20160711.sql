

ALTER TABLE allwood_cart
ADD COLUMN  `transport_type` tinyint(1) DEFAULT '1' COMMENT '当前使用的运费模板  1 物流 2 快递  3 其它',
ADD COLUMN  `is_easybuy` tinyint(1) DEFAULT '0' COMMENT '是否是分期购 1 为是0 为不是',
ADD COLUMN  `easybuy_amount` int(11) DEFAULT '0' COMMENT '分期总额',
ADD COLUMN  `period` tinyint(1) DEFAULT '0' COMMENT '分期数',
ADD COLUMN  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
ADD COLUMN  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
ADD COLUMN  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间';


