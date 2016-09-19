#author:王超洪
#desc:添加地址id
alter table allwood_order_common add column reciver_address_id int(11) not null default 0 COMMENT '收货人地址id（对应address表id）';