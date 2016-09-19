

#author:王超洪
#data:20160602
#desc:商品增加下架时间
ALTER TABLE allwood_goods
ADD COLUMN `goods_weight` varchar(40) DEFAULT '0' COMMENT '商品重量',
ADD COLUMN `goods_volume` varchar(40) DEFAULT '0' COMMENT '商品体积',
ADD COLUMN `customization` varchar(20) DEFAULT NULL COMMENT '是否可定制',
ADD COLUMN `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
ADD COLUMN `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
ADD COLUMN `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
ADD COLUMN `is_offline` int(3) DEFAULT '0' COMMENT '商品表增加线下标志，线上商品在网站上对外展示，线下商品只在店内pad上展示，0：线上，1：线下',
ADD COLUMN `tesu_offline_time` int(11) DEFAULT 0 NOT NULL COMMENT '商品下架时间';


ALTER TABLE allwood_goods_common

ADD COLUMN `goods_weight` varchar(40) DEFAULT NULL COMMENT '商品重量',
ADD COLUMN `goods_volume` varchar(40) DEFAULT NULL COMMENT '商品体积',
ADD COLUMN `customization` varchar(20) DEFAULT NULL COMMENT '是否可定制',
ADD COLUMN `brick_store` varchar(20) DEFAULT NULL COMMENT '体验店id',
ADD COLUMN  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
ADD COLUMN  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
ADD COLUMN  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
ADD COLUMN `is_offline` int(3) DEFAULT NULL COMMENT '商品表增加线下标志，线上商品在网站上对外展示，线下商品只在店内pad上展示，0：线上，1：线下';

ALTER TABLE allwood_goods_common
ADD COLUMN `transport_title` varchar(255) DEFAULT NULL,
ADD COLUMN `tesu_offline_time` int(11) DEFAULT 0 NOT NULL COMMENT '商品下架时间';

#author:鲁仕鑫
#data:20160602
#desc:地址表address中增加省id和邮编
ALTER TABLE allwood_address
ADD COLUMN `zip_code` VARCHAR(300) DEFAULT NULL COMMENT '邮编',
ADD COLUMN `province_id` int(11) DEFAULT 1 COMMENT '省ID';


