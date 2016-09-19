
#author:王超洪
#date:20160719
#desc  分类管理
ALTER TABLE allwood_goods_class
ADD COLUMN  `gc_deep` tinyint(1) DEFAULT 0 COMMENT '深度',
ADD COLUMN  `gc_url` varchar(255) DEFAULT '' COMMENT '专题分类地址';
