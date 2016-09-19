
#author:鲁仕鑫
#data:20160614
#desc:平台介入投诉主题
ALTER TABLE allwood_complain_subject
ADD COLUMN `new_version` int(11) DEFAULT 0 COMMENT '该主题是否只应该在新的买家中心中 1为仅适用在新版本的用户中心，0 为用在老版本';

#插入投诉主题
INSERT  INTO `allwood_complain_subject`(`complain_subject_content`,`complain_subject_desc`,`complain_subject_state`,`tesu_deleted`,`tesu_description`,`tesu_created`,`new_version`) VALUES 
('卖家已签收未处理','卖家已签收未处理',1,0,NULL,0,1),
('卖家拒绝签收','卖家拒绝签收',1,0,NULL,0,1),
('退货地址错误','退货地址错误',1,0,NULL,0,1),
('卖家反馈未收到货','卖家反馈未收到货',1,0,NULL,0,1);