

#author:林吉
#data:20160530
#desc:体验店表
CREATE TABLE `allwood_brick_store` (
  `brickstore_id` int(11) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) DEFAULT NULL,
  `brickstore_name` varchar(60) NOT NULL,
  `brickstore_tel` varchar(30) DEFAULT NULL,
  `brickstore_phone` varchar(20) NOT NULL,
  `brickstore_area_id` varchar(40) NOT NULL,
  `brickstore_city_id` varchar(40) NOT NULL,
  `brickstore_province_id` varchar(40) NOT NULL,
  `brickstore_address` varchar(140) NOT NULL,
  `brickstore_thumb` varchar(200) NOT NULL,
  `brickstore_opentime` varchar(100) NOT NULL,
  `brickstore_closetime` varchar(100) NOT NULL,
  `brickstore_goods` text,
  `brickstore_delete` int(2) DEFAULT '0' COMMENT '0正常 1 删除',
  `tesu_deleted` int(10) NOT NULL DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(300) DEFAULT NULL,
  `tesu_created` varchar(300) DEFAULT NULL,
  `brickstore_area_info` varchar(100) NOT NULL,
  PRIMARY KEY (`brickstore_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
