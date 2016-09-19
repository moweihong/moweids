SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS `allwood_bank_type`;
CREATE TABLE `allwood_bank_type` (
  `bank_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bankname` char(255) DEFAULT NULL,
  `state` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`bank_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

#author：鲁仕鑫
#desc:慈善增加默认值
alter table allwood_order alter column order_charity int(11) not null DEFAULT 0;
