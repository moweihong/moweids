
#author:鲁仕鑫
#date:20160608
#desc:增加包裹的概念，需要对商品增加物流单号，和商品状态
ALTER TABLE allwood_order_goods
ADD COLUMN `shipping_code` CHAR(32) DEFAULT NULL COMMENT '运单号',
ADD COLUMN `express_id` CHAR(32) DEFAULT NULL COMMENT '物流公司',
ADD COLUMN `order_goods_state` tinyint(1) DEFAULT 30 COMMENT '未签收30  已签收40';