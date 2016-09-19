
#author:刘满红
#date:20160720
#desc  专题页楼层管理
CREATE TABLE `allwood_floor` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `categroyname` varchar(20) NOT NULL COMMENT '楼层名称',
  `sort` tinyint(2) NOT NULL DEFAULT '1' COMMENT '序号',
  `caid` varchar(32) NOT NULL COMMENT '关联外键',
  `pageid` int(11) NOT NULL COMMENT '专题页面编号',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8 COMMENT='专题页面的楼层表';

#author:刘满红
#date:20160720
#desc  专题页楼层关联商品
CREATE TABLE `allwood_floor_goods` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `floorid` varchar(32) NOT NULL COMMENT '楼层编号',
  `goodsid` int(11) NOT NULL COMMENT '商品编号',
  `goodscid` int(11) unsigned DEFAULT NULL COMMENT '商品类别',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=119 DEFAULT CHARSET=utf8 COMMENT='专题页楼层商品';
