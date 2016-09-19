#author:鲁仕鑫
#date:201600905
#desc:修改allwood_pd_recharge的pdr_payment_state 类型
ALTER TABLE `tesu_wood`.`allwood_pd_recharge`   
  CHANGE `pdr_payment_state` `pdr_payment_state` TINYINT DEFAULT 0  NOT NULL  COMMENT '支付状态 0未支付1支付';
ALTER TABLE `tesu_wood`.`allwood_pd_cash`   
  CHANGE `pdc_payment_state` `pdc_payment_state` TINYINT DEFAULT 0  NOT NULL  COMMENT '提现支付状态 0默认1审核通过 2审核失败 3汇款成功 4汇款失败';