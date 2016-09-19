


#author:鲁仕鑫
#date:20160615:
#desc:平台介入需要保存投诉人的个人信息  比如电话号码 邮箱 商品状态 物流名称和物流单号
ALTER TABLE allwood_complain
ADD COLUMN  `tel` varchar(300) DEFAULT NULL COMMENT '电话号码',
ADD COLUMN  `email` varchar(500) DEFAULT NULL COMMENT '邮箱地址',
ADD COLUMN  `logistics` int(11) DEFAULT '0' COMMENT '物流id',
ADD COLUMN  `expressNum` varchar(500) DEFAULT NULL COMMENT '物流单号',
ADD COLUMN  `goods_state` int(11) DEFAULT '0' COMMENT '货物状态',
ADD COLUMN  `refund_id` int(11) DEFAULT '0' COMMENT '退款退货id',
ADD COLUMN  `verdict` int(11) DEFAULT '0' COMMENT '仲裁决定，1 为买方责任 2 为卖方责任',
ADD COLUMN  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
ADD COLUMN  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
ADD COLUMN  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间';



#author:王超洪
#data:20160615
#desc:订单协商记录
CREATE TABLE `allwood_deal_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `order_id` int(11) NOT NULL COMMENT '订单id',
  `refund_id` int(11) NOT NULL COMMENT '退货退款id',
  `goods_id` int(11) NOT NULL COMMENT '订单id',
  `log_msg` varchar(150) DEFAULT '' COMMENT '文字描述',
  `log_time` int(10) unsigned NOT NULL COMMENT '处理时间',
  `log_role` char(2) NOT NULL COMMENT '操作角色',
  `log_user` varchar(30) DEFAULT '' COMMENT '操作人',
  `log_orderstate` int(11) NOT NULL COMMENT '状态 10:发起申请 20:对话 30:撤销退货退款申请',
  `log_voucher` varchar(500) DEFAULT NULL COMMENT '上传图片',
  `tesu_deleted` int(10) NOT NULL DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(300) DEFAULT NULL COMMENT '字段描述',
  `tesu_created` varchar(300) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='订单协商记录表';
