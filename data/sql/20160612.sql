
ALTER TABLE allwood_order
ADD COLUMN  `tesu_seller_remark` varchar(300) DEFAULT NULL COMMENT '卖家备注',
ADD COLUMN  `order_type` tinyint(1) DEFAULT '1' COMMENT '订单类型 1 普通订单 2 分期购订单 3 其它',
ADD COLUMN  `order_amount_delta` int(11) DEFAULT '0' COMMENT '分期购订单支付差额',
ADD COLUMN  `period` tinyint(1) DEFAULT '0' COMMENT '分期购订单分期期数 默认为0',
ADD COLUMN  `interest_rate` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '利率',
ADD COLUMN  `factorage` int(11) DEFAULT '0' COMMENT '每一期的手续费',
ADD COLUMN  `interest_total` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '总利息',
ADD COLUMN  `gxht_code` char(15) DEFAULT '' COMMENT '购销合同编号',
ADD COLUMN  `fbxy_code` char(15) DEFAULT '' COMMENT '发标协议编号',
ADD COLUMN  `jkxy_code` char(15) DEFAULT '' COMMENT '借款协议编号',
ADD COLUMN  `down_payment_time` int(11) DEFAULT NULL;





ALTER TABLE allwood_order modify column `order_state` enum('0','10','20','30','40','50') DEFAULT NULL COMMENT '订单状态：0(已取消)10(默认):未付款;20:已付款;30:已发货;40:已收货;50卖家处理中（分期购专用状态）';
