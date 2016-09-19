

#author:林吉
#date:20160601
#desc:敏感词库表
CREATE TABLE `allwood_sensitive_word` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `word` varchar(255) DEFAULT NULL,
  `tesu_deleted` int(10) NOT NULL DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(300) DEFAULT NULL,
  `tesu_created` varchar(300) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;