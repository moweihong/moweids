#author:王超洪
#date:201600906
#desc:修改字段类型
ALTER TABLE `allwood_express` modify column `e_state` tinyint(1) DEFAULT 1 COMMENT '0禁用1启用';

#author:唐哲
#desc: 基本配置
insert into allwood_setting (name,value) values('seller_center_banner','');
insert into allwood_setting (name,value) values('seller_center_slide1','');
insert into allwood_setting (name,value) values('seller_center_slide2','');
insert into allwood_setting (name,value) values('seller_center_slide3','');
insert into allwood_setting (name,value) values('seller_center_slide4','');