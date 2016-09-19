
#author:王超洪
#data:20160527
#desc:热词表
CREATE TABLE `allwood_hot_word` (
  `hw_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '索引ID',
  `hw_name` varchar(100) NOT NULL COMMENT '分类名称',
  `hw_url` varchar(100) NOT NULL COMMENT 'url',
  `hw_parent_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父ID',
  `gc_id` int(10) unsigned NOT NULL COMMENT '类型id',
  `hw_sort` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '0:不显示 1：显示',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`hw_id`)
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8 COMMENT='商品分类表';