
#author:王超洪
#data:20160606
#desc:退款说明、上传凭证
ALTER TABLE allwood_refund_return
ADD COLUMN `tesu_buyer_remark` varchar(300) DEFAULT NULL COMMENT '买家退款退货说明',
ADD COLUMN  `tesu_is_received` tinyint(1) DEFAULT '0' COMMENT '买家退款退货说明',
ADD COLUMN  `tesu_refuse_reason` varchar(300) DEFAULT NULL COMMENT '拒绝原因',
ADD COLUMN  `tesu_refuse_voucher` varchar(500) DEFAULT NULL COMMENT '拒绝收货凭证',
ADD COLUMN  `tesu_voucher` varchar(1000) DEFAULT NULL COMMENT '上传退货退款凭证',
ADD COLUMN  `tesu_wl_voucher` varchar(1000) DEFAULT NULL COMMENT '上传物流信息凭证';

#author:林吉
#data:20150606
#desc:商品goods表拓展
ALTER TABLE allwood_goods
ADD COLUMN  `goods_volume` varchar(40) DEFAULT '0' COMMENT '商品体积',
ADD COLUMN  `customization` varchar(20) DEFAULT NULL COMMENT '是否可定制',
ADD COLUMN  `is_offline` int(3) DEFAULT '0' COMMENT '商品表增加线下标志，线上商品在网站上对外展示，线下商品只在店内pad上展示，0：线上，1：线下';

ALTER TABLE allwood_goods
ADD COLUMN `goods_weight` varchar(40) DEFAULT '0' COMMENT '商品重量';



ALTER TABLE allwood_goods_common
ADD COLUMN `transport_title` varchar(255) DEFAULT NULL,
ADD COLUMN `goods_weight` varchar(40) DEFAULT NULL COMMENT '商品重量',
ADD COLUMN `goods_volume` varchar(40) DEFAULT NULL COMMENT '商品体积',
ADD COLUMN  `customization` varchar(20) DEFAULT NULL COMMENT '是否可定制',
ADD COLUMN `brick_store` varchar(20) DEFAULT NULL COMMENT '体验店id',
ADD COLUMN  `is_offline` int(3) DEFAULT NULL COMMENT '商品表增加线下标志，线上商品在网站上对外展示，线下商品只在店内pad上展示，0：线上，1：线下';


