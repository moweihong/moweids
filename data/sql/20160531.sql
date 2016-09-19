
#author:鲁仕鑫
#date:20160531
#desc:新店入驻过程中填写的主营类目
CREATE TABLE `allwood_store_major_business` (
  `sc_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '索引ID',
  `sc_name` varchar(100) NOT NULL COMMENT '分类名称',
  `tesu_deleted` int(10) NOT NULL DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(300) DEFAULT NULL,
  `tesu_created` varchar(300) DEFAULT NULL,
  PRIMARY KEY (`sc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='新店入驻主营类目表';
