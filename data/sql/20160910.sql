#唐哲
ALTER TABLE `allwood_goods_class`
CHANGE COLUMN `brand` `attr_father`  text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '关联属性值' AFTER `gc_url`;

#author:鲁仕鑫
#money _record 修改字段注释
#m_type    金额类型:2:充值 3:提现 4:交易服务费 5:分期贴息 6:转账 7:v-f 提货 8：c-v 订单  9 乐装 10 乐购


#author:鲁仕鑫
#desc:卖家装修款不能全额提现，增加界限 ，如果当前装修款大于该界限，可以转账差额，小于或者等于该界限不可转账
alter table 'allwood_store' add column `decorate_fund_trans_limit` decimal(11,3) DEFAULT '0.000' COMMENT '不可提现金额';