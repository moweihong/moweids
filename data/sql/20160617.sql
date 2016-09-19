


#author:林吉
#date:20160617:
#desc:新增店铺导航栏目功能
ALTER TABLE allwood_store_navigation
 ADD COLUMN `sn_gc_id` int(11) DEFAULT '0' COMMENT '分类id';

#author:林吉
#date:20160617:
#desc:判断店铺是否已经自动生成过店铺导航
ALTER TABLE allwood_store_extend
ADD COLUMN  `navigation_autocreate` int(11) DEFAULT '0' COMMENT '分类id';
