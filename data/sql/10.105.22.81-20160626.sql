/*
SQLyog Ultimate v12.08 (64 bit)
MySQL - 5.6.26-log : Database - allwood
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`allwood` /*!40100 DEFAULT CHARACTER SET utf8 */;

/*Table structure for table `allwood_activity` */

DROP TABLE IF EXISTS `allwood_activity`;

CREATE TABLE `allwood_activity` (
  `activity_id` mediumint(9) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `activity_title` varchar(255) NOT NULL COMMENT '标题',
  `activity_type` enum('1','2') DEFAULT NULL COMMENT '活动类型 1:商品 2:团购',
  `activity_banner` varchar(255) NOT NULL COMMENT '活动横幅大图片',
  `activity_style` varchar(255) NOT NULL COMMENT '活动页面模板样式标识码',
  `activity_desc` varchar(1000) NOT NULL COMMENT '描述',
  `activity_start_date` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始时间',
  `activity_end_date` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '结束时间',
  `activity_sort` tinyint(1) unsigned NOT NULL DEFAULT '255' COMMENT '排序',
  `activity_state` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '活动状态 0为关闭 1为开启',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`activity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='活动表';

/*Table structure for table `allwood_activity_detail` */

DROP TABLE IF EXISTS `allwood_activity_detail`;

CREATE TABLE `allwood_activity_detail` (
  `activity_detail_id` mediumint(9) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `activity_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '活动编号',
  `item_id` int(11) NOT NULL COMMENT '商品或团购的编号',
  `item_name` varchar(255) NOT NULL COMMENT '商品或团购名称',
  `store_id` int(11) NOT NULL COMMENT '店铺编号',
  `store_name` varchar(255) NOT NULL COMMENT '店铺名称',
  `activity_detail_state` enum('0','1','2','3') NOT NULL DEFAULT '0' COMMENT '审核状态 0:(默认)待审核 1:通过 2:未通过 3:再次申请',
  `activity_detail_sort` tinyint(1) unsigned NOT NULL DEFAULT '255' COMMENT '排序',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`activity_detail_id`),
  KEY `activity_id` (`activity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='活动细节表';

/*Table structure for table `allwood_address` */

DROP TABLE IF EXISTS `allwood_address`;

CREATE TABLE `allwood_address` (
  `address_id` mediumint(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '地址ID',
  `member_id` mediumint(10) unsigned NOT NULL DEFAULT '0' COMMENT '会员ID',
  `true_name` varchar(50) NOT NULL COMMENT '会员姓名',
  `area_id` mediumint(10) unsigned NOT NULL DEFAULT '0' COMMENT '地区ID',
  `city_id` mediumint(9) DEFAULT NULL COMMENT '市级ID',
  `area_info` varchar(255) NOT NULL DEFAULT '' COMMENT '地区内容',
  `address` varchar(255) NOT NULL COMMENT '地址',
  `tel_phone` varchar(20) DEFAULT NULL COMMENT '座机电话',
  `mob_phone` varchar(15) DEFAULT NULL COMMENT '手机电话',
  `is_default` enum('0','1') NOT NULL DEFAULT '0' COMMENT '1默认收货地址',
  `zip_code` varchar(300) DEFAULT NULL COMMENT '邮编',
  `province_id` int(11) DEFAULT '1' COMMENT '省ID',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`address_id`),
  KEY `member_id` (`member_id`)
) ENGINE=InnoDB AUTO_INCREMENT=96 DEFAULT CHARSET=utf8 COMMENT='买家地址信息表';

/*Table structure for table `allwood_admin` */

DROP TABLE IF EXISTS `allwood_admin`;

CREATE TABLE `allwood_admin` (
  `admin_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '管理员ID(uid)',
  `admin_name` varchar(20) NOT NULL COMMENT '管理员名称(username)',
  `admin_password` varchar(32) NOT NULL DEFAULT '' COMMENT '管理员密码(userpass)',
  `admin_login_time` int(10) NOT NULL DEFAULT '0' COMMENT '登录时间(logintime)',
  `admin_login_num` int(11) NOT NULL DEFAULT '0' COMMENT '登录次数',
  `admin_is_super` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否超级管理员',
  `admin_gid` smallint(6) DEFAULT '0' COMMENT '权限组ID',
  `useremail` varchar(100) DEFAULT NULL,
  `addtime` int(10) unsigned DEFAULT NULL,
  `loginip` varchar(15) DEFAULT NULL,
  `mid` tinyint(3) unsigned NOT NULL,
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`admin_id`),
  KEY `member_id` (`admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COMMENT='管理员表';

/*Table structure for table `allwood_admin_log` */

DROP TABLE IF EXISTS `allwood_admin_log`;

CREATE TABLE `allwood_admin_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `content` varchar(50) NOT NULL COMMENT '操作内容',
  `createtime` int(10) unsigned DEFAULT NULL COMMENT '发生时间',
  `admin_name` char(20) NOT NULL COMMENT '管理员',
  `admin_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `ip` char(15) NOT NULL COMMENT 'IP',
  `url` varchar(50) NOT NULL DEFAULT '' COMMENT 'act&op',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='管理员操作日志';

/*Table structure for table `allwood_adv` */

DROP TABLE IF EXISTS `allwood_adv`;

CREATE TABLE `allwood_adv` (
  `adv_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '广告自增标识编号',
  `ap_id` mediumint(8) unsigned NOT NULL COMMENT '广告位id',
  `adv_title` varchar(255) NOT NULL COMMENT '广告内容描述',
  `adv_content` varchar(1000) NOT NULL COMMENT '广告内容',
  `adv_start_date` int(10) DEFAULT NULL COMMENT '广告开始时间',
  `adv_end_date` int(10) DEFAULT NULL COMMENT '广告结束时间',
  `slide_sort` int(10) unsigned NOT NULL COMMENT '幻灯片排序',
  `member_id` int(11) NOT NULL COMMENT '会员ID',
  `member_name` varchar(50) NOT NULL COMMENT '会员用户名',
  `click_num` int(10) unsigned NOT NULL COMMENT '广告点击率',
  `is_allow` smallint(1) unsigned NOT NULL COMMENT '会员购买的广告是否通过审核0未审核1审核已通过2审核未通过',
  `buy_style` varchar(10) NOT NULL COMMENT '购买方式',
  `goldpay` int(10) unsigned NOT NULL COMMENT '购买所支付的金币',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`adv_id`)
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8 COMMENT='广告表';

/*Table structure for table `allwood_adv_click` */

DROP TABLE IF EXISTS `allwood_adv_click`;

CREATE TABLE `allwood_adv_click` (
  `adv_id` mediumint(8) unsigned NOT NULL COMMENT '广告id',
  `ap_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '所属广告位id',
  `click_year` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '点击年份',
  `click_month` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '点击月份',
  `click_num` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '点击率',
  `adv_name` varchar(100) NOT NULL COMMENT '广告名称',
  `ap_name` varchar(100) NOT NULL COMMENT '广告位名称',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='广告点击率表';

/*Table structure for table `allwood_adv_position` */

DROP TABLE IF EXISTS `allwood_adv_position`;

CREATE TABLE `allwood_adv_position` (
  `ap_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '广告位置id',
  `ap_name` varchar(100) NOT NULL COMMENT '广告位置名',
  `ap_intro` varchar(255) NOT NULL COMMENT '广告位简介',
  `ap_class` smallint(1) unsigned NOT NULL COMMENT '广告类别：0图片1文字2幻灯3Flash',
  `ap_display` smallint(1) unsigned NOT NULL COMMENT '广告展示方式：0幻灯片1多广告展示2单广告展示',
  `is_use` smallint(1) unsigned NOT NULL COMMENT '广告位是否启用：0不启用1启用',
  `ap_width` int(10) NOT NULL COMMENT '广告位宽度',
  `ap_height` int(10) NOT NULL COMMENT '广告位高度',
  `ap_price` int(10) unsigned NOT NULL COMMENT '广告位单价',
  `adv_num` int(10) unsigned NOT NULL COMMENT '拥有的广告数',
  `click_num` int(10) unsigned NOT NULL COMMENT '广告位点击率',
  `default_content` varchar(100) NOT NULL COMMENT '广告位默认内容',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`ap_id`)
) ENGINE=InnoDB AUTO_INCREMENT=426 DEFAULT CHARSET=utf8 COMMENT='广告位表';

/*Table structure for table `allwood_album_class` */

DROP TABLE IF EXISTS `allwood_album_class`;

CREATE TABLE `allwood_album_class` (
  `aclass_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '相册id',
  `aclass_name` varchar(100) NOT NULL COMMENT '相册名称',
  `store_id` int(10) unsigned NOT NULL COMMENT '所属店铺id',
  `aclass_des` varchar(255) NOT NULL COMMENT '相册描述',
  `aclass_sort` tinyint(3) unsigned NOT NULL COMMENT '排序',
  `aclass_cover` varchar(255) NOT NULL COMMENT '相册封面',
  `upload_time` int(10) unsigned NOT NULL COMMENT '图片上传时间',
  `is_default` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否为默认相册,1代表默认',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`aclass_id`)
) ENGINE=InnoDB AUTO_INCREMENT=256 DEFAULT CHARSET=utf8 COMMENT='相册表';

/*Table structure for table `allwood_album_pic` */

DROP TABLE IF EXISTS `allwood_album_pic`;

CREATE TABLE `allwood_album_pic` (
  `apic_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '相册图片表id',
  `apic_name` varchar(100) NOT NULL COMMENT '图片名称',
  `apic_tag` varchar(255) NOT NULL COMMENT '图片标签',
  `aclass_id` int(10) unsigned NOT NULL COMMENT '相册id',
  `apic_cover` varchar(255) NOT NULL COMMENT '图片路径',
  `apic_size` int(10) unsigned NOT NULL COMMENT '图片大小',
  `apic_spec` varchar(100) NOT NULL COMMENT '图片规格',
  `store_id` int(10) unsigned NOT NULL COMMENT '所属店铺id',
  `upload_time` int(10) unsigned NOT NULL COMMENT '图片上传时间',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`apic_id`)
) ENGINE=InnoDB AUTO_INCREMENT=18158 DEFAULT CHARSET=utf8 COMMENT='相册图片表';

/*Table structure for table `allwood_area` */

DROP TABLE IF EXISTS `allwood_area`;

CREATE TABLE `allwood_area` (
  `area_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '索引ID',
  `area_name` varchar(50) NOT NULL COMMENT '地区名称',
  `area_parent_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '地区父ID',
  `area_sort` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `area_deep` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '地区深度，从1开始',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`area_id`),
  KEY `area_parent_id` (`area_parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=45056 DEFAULT CHARSET=utf8 COMMENT='地区表';

/*Table structure for table `allwood_article` */

DROP TABLE IF EXISTS `allwood_article`;

CREATE TABLE `allwood_article` (
  `article_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '索引id',
  `ac_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '分类id',
  `article_url` varchar(100) DEFAULT NULL COMMENT '跳转链接',
  `article_show` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否显示，0为否，1为是，默认为1',
  `article_sort` tinyint(3) unsigned NOT NULL DEFAULT '255' COMMENT '排序',
  `article_title` varchar(100) DEFAULT NULL COMMENT '标题',
  `article_content` text COMMENT '内容',
  `article_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发布时间',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`article_id`),
  KEY `ac_id` (`ac_id`)
) ENGINE=InnoDB AUTO_INCREMENT=86 DEFAULT CHARSET=utf8 COMMENT='文章表';

/*Table structure for table `allwood_article_class` */

DROP TABLE IF EXISTS `allwood_article_class`;

CREATE TABLE `allwood_article_class` (
  `ac_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '索引ID',
  `ac_code` varchar(20) DEFAULT NULL COMMENT '分类标识码',
  `ac_name` varchar(100) NOT NULL COMMENT '分类名称',
  `ac_parent_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父ID',
  `ac_sort` tinyint(1) unsigned NOT NULL DEFAULT '255' COMMENT '排序',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`ac_id`),
  KEY `ac_parent_id` (`ac_parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COMMENT='文章分类表';

/*Table structure for table `allwood_attribute` */

DROP TABLE IF EXISTS `allwood_attribute`;

CREATE TABLE `allwood_attribute` (
  `attr_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '属性id',
  `attr_name` varchar(100) NOT NULL COMMENT '属性名称',
  `type_id` int(10) unsigned NOT NULL COMMENT '所属类型id',
  `attr_value` text NOT NULL COMMENT '属性值列',
  `attr_show` tinyint(1) unsigned NOT NULL COMMENT '是否显示。0为不显示、1为显示',
  `attr_sort` tinyint(1) unsigned NOT NULL COMMENT '排序',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`attr_id`),
  KEY `attr_id` (`attr_id`,`type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=634 DEFAULT CHARSET=utf8 COMMENT='商品属性表';

/*Table structure for table `allwood_attribute_value` */

DROP TABLE IF EXISTS `allwood_attribute_value`;

CREATE TABLE `allwood_attribute_value` (
  `attr_value_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '属性值id',
  `attr_value_name` varchar(100) NOT NULL COMMENT '属性值名称',
  `attr_id` int(10) unsigned NOT NULL COMMENT '所属属性id',
  `type_id` int(10) unsigned NOT NULL COMMENT '类型id',
  `attr_value_sort` tinyint(1) unsigned NOT NULL COMMENT '属性值排序',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`attr_value_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12186 DEFAULT CHARSET=utf8 COMMENT='商品属性值表';

/*Table structure for table `allwood_bill_history` */

DROP TABLE IF EXISTS `allwood_bill_history`;

CREATE TABLE `allwood_bill_history` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `member_id` int(11) unsigned NOT NULL COMMENT 'member_id',
  `bill_state` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1:收入 2：支出',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
  `add_time` int(11) unsigned NOT NULL COMMENT '产生时间',
  `remark` varchar(300) DEFAULT NULL COMMENT '备注',
  `goods_id` int(11) unsigned NOT NULL COMMENT '账户详细记录id（订单id）',
  `order_id` int(30) unsigned NOT NULL COMMENT '订单id',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=164 DEFAULT CHARSET=utf8;

/*Table structure for table `allwood_brand` */

DROP TABLE IF EXISTS `allwood_brand`;

CREATE TABLE `allwood_brand` (
  `brand_id` mediumint(11) NOT NULL AUTO_INCREMENT COMMENT '索引ID',
  `brand_name` varchar(100) DEFAULT NULL COMMENT '品牌名称',
  `brand_class` varchar(50) DEFAULT NULL COMMENT '类别名称',
  `brand_pic` varchar(100) DEFAULT NULL COMMENT '图片',
  `brand_sort` tinyint(3) unsigned DEFAULT '0' COMMENT '排序',
  `brand_recommend` tinyint(1) DEFAULT '0' COMMENT '推荐，0为否，1为是，默认为0',
  `store_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '店铺ID',
  `brand_apply` tinyint(1) NOT NULL DEFAULT '1' COMMENT '品牌申请，0为申请中，1为通过，默认为1，申请功能是会员使用，系统后台默认为1',
  `class_id` int(10) unsigned DEFAULT '0' COMMENT '所属分类id',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`brand_id`)
) ENGINE=InnoDB AUTO_INCREMENT=416 DEFAULT CHARSET=utf8 COMMENT='品牌表';

/*Table structure for table `allwood_brand_type` */

DROP TABLE IF EXISTS `allwood_brand_type`;

CREATE TABLE `allwood_brand_type` (
  `int` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `brandtypename` varchar(30) NOT NULL COMMENT '品牌类型名称',
  `sort` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '排序',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`int`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='品牌类型';

/*Table structure for table `allwood_brick_store` */

DROP TABLE IF EXISTS `allwood_brick_store`;

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

/*Table structure for table `allwood_brick_store_goods` */

DROP TABLE IF EXISTS `allwood_brick_store_goods`;

CREATE TABLE `allwood_brick_store_goods` (
  `goods_id` varchar(1000) NOT NULL COMMENT '体验店商品编号',
  `brick_store_id` int(11) NOT NULL COMMENT '体验店编号',
  `brick_store_name` varchar(60) DEFAULT NULL COMMENT '体验店名称',
  `brick_store_phone` varchar(20) DEFAULT NULL COMMENT '体验店手机号',
  `brick_store_address` varchar(140) DEFAULT NULL COMMENT '体验店地址',
  `goodsbrick_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`goodsbrick_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

/*Table structure for table `allwood_carousel` */

DROP TABLE IF EXISTS `allwood_carousel`;

CREATE TABLE `allwood_carousel` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `pic` varchar(40) NOT NULL COMMENT '图片名称',
  `caid` int(11) unsigned NOT NULL COMMENT '专题页编号',
  `alt` varchar(20) DEFAULT NULL COMMENT '锚点文字说明',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='专题页轮播图';

/*Table structure for table `allwood_cart` */

DROP TABLE IF EXISTS `allwood_cart`;

CREATE TABLE `allwood_cart` (
  `cart_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '购物车id',
  `buyer_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '买家id',
  `store_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '店铺id',
  `store_name` varchar(50) NOT NULL DEFAULT '' COMMENT '店铺名称',
  `goods_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '商品id',
  `goods_name` varchar(100) NOT NULL COMMENT '商品名称',
  `goods_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '商品价格',
  `goods_num` smallint(5) unsigned NOT NULL DEFAULT '1' COMMENT '购买商品数量',
  `goods_image` varchar(100) NOT NULL COMMENT '商品图片',
  `bl_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '组合套装ID',
  `transport_type` tinyint(1) DEFAULT '1' COMMENT '当前使用的运费模板  1 物流 2 快递  3 其它',
  `is_easybuy` tinyint(1) DEFAULT '0' COMMENT '是否是分期购 1 为是0 为不是',
  `easybuy_amount` int(11) DEFAULT '0' COMMENT '分期总额',
  `period` tinyint(1) DEFAULT '0' COMMENT '分期数',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`cart_id`),
  KEY `member_id` (`buyer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=239 DEFAULT CHARSET=utf8 COMMENT='购物车数据表';

/*Table structure for table `allwood_chat_log` */

DROP TABLE IF EXISTS `allwood_chat_log`;

CREATE TABLE `allwood_chat_log` (
  `m_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '记录ID',
  `f_id` int(10) unsigned NOT NULL COMMENT '会员ID',
  `f_name` varchar(50) NOT NULL COMMENT '会员名',
  `f_ip` varchar(15) NOT NULL COMMENT '发自IP',
  `t_id` int(10) unsigned NOT NULL COMMENT '接收会员ID',
  `t_name` varchar(50) NOT NULL COMMENT '接收会员名',
  `t_msg` varchar(300) DEFAULT NULL COMMENT '消息内容',
  `add_time` int(10) unsigned DEFAULT '0' COMMENT '添加时间',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`m_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='消息记录表';

/*Table structure for table `allwood_chat_msg` */

DROP TABLE IF EXISTS `allwood_chat_msg`;

CREATE TABLE `allwood_chat_msg` (
  `m_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '记录ID',
  `f_id` int(10) unsigned NOT NULL COMMENT '会员ID',
  `f_name` varchar(50) NOT NULL COMMENT '会员名',
  `f_ip` varchar(15) NOT NULL COMMENT '发自IP',
  `t_id` int(10) unsigned NOT NULL COMMENT '接收会员ID',
  `t_name` varchar(50) NOT NULL COMMENT '接收会员名',
  `t_msg` varchar(300) DEFAULT NULL COMMENT '消息内容',
  `r_state` tinyint(1) unsigned DEFAULT '2' COMMENT '状态:1为已读,2为未读,默认为2',
  `add_time` int(10) unsigned DEFAULT '0' COMMENT '添加时间',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`m_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='消息表';

/*Table structure for table `allwood_circle` */

DROP TABLE IF EXISTS `allwood_circle`;

CREATE TABLE `allwood_circle` (
  `circle_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '圈子id',
  `circle_name` varchar(12) NOT NULL COMMENT '圈子名称',
  `circle_desc` varchar(255) DEFAULT NULL COMMENT '圈子描述',
  `circle_masterid` int(11) unsigned NOT NULL COMMENT '圈主id',
  `circle_mastername` varchar(50) NOT NULL COMMENT '圈主名称',
  `circle_img` varchar(50) DEFAULT NULL COMMENT '圈子图片',
  `class_id` int(11) unsigned NOT NULL COMMENT '圈子分类',
  `circle_mcount` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '圈子成员数',
  `circle_thcount` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '圈子主题数',
  `circle_gcount` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '圈子商品数',
  `circle_pursuereason` varchar(255) DEFAULT NULL COMMENT '圈子申请理由',
  `circle_notice` varchar(255) DEFAULT NULL COMMENT '圈子公告',
  `circle_status` tinyint(3) unsigned NOT NULL COMMENT '圈子状态，0关闭，1开启，2审核中，3审核失败',
  `circle_statusinfo` varchar(255) DEFAULT NULL COMMENT '关闭或审核失败原因',
  `circle_joinaudit` tinyint(3) unsigned NOT NULL COMMENT '加入圈子时候需要审核，0不需要，1需要',
  `circle_addtime` varchar(10) NOT NULL COMMENT '圈子创建时间',
  `circle_noticetime` varchar(10) DEFAULT NULL COMMENT '圈子公告更新时间',
  `is_recommend` tinyint(3) unsigned NOT NULL COMMENT '是否推荐 0未推荐，1已推荐',
  `is_hot` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否为热门圈子 1是 0否',
  `circle_tag` varchar(60) DEFAULT NULL COMMENT '圈子标签',
  `new_verifycount` int(5) unsigned NOT NULL DEFAULT '0' COMMENT '等待审核成员数',
  `new_informcount` int(5) unsigned NOT NULL DEFAULT '0' COMMENT '等待处理举报数',
  `mapply_open` tinyint(4) NOT NULL DEFAULT '0' COMMENT '申请管理是否开启 0关闭，1开启',
  `mapply_ml` tinyint(4) NOT NULL DEFAULT '0' COMMENT '成员级别',
  `new_mapplycount` int(5) unsigned NOT NULL DEFAULT '0' COMMENT '管理申请数量',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`circle_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='圈子表';

/*Table structure for table `allwood_circle_affix` */

DROP TABLE IF EXISTS `allwood_circle_affix`;

CREATE TABLE `allwood_circle_affix` (
  `affix_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '附件id',
  `affix_filename` varchar(100) NOT NULL COMMENT '文件名称',
  `affix_filethumb` varchar(100) NOT NULL COMMENT '缩略图名称',
  `affix_filesize` int(10) unsigned NOT NULL COMMENT '文件大小，单位字节',
  `affix_addtime` varchar(10) NOT NULL COMMENT '上传时间',
  `affix_type` tinyint(3) unsigned NOT NULL COMMENT '文件类型 0无 1主题 2评论',
  `member_id` int(11) unsigned NOT NULL COMMENT '会员id',
  `theme_id` int(11) unsigned NOT NULL COMMENT '主题id',
  `reply_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '评论id',
  `circle_id` int(11) unsigned NOT NULL COMMENT '圈子id',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`affix_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='附件表';

/*Table structure for table `allwood_circle_class` */

DROP TABLE IF EXISTS `allwood_circle_class`;

CREATE TABLE `allwood_circle_class` (
  `class_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '圈子分类id',
  `class_name` varchar(50) NOT NULL COMMENT '圈子分类名称',
  `class_addtime` varchar(10) NOT NULL COMMENT '圈子分类创建时间',
  `class_sort` tinyint(3) unsigned NOT NULL COMMENT '圈子分类排序',
  `class_status` tinyint(3) unsigned NOT NULL COMMENT '圈子分类状态 0不显示，1显示',
  `is_recommend` tinyint(3) unsigned NOT NULL COMMENT '是否推荐 0未推荐，1已推荐',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`class_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='圈子分类表';

/*Table structure for table `allwood_circle_explog` */

DROP TABLE IF EXISTS `allwood_circle_explog`;

CREATE TABLE `allwood_circle_explog` (
  `el_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '经验日志id',
  `circle_id` int(11) unsigned NOT NULL COMMENT '圈子id',
  `member_id` int(11) unsigned NOT NULL COMMENT '成员id',
  `member_name` varchar(50) NOT NULL COMMENT '成员名称',
  `el_exp` int(10) NOT NULL COMMENT '获得经验',
  `el_time` varchar(10) NOT NULL COMMENT '获得时间',
  `el_type` tinyint(3) unsigned NOT NULL COMMENT '类型 1管理员操作 2发表话题 3发表回复 4话题被回复 5话题被删除 6回复被删除',
  `el_itemid` varchar(100) NOT NULL COMMENT '信息id',
  `el_desc` varchar(255) NOT NULL COMMENT '描述',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`el_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='经验日志表';

/*Table structure for table `allwood_circle_expmember` */

DROP TABLE IF EXISTS `allwood_circle_expmember`;

CREATE TABLE `allwood_circle_expmember` (
  `member_id` int(11) NOT NULL COMMENT '成员id',
  `circle_id` int(11) unsigned NOT NULL COMMENT '圈子id',
  `em_exp` int(10) NOT NULL COMMENT '获得经验',
  `em_time` varchar(10) NOT NULL COMMENT '获得时间',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`member_id`,`circle_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='成员每天获得经验表';

/*Table structure for table `allwood_circle_exptheme` */

DROP TABLE IF EXISTS `allwood_circle_exptheme`;

CREATE TABLE `allwood_circle_exptheme` (
  `theme_id` int(11) unsigned NOT NULL COMMENT '主题id',
  `et_exp` int(10) NOT NULL COMMENT '获得经验',
  `et_time` varchar(10) NOT NULL COMMENT '获得时间',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`theme_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='主题每天获得经验表';

/*Table structure for table `allwood_circle_fs` */

DROP TABLE IF EXISTS `allwood_circle_fs`;

CREATE TABLE `allwood_circle_fs` (
  `circle_id` int(11) unsigned NOT NULL COMMENT '圈子id',
  `friendship_id` int(11) unsigned NOT NULL COMMENT '友情圈子id',
  `friendship_name` varchar(11) NOT NULL COMMENT '友情圈子名称',
  `friendship_sort` tinyint(4) unsigned NOT NULL COMMENT '友情圈子排序',
  `friendship_status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '友情圈子名称 1显示 0隐藏',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`circle_id`,`friendship_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='友情圈子表';

/*Table structure for table `allwood_circle_inform` */

DROP TABLE IF EXISTS `allwood_circle_inform`;

CREATE TABLE `allwood_circle_inform` (
  `inform_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '举报id',
  `circle_id` int(11) unsigned NOT NULL COMMENT '圈子id',
  `circle_name` varchar(12) NOT NULL COMMENT '圈子名称',
  `theme_id` int(11) unsigned NOT NULL COMMENT '话题id',
  `theme_name` varchar(50) NOT NULL COMMENT '主题名称',
  `reply_id` int(11) unsigned NOT NULL COMMENT '回复id',
  `member_id` int(11) unsigned NOT NULL COMMENT '会员id',
  `member_name` varchar(50) NOT NULL COMMENT '会员名称',
  `inform_content` varchar(255) NOT NULL COMMENT '举报内容',
  `inform_time` varchar(10) NOT NULL COMMENT '举报时间',
  `inform_type` tinyint(4) NOT NULL COMMENT '类型 0话题、1回复',
  `inform_state` tinyint(4) NOT NULL COMMENT '状态 0未处理、1已处理',
  `inform_opid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '操作人id',
  `inform_opname` varchar(50) NOT NULL DEFAULT '' COMMENT '操作人名称',
  `inform_opexp` tinyint(4) NOT NULL COMMENT '操作经验',
  `inform_opresult` varchar(255) NOT NULL DEFAULT '' COMMENT '处理结果',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`inform_id`),
  KEY `circle_id` (`circle_id`,`theme_id`,`reply_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='圈子举报表';

/*Table structure for table `allwood_circle_like` */

DROP TABLE IF EXISTS `allwood_circle_like`;

CREATE TABLE `allwood_circle_like` (
  `theme_id` int(11) unsigned NOT NULL COMMENT '主题id',
  `member_id` int(11) unsigned NOT NULL COMMENT '会员id',
  `circle_id` int(11) unsigned NOT NULL COMMENT '圈子id',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='主题赞表';

/*Table structure for table `allwood_circle_mapply` */

DROP TABLE IF EXISTS `allwood_circle_mapply`;

CREATE TABLE `allwood_circle_mapply` (
  `mapply_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '申请id',
  `circle_id` int(11) unsigned NOT NULL COMMENT '圈子id',
  `member_id` int(11) unsigned NOT NULL COMMENT '成员id',
  `mapply_reason` varchar(255) NOT NULL COMMENT '申请理由',
  `mapply_time` varchar(10) NOT NULL COMMENT '申请时间',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`mapply_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='申请管理表';

/*Table structure for table `allwood_circle_member` */

DROP TABLE IF EXISTS `allwood_circle_member`;

CREATE TABLE `allwood_circle_member` (
  `member_id` int(11) unsigned NOT NULL COMMENT '会员id',
  `circle_id` int(11) unsigned NOT NULL COMMENT '圈子id',
  `circle_name` varchar(12) NOT NULL COMMENT '圈子名称',
  `member_name` varchar(50) NOT NULL COMMENT '会员名称',
  `cm_applycontent` varchar(255) DEFAULT '' COMMENT '申请内容',
  `cm_applytime` varchar(10) NOT NULL COMMENT '申请时间',
  `cm_state` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '状态 0申请中 1通过 2未通过',
  `cm_intro` varchar(255) DEFAULT '' COMMENT '自我介绍',
  `cm_jointime` varchar(10) NOT NULL COMMENT '加入时间',
  `cm_level` int(11) NOT NULL DEFAULT '1' COMMENT '成员级别',
  `cm_levelname` varchar(10) NOT NULL DEFAULT '初级粉丝' COMMENT '成员头衔',
  `cm_exp` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '会员经验',
  `cm_nextexp` int(10) NOT NULL DEFAULT '5' COMMENT '下一级所需经验',
  `is_identity` tinyint(3) unsigned NOT NULL COMMENT '1圈主 2管理 3成员',
  `is_allowspeak` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '是否允许发言 1允许 0禁止',
  `is_star` tinyint(4) NOT NULL DEFAULT '0' COMMENT '明星成员 1是 0否',
  `cm_thcount` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '主题数',
  `cm_comcount` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '回复数',
  `cm_lastspeaktime` varchar(10) DEFAULT '' COMMENT '最后发言时间',
  `is_recommend` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否推荐 1是 0否',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`member_id`,`circle_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='圈子会员表';

/*Table structure for table `allwood_circle_ml` */

DROP TABLE IF EXISTS `allwood_circle_ml`;

CREATE TABLE `allwood_circle_ml` (
  `circle_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '圈子id',
  `mlref_id` int(10) DEFAULT NULL COMMENT '参考头衔id 0为默认 null为自定义',
  `ml_1` varchar(10) NOT NULL COMMENT '1级头衔名称',
  `ml_2` varchar(10) NOT NULL COMMENT '2级头衔名称',
  `ml_3` varchar(10) NOT NULL COMMENT '3级头衔名称',
  `ml_4` varchar(10) NOT NULL COMMENT '4级头衔名称',
  `ml_5` varchar(10) NOT NULL COMMENT '5级头衔名称',
  `ml_6` varchar(10) NOT NULL COMMENT '6级头衔名称',
  `ml_7` varchar(10) NOT NULL COMMENT '7级头衔名称',
  `ml_8` varchar(10) NOT NULL COMMENT '8级头衔名称',
  `ml_9` varchar(10) NOT NULL COMMENT '9级头衔名称',
  `ml_10` varchar(10) NOT NULL COMMENT '10级头衔名称',
  `ml_11` varchar(10) NOT NULL COMMENT '11级头衔名称',
  `ml_12` varchar(10) NOT NULL COMMENT '12级头衔名称',
  `ml_13` varchar(10) NOT NULL COMMENT '13级头衔名称',
  `ml_14` varchar(10) NOT NULL COMMENT '14级头衔名称',
  `ml_15` varchar(10) NOT NULL COMMENT '15级头衔名称',
  `ml_16` varchar(10) NOT NULL COMMENT '16级头衔名称',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`circle_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='会员头衔表';

/*Table structure for table `allwood_circle_mldefault` */

DROP TABLE IF EXISTS `allwood_circle_mldefault`;

CREATE TABLE `allwood_circle_mldefault` (
  `mld_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '头衔等级',
  `mld_name` varchar(10) NOT NULL COMMENT '头衔名称',
  `mld_exp` int(10) NOT NULL COMMENT '所需经验值',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`mld_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COMMENT='成员头衔默认设置表';

/*Table structure for table `allwood_circle_mlref` */

DROP TABLE IF EXISTS `allwood_circle_mlref`;

CREATE TABLE `allwood_circle_mlref` (
  `mlref_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '参考头衔id',
  `mlref_name` varchar(10) NOT NULL COMMENT '参考头衔名称',
  `mlref_addtime` varchar(10) NOT NULL COMMENT '创建时间',
  `mlref_status` tinyint(3) unsigned NOT NULL COMMENT '状态',
  `mlref_1` varchar(10) NOT NULL COMMENT '1级头衔名称',
  `mlref_2` varchar(10) NOT NULL COMMENT '2级头衔名称',
  `mlref_3` varchar(10) NOT NULL COMMENT '3级头衔名称',
  `mlref_4` varchar(10) NOT NULL COMMENT '4级头衔名称',
  `mlref_5` varchar(10) NOT NULL COMMENT '5级头衔名称',
  `mlref_6` varchar(10) NOT NULL COMMENT '6级头衔名称',
  `mlref_7` varchar(10) NOT NULL COMMENT '7级头衔名称',
  `mlref_8` varchar(10) NOT NULL COMMENT '8级头衔名称',
  `mlref_9` varchar(10) NOT NULL COMMENT '9级头衔名称',
  `mlref_10` varchar(10) NOT NULL COMMENT '10级头衔名称',
  `mlref_11` varchar(10) NOT NULL COMMENT '11级头衔名称',
  `mlref_12` varchar(10) NOT NULL COMMENT '12级头衔名称',
  `mlref_13` varchar(10) NOT NULL COMMENT '13级头衔名称',
  `mlref_14` varchar(10) NOT NULL COMMENT '14级头衔名称',
  `mlref_15` varchar(10) NOT NULL COMMENT '15级头衔名称',
  `mlref_16` varchar(10) NOT NULL COMMENT '16级头衔名称',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`mlref_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='会员参考头衔表';

/*Table structure for table `allwood_circle_recycle` */

DROP TABLE IF EXISTS `allwood_circle_recycle`;

CREATE TABLE `allwood_circle_recycle` (
  `recycle_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '回收站id',
  `member_id` int(11) NOT NULL COMMENT '会员id',
  `member_name` varchar(50) NOT NULL COMMENT '会员名称',
  `circle_id` int(11) unsigned NOT NULL COMMENT '圈子id',
  `circle_name` varchar(12) NOT NULL COMMENT '圈子名称',
  `theme_name` varchar(50) NOT NULL COMMENT '主题名称',
  `recycle_content` text NOT NULL COMMENT '内容',
  `recycle_opid` int(11) unsigned NOT NULL COMMENT '操作人id',
  `recycle_opname` varchar(50) NOT NULL COMMENT '操作人名称',
  `recycle_type` tinyint(3) unsigned NOT NULL COMMENT '类型 1话题，2回复',
  `recycle_time` varchar(10) NOT NULL COMMENT '操作时间',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`recycle_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='圈子回收站表';

/*Table structure for table `allwood_circle_thclass` */

DROP TABLE IF EXISTS `allwood_circle_thclass`;

CREATE TABLE `allwood_circle_thclass` (
  `thclass_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主题分类id',
  `thclass_name` varchar(20) NOT NULL COMMENT '主题名称',
  `thclass_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '主题状态 1开启，0关闭',
  `is_moderator` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '管理专属 1是，0否',
  `thclass_sort` tinyint(3) unsigned NOT NULL COMMENT '分类排序',
  `circle_id` int(11) unsigned NOT NULL COMMENT '所属圈子id',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`thclass_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='圈子主题分类表';

/*Table structure for table `allwood_circle_theme` */

DROP TABLE IF EXISTS `allwood_circle_theme`;

CREATE TABLE `allwood_circle_theme` (
  `theme_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主题id',
  `theme_name` varchar(50) NOT NULL COMMENT '主题名称',
  `theme_content` text NOT NULL COMMENT '主题内容',
  `circle_id` int(11) unsigned NOT NULL COMMENT '圈子id',
  `circle_name` varchar(12) NOT NULL COMMENT '圈子名称',
  `thclass_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '主题分类id',
  `thclass_name` varchar(20) NOT NULL COMMENT '主题分类名称',
  `member_id` int(11) unsigned NOT NULL COMMENT '会员id',
  `member_name` varchar(50) NOT NULL COMMENT '会员名称',
  `is_identity` tinyint(3) unsigned NOT NULL COMMENT '1圈主 2管理 3成员',
  `theme_addtime` varchar(10) NOT NULL COMMENT '主题发表时间',
  `theme_editname` varchar(50) DEFAULT NULL COMMENT '编辑人名称',
  `theme_edittime` varchar(10) DEFAULT NULL COMMENT '主题编辑时间',
  `theme_likecount` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '喜欢数量',
  `theme_commentcount` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '评论数量',
  `theme_browsecount` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '浏览数量',
  `theme_sharecount` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '分享数量',
  `is_stick` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否置顶 1是  0否',
  `is_digest` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否加精 1是 0否',
  `lastspeak_id` int(11) unsigned DEFAULT NULL COMMENT '最后发言人id',
  `lastspeak_name` varchar(50) DEFAULT NULL COMMENT '最后发言人名称',
  `lastspeak_time` varchar(10) DEFAULT NULL COMMENT '最后发言时间',
  `has_goods` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '商品标记 1是 0否',
  `has_affix` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '附件标记 1是 0 否',
  `is_closed` tinyint(4) NOT NULL DEFAULT '0' COMMENT '屏蔽 1是 0否',
  `is_recommend` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否推荐 1是 0否',
  `is_shut` tinyint(4) NOT NULL DEFAULT '0' COMMENT '主题是否关闭 1是 0否',
  `theme_exp` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '获得经验',
  `theme_readperm` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '阅读权限',
  `theme_special` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '特殊话题 0普通 1投票',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`theme_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='圈子主题表';

/*Table structure for table `allwood_circle_thg` */

DROP TABLE IF EXISTS `allwood_circle_thg`;

CREATE TABLE `allwood_circle_thg` (
  `themegoods_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主题商品id',
  `theme_id` int(11) NOT NULL COMMENT '主题id',
  `reply_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '回复id',
  `circle_id` int(11) unsigned NOT NULL COMMENT '圈子id',
  `goods_id` int(11) NOT NULL COMMENT '商品id',
  `goods_name` varchar(100) NOT NULL COMMENT '商品名称',
  `goods_price` decimal(10,2) NOT NULL COMMENT '商品价格',
  `goods_image` varchar(1000) NOT NULL COMMENT '商品图片',
  `store_id` int(11) NOT NULL COMMENT '店铺id',
  `thg_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '商品类型 0为本商城、1为淘宝 默认为0',
  `thg_url` varchar(1000) DEFAULT NULL COMMENT '商品链接',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`themegoods_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='主题商品表';

/*Table structure for table `allwood_circle_thpoll` */

DROP TABLE IF EXISTS `allwood_circle_thpoll`;

CREATE TABLE `allwood_circle_thpoll` (
  `theme_id` int(11) unsigned NOT NULL COMMENT '话题id',
  `poll_multiple` tinyint(3) unsigned NOT NULL COMMENT '单/多选 0单选、1多选',
  `poll_startime` varchar(10) NOT NULL COMMENT '开始时间',
  `poll_endtime` varchar(10) NOT NULL COMMENT '结束时间',
  `poll_days` tinyint(3) unsigned NOT NULL COMMENT '投票天数',
  `poll_voters` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '投票参与人数',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`theme_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='投票表';

/*Table structure for table `allwood_circle_thpolloption` */

DROP TABLE IF EXISTS `allwood_circle_thpolloption`;

CREATE TABLE `allwood_circle_thpolloption` (
  `pollop_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '投票选项id',
  `theme_id` int(11) unsigned NOT NULL COMMENT '话题id',
  `pollop_option` varchar(80) NOT NULL COMMENT '投票选项',
  `pollop_votes` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '得票数',
  `pollop_sort` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `pollop_votername` mediumtext COMMENT '投票者名称',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`pollop_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='投票选项表';

/*Table structure for table `allwood_circle_thpollvoter` */

DROP TABLE IF EXISTS `allwood_circle_thpollvoter`;

CREATE TABLE `allwood_circle_thpollvoter` (
  `theme_id` int(11) unsigned NOT NULL COMMENT '话题id',
  `member_id` int(11) unsigned NOT NULL COMMENT '成员id',
  `member_name` varchar(50) NOT NULL COMMENT '成员名称',
  `pollvo_options` mediumtext NOT NULL COMMENT '投票选项',
  `pollvo_time` varchar(10) NOT NULL COMMENT '投票选项',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  KEY `theme_id` (`theme_id`,`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='成员投票信息表';

/*Table structure for table `allwood_circle_threply` */

DROP TABLE IF EXISTS `allwood_circle_threply`;

CREATE TABLE `allwood_circle_threply` (
  `theme_id` int(11) unsigned NOT NULL COMMENT '主题id',
  `reply_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '评论id',
  `circle_id` int(11) unsigned NOT NULL COMMENT '圈子id',
  `member_id` int(11) unsigned NOT NULL COMMENT '会员id',
  `member_name` varchar(50) NOT NULL COMMENT '会员名称',
  `reply_content` text NOT NULL COMMENT '评论内容',
  `reply_addtime` varchar(10) NOT NULL COMMENT '发表时间',
  `reply_replyid` int(11) unsigned DEFAULT NULL COMMENT '回复楼层id',
  `reply_replyname` varchar(50) DEFAULT NULL COMMENT '回复楼层会员名称',
  `is_closed` tinyint(4) NOT NULL DEFAULT '0' COMMENT '屏蔽 1是 0否',
  `reply_exp` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '获得经验',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`theme_id`,`reply_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='主题评论表';

/*Table structure for table `allwood_cms_article` */

DROP TABLE IF EXISTS `allwood_cms_article`;

CREATE TABLE `allwood_cms_article` (
  `article_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '文章编号',
  `article_title` varchar(50) NOT NULL COMMENT '文章标题',
  `article_class_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文章分类编号',
  `article_origin` varchar(50) DEFAULT NULL COMMENT '文章来源',
  `article_origin_address` varchar(255) DEFAULT NULL COMMENT '文章来源链接',
  `article_author` varchar(50) NOT NULL COMMENT '文章作者',
  `article_abstract` varchar(140) DEFAULT NULL COMMENT '文章摘要',
  `article_content` text COMMENT '文章正文',
  `article_image` varchar(255) DEFAULT NULL COMMENT '文章图片',
  `article_keyword` varchar(255) DEFAULT NULL COMMENT '文章关键字',
  `article_link` varchar(255) DEFAULT NULL COMMENT '相关文章',
  `article_goods` text COMMENT '相关商品',
  `article_start_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文章有效期开始时间',
  `article_end_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文章有效期结束时间',
  `article_publish_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文章发布时间',
  `article_click` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文章点击量',
  `article_sort` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '文章排序0-255',
  `article_commend_flag` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '文章推荐标志0-未推荐，1-已推荐',
  `article_comment_flag` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '文章是否允许评论1-允许，0-不允许',
  `article_verify_admin` varchar(50) DEFAULT NULL COMMENT '文章审核管理员',
  `article_verify_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文章审核时间',
  `article_state` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1-草稿、2-待审核、3-已发布、4-回收站',
  `article_publisher_name` varchar(50) NOT NULL COMMENT '发布者用户名 ',
  `article_publisher_id` int(10) unsigned NOT NULL COMMENT '发布者编号',
  `article_type` tinyint(1) unsigned NOT NULL COMMENT '文章类型1-管理员发布，2-用户投稿',
  `article_attachment_path` varchar(50) NOT NULL COMMENT '文章附件路径',
  `article_image_all` text COMMENT '文章全部图片',
  `article_modify_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文章修改时间',
  `article_tag` varchar(255) DEFAULT NULL COMMENT '文章标签',
  `article_comment_count` int(10) unsigned NOT NULL COMMENT '文章评论数',
  `article_attitude_1` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文章心情1',
  `article_attitude_2` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文章心情2',
  `article_attitude_3` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文章心情3',
  `article_attitude_4` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文章心情4',
  `article_attitude_5` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文章心情5',
  `article_attitude_6` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文章心情6',
  `article_title_short` varchar(50) NOT NULL DEFAULT '' COMMENT '文章短标题',
  `article_attitude_flag` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '文章态度开关1-允许，0-不允许',
  `article_commend_image_flag` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '文章推荐标志(图文)',
  `article_share_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文章分享数',
  `article_verify_reason` varchar(255) DEFAULT NULL COMMENT '审核失败原因',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`article_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='CMS文章表';

/*Table structure for table `allwood_cms_article_attitude` */

DROP TABLE IF EXISTS `allwood_cms_article_attitude`;

CREATE TABLE `allwood_cms_article_attitude` (
  `attitude_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '心情编号',
  `attitude_article_id` int(10) unsigned NOT NULL COMMENT '文章编号',
  `attitude_member_id` int(10) unsigned NOT NULL COMMENT '用户编号',
  `attitude_time` int(10) unsigned NOT NULL COMMENT '发布心情时间',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`attitude_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='CMS文章心情表';

/*Table structure for table `allwood_cms_article_class` */

DROP TABLE IF EXISTS `allwood_cms_article_class`;

CREATE TABLE `allwood_cms_article_class` (
  `class_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '分类编号 ',
  `class_name` varchar(50) NOT NULL COMMENT '分类名称',
  `class_sort` tinyint(1) unsigned NOT NULL DEFAULT '255' COMMENT '排序',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`class_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='cms文章分类表';

/*Table structure for table `allwood_cms_comment` */

DROP TABLE IF EXISTS `allwood_cms_comment`;

CREATE TABLE `allwood_cms_comment` (
  `comment_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '评论编号',
  `comment_type` tinyint(1) NOT NULL COMMENT '评论类型编号',
  `comment_object_id` int(10) unsigned NOT NULL COMMENT '推荐商品编号',
  `comment_message` varchar(2000) NOT NULL COMMENT '评论内容',
  `comment_member_id` int(10) unsigned NOT NULL COMMENT '评论人编号',
  `comment_time` int(10) unsigned NOT NULL COMMENT '评论时间',
  `comment_quote` varchar(255) DEFAULT NULL COMMENT '评论引用',
  `comment_up` int(10) unsigned NOT NULL COMMENT '顶数量',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`comment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='CMS评论表';

/*Table structure for table `allwood_cms_comment_up` */

DROP TABLE IF EXISTS `allwood_cms_comment_up`;

CREATE TABLE `allwood_cms_comment_up` (
  `up_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '顶编号',
  `comment_id` int(10) unsigned NOT NULL COMMENT '评论编号',
  `up_member_id` int(10) unsigned NOT NULL COMMENT '用户编号',
  `up_time` int(10) unsigned NOT NULL COMMENT '评论时间',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`up_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='CMS评论顶表';

/*Table structure for table `allwood_cms_index_module` */

DROP TABLE IF EXISTS `allwood_cms_index_module`;

CREATE TABLE `allwood_cms_index_module` (
  `module_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '模块编号',
  `module_title` varchar(50) DEFAULT '' COMMENT '模块标题',
  `module_name` varchar(50) NOT NULL COMMENT '模板名称',
  `module_type` varchar(50) DEFAULT '' COMMENT '模块类型，index-固定内容、article1-文章模块1、article2-文章模块2、micro-微商城、adv-通栏广告',
  `module_sort` tinyint(1) unsigned DEFAULT '255' COMMENT '排序',
  `module_state` tinyint(1) unsigned DEFAULT '1' COMMENT '状态1-显示、0-不显示',
  `module_content` text COMMENT '模块内容',
  `module_style` varchar(50) NOT NULL DEFAULT 'style1' COMMENT '模块主题',
  `module_view` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '后台列表显示样式 1-展开 2-折叠',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`module_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='CMS首页模块表';

/*Table structure for table `allwood_cms_module` */

DROP TABLE IF EXISTS `allwood_cms_module`;

CREATE TABLE `allwood_cms_module` (
  `module_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '模板模块编号',
  `module_title` varchar(50) NOT NULL DEFAULT '' COMMENT '模板模块标题',
  `module_name` varchar(50) NOT NULL DEFAULT '' COMMENT '模板名称',
  `module_type` varchar(50) NOT NULL DEFAULT '' COMMENT '模板模块类型，index-固定内容、article1-文章模块1、article2-文章模块2、micro-微商城、adv-通栏广告',
  `module_class` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '模板模块种类1-系统自带 2-用户自定义',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`module_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='CMS模板模块表';

/*Table structure for table `allwood_cms_module_assembly` */

DROP TABLE IF EXISTS `allwood_cms_module_assembly`;

CREATE TABLE `allwood_cms_module_assembly` (
  `assembly_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '组件编号',
  `assembly_title` varchar(50) NOT NULL COMMENT '组件标题',
  `assembly_name` varchar(50) NOT NULL COMMENT '组件名称',
  `assembly_explain` varchar(255) NOT NULL COMMENT '组件说明',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`assembly_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COMMENT='cms模块组件表';

/*Table structure for table `allwood_cms_module_frame` */

DROP TABLE IF EXISTS `allwood_cms_module_frame`;

CREATE TABLE `allwood_cms_module_frame` (
  `frame_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '框架编号',
  `frame_title` varchar(50) NOT NULL COMMENT '框架标题',
  `frame_name` varchar(50) NOT NULL COMMENT '框架名称',
  `frame_explain` varchar(255) NOT NULL COMMENT '框架说明',
  `frame_structure` varchar(255) NOT NULL COMMENT '框架结构',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`frame_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COMMENT='cms模块框架表';

/*Table structure for table `allwood_cms_navigation` */

DROP TABLE IF EXISTS `allwood_cms_navigation`;

CREATE TABLE `allwood_cms_navigation` (
  `navigation_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '导航编号',
  `navigation_title` varchar(50) NOT NULL COMMENT '导航标题',
  `navigation_link` varchar(255) NOT NULL COMMENT '导航链接',
  `navigation_sort` tinyint(1) unsigned NOT NULL DEFAULT '255' COMMENT '排序',
  `navigation_open_type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '导航打开方式1-本页打开，2-新页打开',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`navigation_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='CMS导航表';

/*Table structure for table `allwood_cms_picture` */

DROP TABLE IF EXISTS `allwood_cms_picture`;

CREATE TABLE `allwood_cms_picture` (
  `picture_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '画报编号',
  `picture_title` varchar(50) NOT NULL COMMENT '画报标题',
  `picture_class_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '画报分类编号',
  `picture_author` varchar(50) NOT NULL COMMENT '画报作者',
  `picture_abstract` varchar(140) DEFAULT NULL COMMENT '画报摘要',
  `picture_image` varchar(255) DEFAULT NULL COMMENT '画报图片',
  `picture_keyword` varchar(255) DEFAULT NULL COMMENT '画报关键字',
  `picture_publish_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '画报发布时间',
  `picture_click` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '画报点击量',
  `picture_sort` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '画报排序0-255',
  `picture_commend_flag` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '画报推荐标志1-未推荐，2-已推荐',
  `picture_comment_flag` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '画报是否允许评论1-允许，2-不允许',
  `picture_verify_admin` varchar(50) DEFAULT NULL COMMENT '画报审核管理员',
  `picture_verify_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '画报审核时间',
  `picture_state` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1-草稿、2-待审核、3-已发布、4-回收站、5-已关闭',
  `picture_publisher_name` varchar(50) NOT NULL COMMENT '发布人用户名',
  `picture_publisher_id` int(10) unsigned NOT NULL COMMENT '发布人编号',
  `picture_type` tinyint(1) unsigned NOT NULL COMMENT '画报类型1-管理员发布，2-用户投稿',
  `picture_attachment_path` varchar(50) NOT NULL DEFAULT '',
  `picture_modify_time` int(10) unsigned NOT NULL COMMENT '画报修改时间',
  `picture_tag` varchar(255) DEFAULT NULL COMMENT '画报标签',
  `picture_comment_count` int(10) unsigned NOT NULL COMMENT '画报评论数',
  `picture_title_short` varchar(50) NOT NULL DEFAULT '' COMMENT '画报短标题',
  `picture_image_count` tinyint(1) unsigned NOT NULL COMMENT '画报图片总数',
  `picture_share_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '画报分享数',
  `picture_verify_reason` varchar(255) DEFAULT NULL COMMENT '审核失败原因',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`picture_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='CMS画报表';

/*Table structure for table `allwood_cms_picture_class` */

DROP TABLE IF EXISTS `allwood_cms_picture_class`;

CREATE TABLE `allwood_cms_picture_class` (
  `class_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '分类编号 ',
  `class_name` varchar(50) NOT NULL COMMENT '分类名称',
  `class_sort` tinyint(1) unsigned NOT NULL DEFAULT '255' COMMENT '排序',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`class_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='cms画报分类表';

/*Table structure for table `allwood_cms_picture_image` */

DROP TABLE IF EXISTS `allwood_cms_picture_image`;

CREATE TABLE `allwood_cms_picture_image` (
  `image_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '图片编号',
  `image_name` varchar(255) NOT NULL COMMENT '图片地址',
  `image_abstract` varchar(200) DEFAULT NULL COMMENT '图片摘要',
  `image_goods` text COMMENT '相关商品',
  `image_store` varchar(255) DEFAULT NULL COMMENT '相关店铺',
  `image_width` int(10) unsigned DEFAULT NULL COMMENT '图片宽度',
  `image_height` int(10) unsigned DEFAULT NULL COMMENT '图片高度',
  `image_picture_id` int(10) unsigned NOT NULL COMMENT '画报编号',
  `image_path` varchar(50) DEFAULT NULL COMMENT '图片路径',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`image_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='CMS画报图片表';

/*Table structure for table `allwood_cms_special` */

DROP TABLE IF EXISTS `allwood_cms_special`;

CREATE TABLE `allwood_cms_special` (
  `special_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '专题编号',
  `special_title` varchar(50) NOT NULL COMMENT '专题标题',
  `special_margin_top` int(10) DEFAULT '0' COMMENT '正文距顶部距离',
  `special_background` varchar(255) DEFAULT NULL COMMENT '专题背景',
  `special_image` varchar(255) DEFAULT NULL COMMENT '专题封面图',
  `special_image_all` text COMMENT '专题图片',
  `special_content` text COMMENT '专题内容',
  `special_modify_time` int(10) unsigned NOT NULL COMMENT '专题修改时间',
  `special_publish_id` int(10) unsigned NOT NULL COMMENT '专题发布者编号',
  `special_state` tinyint(1) unsigned NOT NULL COMMENT '专题状态1-草稿、2-已发布',
  `special_background_color` varchar(10) NOT NULL DEFAULT '#FFFFFF' COMMENT '专题背景色',
  `special_repeat` varchar(10) NOT NULL DEFAULT 'no-repeat' COMMENT '背景重复方式',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`special_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='CMS专题表';

/*Table structure for table `allwood_cms_tag` */

DROP TABLE IF EXISTS `allwood_cms_tag`;

CREATE TABLE `allwood_cms_tag` (
  `tag_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '标签编号',
  `tag_name` varchar(50) NOT NULL COMMENT '标签名称',
  `tag_sort` tinyint(1) unsigned NOT NULL COMMENT '标签排序',
  `tag_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '标签使用计数',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='CMS标签表';

/*Table structure for table `allwood_cms_tag_relation` */

DROP TABLE IF EXISTS `allwood_cms_tag_relation`;

CREATE TABLE `allwood_cms_tag_relation` (
  `relation_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '关系编号',
  `relation_type` tinyint(1) unsigned NOT NULL COMMENT '关系类型1-文章，2-画报',
  `relation_tag_id` int(10) unsigned NOT NULL COMMENT '标签编号',
  `relation_object_id` int(10) unsigned NOT NULL COMMENT '对象编号',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`relation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='CMS标签关系表';

/*Table structure for table `allwood_collect_money_record` */

DROP TABLE IF EXISTS `allwood_collect_money_record`;

CREATE TABLE `allwood_collect_money_record` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `money` decimal(18,0) DEFAULT NULL COMMENT '金额',
  `is_income` tinyint(4) DEFAULT NULL COMMENT '方向，0：支出，1：收入',
  `store_id` bigint(20) DEFAULT NULL COMMENT '对方店铺id，如果是链金所则为0',
  `store_name` varchar(48) DEFAULT NULL COMMENT '对方店铺名，如果是链金所则写入“链金所”',
  `remark` varchar(48) DEFAULT NULL COMMENT '备注，资金说明',
  `add_time` datetime DEFAULT NULL COMMENT '增加时间',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=72 DEFAULT CHARSET=utf8 COMMENT='全木行募集资金流水表';

/*Table structure for table `allwood_complain` */

DROP TABLE IF EXISTS `allwood_complain`;

CREATE TABLE `allwood_complain` (
  `complain_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '投诉id',
  `order_id` int(11) NOT NULL COMMENT '订单id',
  `accuser_id` int(11) NOT NULL COMMENT '原告id',
  `accuser_name` varchar(50) NOT NULL COMMENT '原告名称',
  `accused_id` int(11) NOT NULL COMMENT '被告id',
  `accused_name` varchar(50) NOT NULL COMMENT '被告名称',
  `complain_subject_content` varchar(50) NOT NULL COMMENT '投诉主题',
  `complain_subject_id` int(11) NOT NULL COMMENT '投诉主题id',
  `complain_content` varchar(255) NOT NULL COMMENT '投诉内容',
  `complain_pic1` varchar(100) NOT NULL COMMENT '投诉图片1',
  `complain_pic2` varchar(100) NOT NULL COMMENT '投诉图片2',
  `complain_pic3` varchar(100) NOT NULL COMMENT '投诉图片3',
  `complain_datetime` int(11) NOT NULL COMMENT '投诉时间',
  `complain_handle_datetime` int(11) NOT NULL COMMENT '投诉处理时间',
  `complain_handle_member_id` int(11) NOT NULL COMMENT '投诉处理人id',
  `appeal_message` varchar(255) NOT NULL COMMENT '申诉内容',
  `appeal_datetime` int(11) NOT NULL COMMENT '申诉时间',
  `appeal_pic1` varchar(100) NOT NULL COMMENT '申诉图片1',
  `appeal_pic2` varchar(100) NOT NULL COMMENT '申诉图片2',
  `appeal_pic3` varchar(100) NOT NULL COMMENT '申诉图片3',
  `final_handle_message` varchar(255) NOT NULL COMMENT '最终处理意见',
  `final_handle_datetime` int(11) NOT NULL COMMENT '最终处理时间',
  `final_handle_member_id` int(11) NOT NULL COMMENT '最终处理人id',
  `complain_state` tinyint(4) NOT NULL COMMENT '投诉状态(10-新投诉/20-投诉通过转给被投诉人/30-被投诉人已申诉/40-提交仲裁/99-已关闭)',
  `complain_active` tinyint(4) NOT NULL DEFAULT '1' COMMENT '投诉是否通过平台审批(1未通过/2通过)',
  `tel` varchar(300) DEFAULT NULL COMMENT '电话号码',
  `email` varchar(500) DEFAULT NULL COMMENT '邮箱地址',
  `logistics` int(11) DEFAULT '0' COMMENT '物流id',
  `expressNum` varchar(500) DEFAULT NULL COMMENT '物流单号',
  `goods_state` int(11) DEFAULT '0' COMMENT '货物状态',
  `refund_id` int(11) DEFAULT '0' COMMENT '退款退货id',
  `verdict` int(11) DEFAULT '0' COMMENT '仲裁决定，1 为买方责任 2 为卖方责任',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`complain_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COMMENT='投诉表';

/*Table structure for table `allwood_complain_goods` */

DROP TABLE IF EXISTS `allwood_complain_goods`;

CREATE TABLE `allwood_complain_goods` (
  `complain_goods_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '投诉商品序列id',
  `complain_id` int(11) NOT NULL COMMENT '投诉id',
  `goods_id` int(11) NOT NULL COMMENT '商品id',
  `goods_name` varchar(100) NOT NULL COMMENT '商品名称',
  `goods_price` decimal(10,2) NOT NULL COMMENT '商品价格',
  `goods_num` int(11) NOT NULL COMMENT '商品数量',
  `goods_image` varchar(100) NOT NULL DEFAULT '' COMMENT '商品图片',
  `complain_message` varchar(100) NOT NULL COMMENT '被投诉商品的问题描述',
  `order_goods_id` int(10) unsigned DEFAULT '0' COMMENT '订单商品ID',
  `order_goods_type` tinyint(1) unsigned DEFAULT '1' COMMENT '订单商品类型:1默认2团购商品3限时折扣商品4组合套装',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`complain_goods_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COMMENT='投诉商品表';

/*Table structure for table `allwood_complain_subject` */

DROP TABLE IF EXISTS `allwood_complain_subject`;

CREATE TABLE `allwood_complain_subject` (
  `complain_subject_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '投诉主题id',
  `complain_subject_content` varchar(50) NOT NULL COMMENT '投诉主题',
  `complain_subject_desc` varchar(100) NOT NULL COMMENT '投诉主题描述',
  `complain_subject_state` tinyint(4) NOT NULL COMMENT '投诉主题状态(1-有效/2-失效)',
  `new_version` int(11) DEFAULT '0' COMMENT '该主题是否只应该在新的买家中心中 1为仅适用在新版本的用户中心，0 为用在老版本',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`complain_subject_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='投诉主题表';

/*Table structure for table `allwood_complain_talk` */

DROP TABLE IF EXISTS `allwood_complain_talk`;

CREATE TABLE `allwood_complain_talk` (
  `talk_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '投诉对话id',
  `complain_id` int(11) NOT NULL COMMENT '投诉id',
  `talk_member_id` int(11) NOT NULL COMMENT '发言人id',
  `talk_member_name` varchar(50) NOT NULL COMMENT '发言人名称',
  `talk_member_type` varchar(10) NOT NULL COMMENT '发言人类型(1-投诉人/2-被投诉人/3-平台)',
  `talk_content` varchar(255) NOT NULL COMMENT '发言内容',
  `talk_state` tinyint(4) NOT NULL COMMENT '发言状态(1-显示/2-不显示)',
  `talk_admin` int(11) NOT NULL DEFAULT '0' COMMENT '对话管理员，屏蔽对话人的id',
  `talk_datetime` int(11) NOT NULL COMMENT '对话发表时间',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`talk_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='投诉对话表';

/*Table structure for table `allwood_consult` */

DROP TABLE IF EXISTS `allwood_consult`;

CREATE TABLE `allwood_consult` (
  `consult_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '咨询编号',
  `goods_id` int(11) unsigned DEFAULT '0' COMMENT '商品编号',
  `cgoods_name` varchar(100) NOT NULL COMMENT '商品名称',
  `member_id` int(11) NOT NULL DEFAULT '0' COMMENT '咨询发布者会员编号(0：游客)',
  `cmember_name` varchar(100) DEFAULT NULL COMMENT '会员名称',
  `store_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '店铺编号',
  `email` varchar(255) DEFAULT NULL COMMENT '咨询发布者邮箱',
  `consult_content` varchar(255) DEFAULT NULL COMMENT '咨询内容',
  `consult_addtime` int(10) DEFAULT NULL COMMENT '咨询发布时间',
  `consult_reply` varchar(255) DEFAULT NULL COMMENT '咨询回复内容',
  `consult_reply_time` int(10) DEFAULT NULL COMMENT '咨询回复时间',
  `isanonymous` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0表示不匿名 1表示匿名',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`consult_id`),
  KEY `goods_id` (`goods_id`),
  KEY `seller_id` (`store_id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8 COMMENT='产品咨询表';

/*Table structure for table `allwood_cps_policy` */

DROP TABLE IF EXISTS `allwood_cps_policy`;

CREATE TABLE `allwood_cps_policy` (
  `cps_id` int(11) NOT NULL AUTO_INCREMENT,
  `cps_type` tinyint(4) DEFAULT '0' COMMENT '1：商户交易提成，0：商品佣金抽佣',
  `policy_name` varchar(50) DEFAULT '0' COMMENT '策略名称',
  `commission_rate` decimal(6,4) DEFAULT '0.0000' COMMENT '佣金比例',
  `is_permanent` tinyint(4) DEFAULT '0' COMMENT '永久标志，0:不是，1：是',
  `begin_time` int(11) DEFAULT '0' COMMENT '开始时间',
  `end_time` int(11) DEFAULT '0' COMMENT '结束时间',
  `check_status` tinyint(4) DEFAULT '0' COMMENT '状态：0：待审核，1：有效，2：无效',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`cps_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='策略';

/*Table structure for table `allwood_cron` */

DROP TABLE IF EXISTS `allwood_cron`;

CREATE TABLE `allwood_cron` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(3) unsigned DEFAULT NULL COMMENT '任务类型 1商品上架 2发送邮件 3优惠套装过期 4推荐展位过期',
  `exeid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '关联任务的ID[如商品ID,会员ID]',
  `exetime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '执行时间',
  `code` char(50) DEFAULT NULL COMMENT '邮件模板CODE',
  `content` text COMMENT '内容',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COMMENT='任务队列表';

/*Table structure for table `allwood_daddress` */

DROP TABLE IF EXISTS `allwood_daddress`;

CREATE TABLE `allwood_daddress` (
  `address_id` mediumint(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '地址ID',
  `store_id` mediumint(10) unsigned NOT NULL DEFAULT '0' COMMENT '店铺ID',
  `seller_name` varchar(50) NOT NULL DEFAULT '' COMMENT '联系人',
  `area_id` mediumint(10) unsigned NOT NULL DEFAULT '0' COMMENT '地区ID',
  `city_id` mediumint(9) DEFAULT NULL COMMENT '市级ID',
  `area_info` varchar(100) DEFAULT NULL COMMENT '省市县',
  `address` varchar(100) NOT NULL COMMENT '地址',
  `telphone` varchar(40) DEFAULT NULL COMMENT '电话',
  `company` varchar(50) NOT NULL COMMENT '公司',
  `is_default` enum('0','1') NOT NULL DEFAULT '0' COMMENT '是否默认1是',
  `receiver` varchar(300) DEFAULT NULL COMMENT '收货人姓名',
  `zip_code` varchar(300) DEFAULT NULL COMMENT '邮编',
  `province_id` int(11) DEFAULT '1' COMMENT '省ID',
  `mobile_phone` int(15) DEFAULT '0' COMMENT '手机号码',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`address_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='卖家发货地址信息表';

/*Table structure for table `allwood_deal_log` */

DROP TABLE IF EXISTS `allwood_deal_log`;

CREATE TABLE `allwood_deal_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `order_id` int(11) NOT NULL COMMENT '订单id',
  `refund_id` int(11) NOT NULL COMMENT '退货退款id',
  `goods_id` int(11) NOT NULL COMMENT '订单id',
  `log_msg` varchar(150) DEFAULT '' COMMENT '文字描述',
  `log_time` int(10) unsigned NOT NULL COMMENT '处理时间',
  `log_role` char(2) NOT NULL COMMENT '操作角色',
  `log_user` varchar(30) DEFAULT '' COMMENT '操作人',
  `log_orderstate` int(11) NOT NULL COMMENT '状态 10:发起申请 20:对话 30:撤销退货退款申请',
  `log_voucher` varchar(500) DEFAULT NULL COMMENT '上传图片',
  `tesu_deleted` int(10) NOT NULL DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(300) DEFAULT NULL COMMENT '字段描述',
  `tesu_created` varchar(300) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='订单协商记录表';

/*Table structure for table `allwood_decorate_company` */

DROP TABLE IF EXISTS `allwood_decorate_company`;

CREATE TABLE `allwood_decorate_company` (
  `de_com_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int(11) unsigned NOT NULL COMMENT '店铺id',
  `company_name` varchar(30) DEFAULT NULL COMMENT '公司名',
  `logo` varchar(100) DEFAULT NULL COMMENT 'logo',
  `province_id` int(11) unsigned DEFAULT NULL COMMENT '省id',
  `city_id` int(11) unsigned DEFAULT NULL COMMENT '市id',
  `area_id` int(11) unsigned DEFAULT NULL COMMENT '区id',
  `mobile` char(11) DEFAULT NULL COMMENT '手机号',
  `phone` char(20) DEFAULT NULL COMMENT '电话',
  `address` varchar(200) DEFAULT NULL COMMENT '地址',
  `description` text COMMENT '描述',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`de_com_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

/*Table structure for table `allwood_decorate_effectdraw` */

DROP TABLE IF EXISTS `allwood_decorate_effectdraw`;

CREATE TABLE `allwood_decorate_effectdraw` (
  `draw_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `com_id` int(11) unsigned DEFAULT NULL COMMENT 'decorate_company表de_com_id',
  `store_id` int(11) unsigned NOT NULL COMMENT '商店id',
  `title` varchar(50) DEFAULT NULL COMMENT '标题',
  `pic` varchar(250) DEFAULT NULL COMMENT '图片',
  `tesu_deleted` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1:已删除 0：未删除',
  PRIMARY KEY (`draw_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

/*Table structure for table `allwood_decorate_plan` */

DROP TABLE IF EXISTS `allwood_decorate_plan`;

CREATE TABLE `allwood_decorate_plan` (
  `de_plan_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int(11) unsigned NOT NULL COMMENT '店铺id',
  `title` varchar(50) DEFAULT NULL COMMENT '标题',
  `house_type` varchar(20) DEFAULT NULL COMMENT '户型',
  `house_address` varchar(50) DEFAULT NULL COMMENT '户型地址',
  `cost` decimal(10,2) DEFAULT '0.00' COMMENT '造价',
  `visit_pwd` varchar(40) DEFAULT NULL COMMENT '访问密码',
  `coverpage` varchar(500) DEFAULT NULL COMMENT '封面',
  `contract_pic` varchar(500) DEFAULT NULL COMMENT '合同',
  `description` text COMMENT '描述',
  `tesu_deleted` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1:已删除 0：未删除',
  `com_id` int(3) NOT NULL DEFAULT '0' COMMENT '公司id',
  `area` decimal(11,2) NOT NULL DEFAULT '0.00' COMMENT '面积',
  PRIMARY KEY (`de_plan_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

/*Table structure for table `allwood_document` */

DROP TABLE IF EXISTS `allwood_document`;

CREATE TABLE `allwood_document` (
  `doc_id` mediumint(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `doc_code` varchar(255) NOT NULL COMMENT '调用标识码',
  `doc_title` varchar(255) NOT NULL COMMENT '标题',
  `doc_content` text NOT NULL COMMENT '内容',
  `doc_time` int(10) unsigned NOT NULL COMMENT '添加时间/修改时间',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`doc_id`),
  UNIQUE KEY `doc_code` (`doc_code`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='系统文章表';

/*Table structure for table `allwood_easypay_application` */

DROP TABLE IF EXISTS `allwood_easypay_application`;

CREATE TABLE `allwood_easypay_application` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL COMMENT 'member_id',
  `credit_total` int(11) NOT NULL DEFAULT '0' COMMENT '总额度',
  `credit_available` int(11) NOT NULL DEFAULT '0' COMMENT '可用额度',
  `credit_status` int(11) NOT NULL DEFAULT '0' COMMENT '分期购额度状态 6 代表冻结,0  代表未申请， 1 代表新申请， 3 代表初审通过  2 代表初审失败 5 代表复审通过 4 代表复审失败',
  `m_id` int(11) DEFAULT NULL COMMENT 'java数据空的用户id',
  `usrname` varchar(300) DEFAULT NULL COMMENT '姓名',
  `id_card` varchar(300) DEFAULT NULL COMMENT '身份证号',
  `sex` int(10) NOT NULL DEFAULT '0' COMMENT '性别 0 男 1 女',
  `marital` int(10) NOT NULL DEFAULT '0' COMMENT '婚姻状况 0 未婚 1 已婚 2 其它',
  `addr_province` int(10) NOT NULL DEFAULT '0' COMMENT '省',
  `addr_city` int(10) NOT NULL DEFAULT '0' COMMENT '市',
  `addr_county` int(10) NOT NULL DEFAULT '0' COMMENT '区',
  `addr_street` varchar(300) DEFAULT NULL COMMENT '街道',
  `usr_native` varchar(300) DEFAULT NULL COMMENT '籍贯',
  `diploma` int(10) NOT NULL DEFAULT '0' COMMENT '学历 1 研究生以上 2 本科 3 大专以下',
  `mobile_phone` varchar(300) DEFAULT NULL COMMENT '手机',
  `home_phone` varchar(300) DEFAULT NULL COMMENT '住宅电话',
  `profession` varchar(300) DEFAULT NULL COMMENT '职业',
  `profession_level` varchar(300) DEFAULT NULL COMMENT '职业级别',
  `com_name` varchar(300) DEFAULT NULL COMMENT '单位名称',
  `com_street` varchar(300) DEFAULT NULL COMMENT '单位地址',
  `income` int(10) NOT NULL DEFAULT '0' COMMENT '收入',
  `working_long` int(10) NOT NULL DEFAULT '0' COMMENT '本单位工作年限',
  `house_type` int(10) NOT NULL DEFAULT '0' COMMENT '住房类型 0 自由无贷款 1 自有 2 按揭  3 租赁 4 其它',
  `house_pay` int(10) NOT NULL DEFAULT '0' COMMENT '房屋支出 按揭或者租赁',
  `estates` int(10) NOT NULL DEFAULT '0' COMMENT '房产',
  `car_assets` int(10) NOT NULL DEFAULT '0' COMMENT '车辆资产',
  `securities` int(10) NOT NULL DEFAULT '0' COMMENT '有价证券',
  `other_assets` int(10) NOT NULL DEFAULT '0' COMMENT '其它资产',
  `id_card_front_pic` varchar(300) DEFAULT NULL COMMENT '身份证正面照',
  `id_card_reverse_pic` varchar(300) DEFAULT NULL COMMENT '身份证背面照',
  `is_face_id_pass` int(10) NOT NULL DEFAULT '0' COMMENT '人脸识别 0 已识别 1 未识别',
  `with_id_card_pic` varchar(300) DEFAULT NULL COMMENT '手持身份证照片',
  `bondsmaninf_list` varchar(4000) DEFAULT NULL,
  `other_pic_list` varchar(4000) DEFAULT NULL,
  `usrid` int(11) NOT NULL COMMENT 'java用户id',
  `usr_native_province` int(11) NOT NULL COMMENT '户籍省id',
  `usr_native_city` int(11) NOT NULL COMMENT '户籍市id',
  `add_areainfo` int(11) NOT NULL COMMENT '所在地区域信息',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=85 DEFAULT CHARSET=utf8;

/*Table structure for table `allwood_evaluate_goods` */

DROP TABLE IF EXISTS `allwood_evaluate_goods`;

CREATE TABLE `allwood_evaluate_goods` (
  `geval_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '评价ID',
  `geval_orderid` int(11) NOT NULL COMMENT '订单表自增ID',
  `geval_orderno` bigint(20) unsigned NOT NULL COMMENT '订单编号',
  `geval_ordergoodsid` int(11) NOT NULL COMMENT '订单商品表编号',
  `geval_goodsid` int(11) NOT NULL COMMENT '商品表编号',
  `geval_goodsname` varchar(100) NOT NULL COMMENT '商品名称',
  `geval_goodsprice` decimal(10,2) DEFAULT NULL COMMENT '商品价格',
  `geval_scores` tinyint(1) NOT NULL COMMENT '1-5分',
  `geval_content` varchar(255) DEFAULT NULL COMMENT '信誉评价内容',
  `geval_isanonymous` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0表示不是 1表示是匿名评价',
  `geval_addtime` int(11) NOT NULL COMMENT '评价时间',
  `geval_storeid` int(11) NOT NULL COMMENT '店铺编号',
  `geval_storename` varchar(100) NOT NULL COMMENT '店铺名称',
  `geval_frommemberid` int(11) NOT NULL COMMENT '评价人编号',
  `geval_frommembername` varchar(100) NOT NULL COMMENT '评价人名称',
  `geval_state` tinyint(1) NOT NULL DEFAULT '0' COMMENT '评价信息的状态 0为正常 1为禁止显示',
  `geval_remark` varchar(255) DEFAULT NULL COMMENT '管理员对评价的处理备注',
  `geval_explain` varchar(255) DEFAULT NULL COMMENT '解释内容',
  `geval_image` varchar(255) DEFAULT NULL COMMENT '晒单图片',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`geval_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COMMENT='信誉评价表';

/*Table structure for table `allwood_evaluate_store` */

DROP TABLE IF EXISTS `allwood_evaluate_store`;

CREATE TABLE `allwood_evaluate_store` (
  `seval_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '评价ID',
  `seval_orderid` int(11) unsigned NOT NULL COMMENT '订单ID',
  `seval_orderno` varchar(100) NOT NULL COMMENT '订单编号',
  `seval_addtime` int(11) unsigned NOT NULL COMMENT '评价时间',
  `seval_storeid` int(11) unsigned NOT NULL COMMENT '店铺编号',
  `seval_storename` varchar(100) NOT NULL COMMENT '店铺名称',
  `seval_memberid` int(11) unsigned NOT NULL COMMENT '买家编号',
  `seval_membername` varchar(100) NOT NULL COMMENT '买家名称',
  `seval_desccredit` tinyint(1) unsigned NOT NULL DEFAULT '5' COMMENT '描述相符评分',
  `seval_servicecredit` tinyint(1) unsigned NOT NULL DEFAULT '5' COMMENT '服务态度评分',
  `seval_deliverycredit` tinyint(1) unsigned NOT NULL DEFAULT '5' COMMENT '发货速度评分',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`seval_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COMMENT='店铺评分表';

/*Table structure for table `allwood_express` */

DROP TABLE IF EXISTS `allwood_express`;

CREATE TABLE `allwood_express` (
  `id` tinyint(1) unsigned NOT NULL AUTO_INCREMENT COMMENT '索引ID',
  `e_name` varchar(50) NOT NULL COMMENT '公司名称',
  `e_state` enum('0','1') NOT NULL DEFAULT '1' COMMENT '状态',
  `e_code` varchar(50) NOT NULL COMMENT '编号',
  `e_letter` char(1) NOT NULL COMMENT '首字母',
  `e_order` enum('1','2') NOT NULL DEFAULT '2' COMMENT '1常用2不常用',
  `e_url` varchar(100) NOT NULL COMMENT '公司网址',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8 COMMENT='快递公司';

/*Table structure for table `allwood_favorites` */

DROP TABLE IF EXISTS `allwood_favorites`;

CREATE TABLE `allwood_favorites` (
  `member_id` int(10) unsigned NOT NULL COMMENT '会员ID',
  `fav_id` int(10) unsigned NOT NULL COMMENT '收藏ID',
  `fav_type` varchar(20) NOT NULL COMMENT '收藏类型',
  `fav_time` int(10) unsigned NOT NULL COMMENT '收藏时间',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='买家收藏表';

/*Table structure for table `allwood_financial_easypay` */

DROP TABLE IF EXISTS `allwood_financial_easypay`;

CREATE TABLE `allwood_financial_easypay` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `images` varchar(200) NOT NULL COMMENT '商品图片',
  `goods_commonid` int(10) unsigned NOT NULL COMMENT '商品货号',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0禁用，1启用',
  `note` text NOT NULL COMMENT '分期，费率，服务费',
  `description` varchar(200) NOT NULL COMMENT '项目描述',
  `addtime` int(10) unsigned NOT NULL COMMENT '添加时间',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

/*Table structure for table `allwood_financial_invest` */

DROP TABLE IF EXISTS `allwood_financial_invest`;

CREATE TABLE `allwood_financial_invest` (
  `id` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `goods_commonid` int(10) unsigned NOT NULL COMMENT '商品货号',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0禁用，1启用',
  `note` text NOT NULL COMMENT '分期，金额',
  `addtime` int(10) unsigned NOT NULL COMMENT '添加时间',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

/*Table structure for table `allwood_floor` */

DROP TABLE IF EXISTS `allwood_floor`;

CREATE TABLE `allwood_floor` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `categroyname` varchar(20) NOT NULL COMMENT '楼层名称',
  `sort` tinyint(2) NOT NULL DEFAULT '1' COMMENT '序号',
  `caid` varchar(32) NOT NULL COMMENT '关联外键',
  `pageid` int(11) NOT NULL COMMENT '专题页面编号',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8 COMMENT='专题页面的楼层表';

/*Table structure for table `allwood_floor_goods` */

DROP TABLE IF EXISTS `allwood_floor_goods`;

CREATE TABLE `allwood_floor_goods` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `floorid` varchar(32) NOT NULL COMMENT '楼层编号',
  `goodsid` int(11) NOT NULL COMMENT '商品编号',
  `goodscid` int(11) unsigned DEFAULT NULL COMMENT '商品类别',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=133 DEFAULT CHARSET=utf8 COMMENT='专题页楼层商品';

/*Table structure for table `allwood_flowstat_1` */

DROP TABLE IF EXISTS `allwood_flowstat_1`;

CREATE TABLE `allwood_flowstat_1` (
  `date` int(8) unsigned NOT NULL COMMENT '访问日期',
  `clicknum` int(11) unsigned NOT NULL COMMENT '访问量',
  `store_id` int(11) unsigned NOT NULL COMMENT '店铺ID',
  `type` varchar(10) NOT NULL COMMENT '类型',
  `goods_id` int(11) unsigned NOT NULL COMMENT '商品ID',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='访问量统计表';

/*Table structure for table `allwood_flowstat_2` */

DROP TABLE IF EXISTS `allwood_flowstat_2`;

CREATE TABLE `allwood_flowstat_2` (
  `date` int(8) unsigned NOT NULL COMMENT '访问日期',
  `clicknum` int(11) unsigned NOT NULL COMMENT '访问量',
  `store_id` int(11) unsigned NOT NULL COMMENT '店铺ID',
  `type` varchar(10) NOT NULL COMMENT '类型',
  `goods_id` int(11) unsigned NOT NULL COMMENT '商品ID',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='访问量统计表';

/*Table structure for table `allwood_flowstat_3` */

DROP TABLE IF EXISTS `allwood_flowstat_3`;

CREATE TABLE `allwood_flowstat_3` (
  `date` int(8) unsigned NOT NULL COMMENT '访问日期',
  `clicknum` int(11) unsigned NOT NULL COMMENT '访问量',
  `store_id` int(11) unsigned NOT NULL COMMENT '店铺ID',
  `type` varchar(10) NOT NULL COMMENT '类型',
  `goods_id` int(11) unsigned NOT NULL COMMENT '商品ID',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='访问量统计表';

/*Table structure for table `allwood_flowstat_4` */

DROP TABLE IF EXISTS `allwood_flowstat_4`;

CREATE TABLE `allwood_flowstat_4` (
  `date` int(8) unsigned NOT NULL COMMENT '访问日期',
  `clicknum` int(11) unsigned NOT NULL COMMENT '访问量',
  `store_id` int(11) unsigned NOT NULL COMMENT '店铺ID',
  `type` varchar(10) NOT NULL COMMENT '类型',
  `goods_id` int(11) unsigned NOT NULL COMMENT '商品ID',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='访问量统计表';

/*Table structure for table `allwood_flowstat_5` */

DROP TABLE IF EXISTS `allwood_flowstat_5`;

CREATE TABLE `allwood_flowstat_5` (
  `date` int(8) unsigned NOT NULL COMMENT '访问日期',
  `clicknum` int(11) unsigned NOT NULL COMMENT '访问量',
  `store_id` int(11) unsigned NOT NULL COMMENT '店铺ID',
  `type` varchar(10) NOT NULL COMMENT '类型',
  `goods_id` int(11) unsigned NOT NULL COMMENT '商品ID',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='访问量统计表';

/*Table structure for table `allwood_gadmin` */

DROP TABLE IF EXISTS `allwood_gadmin`;

CREATE TABLE `allwood_gadmin` (
  `gid` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `gname` varchar(50) DEFAULT NULL COMMENT '组名',
  `limits` text COMMENT '权限内容',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`gid`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='权限组';

/*Table structure for table `allwood_goods` */

DROP TABLE IF EXISTS `allwood_goods`;

CREATE TABLE `allwood_goods` (
  `goods_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '商品id(SKU)',
  `goods_commonid` int(10) unsigned NOT NULL COMMENT '商品公共表id',
  `goods_name` varchar(50) NOT NULL COMMENT '商品名称（+规格名称）',
  `goods_jingle` varchar(50) NOT NULL COMMENT '商品广告词',
  `store_id` int(10) unsigned NOT NULL COMMENT '店铺id',
  `store_name` varchar(50) NOT NULL COMMENT '店铺名称',
  `gc_id` int(10) unsigned NOT NULL COMMENT '商品分类id',
  `brand_id` int(10) unsigned NOT NULL COMMENT '品牌id',
  `goods_price` decimal(10,2) NOT NULL COMMENT '商品价格',
  `goods_marketprice` decimal(10,2) NOT NULL COMMENT '市场价',
  `goods_serial` varchar(50) NOT NULL COMMENT '商家编号',
  `goods_click` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '商品点击数量',
  `goods_salenum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '销售数量',
  `goods_collect` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '收藏数量',
  `goods_spec` text NOT NULL COMMENT '商品规格序列化',
  `goods_storage` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '商品库存',
  `goods_image` varchar(100) NOT NULL DEFAULT '' COMMENT '商品主图',
  `goods_state` tinyint(3) unsigned NOT NULL COMMENT '商品状态 0下架，1正常，10违规（禁售）',
  `goods_verify` tinyint(3) unsigned NOT NULL COMMENT '商品审核 1通过，0未通过，10审核中',
  `goods_addtime` int(10) unsigned NOT NULL COMMENT '商品添加时间',
  `goods_edittime` int(10) unsigned NOT NULL COMMENT '商品编辑时间',
  `areaid_1` int(10) unsigned NOT NULL COMMENT '一级地区id',
  `areaid_2` int(10) unsigned NOT NULL COMMENT '二级地区id',
  `color_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '颜色规格id',
  `transport_id` mediumint(8) unsigned NOT NULL COMMENT '运费模板id',
  `goods_freight` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '运费 0为免运费',
  `goods_vat` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否开具增值税发票 1是，0否',
  `goods_commend` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '商品推荐 1是，0否 默认0',
  `goods_stcids` varchar(255) NOT NULL DEFAULT '' COMMENT '店铺分类id 首尾用,隔开',
  `evaluation_good_star` tinyint(3) unsigned NOT NULL DEFAULT '5' COMMENT '好评星级',
  `evaluation_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评价数',
  `tesu_offline_time` int(11) NOT NULL DEFAULT '0' COMMENT '商品下架时间',
  `goods_weight` varchar(40) DEFAULT '0' COMMENT '商品重量',
  `goods_volume` varchar(40) DEFAULT '0' COMMENT '商品体积',
  `customization` varchar(20) DEFAULT NULL COMMENT '是否可定制',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  `is_offline` int(3) DEFAULT '0' COMMENT '商品表增加线下标志，线上商品在网站上对外展示，线下商品只在店内pad上展示，0：线上，1：线下',
  PRIMARY KEY (`goods_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3020 DEFAULT CHARSET=utf8 COMMENT='商品表';

/*Table structure for table `allwood_goods_attr_index` */

DROP TABLE IF EXISTS `allwood_goods_attr_index`;

CREATE TABLE `allwood_goods_attr_index` (
  `goods_id` int(10) unsigned NOT NULL COMMENT '商品id',
  `goods_commonid` int(10) unsigned NOT NULL COMMENT '商品公共表id',
  `gc_id` int(10) unsigned NOT NULL COMMENT '商品分类id',
  `type_id` int(10) unsigned NOT NULL COMMENT '类型id',
  `attr_id` int(10) unsigned NOT NULL COMMENT '属性id',
  `attr_value_id` int(10) unsigned NOT NULL COMMENT '属性值id',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`goods_id`,`gc_id`,`attr_value_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品与属性对应表';

/*Table structure for table `allwood_goods_browse` */

DROP TABLE IF EXISTS `allwood_goods_browse`;

CREATE TABLE `allwood_goods_browse` (
  `goods_id` int(11) NOT NULL COMMENT '商品ID',
  `member_id` int(11) NOT NULL COMMENT '会员ID',
  `browsetime` int(11) NOT NULL COMMENT '浏览时间',
  `gc_id` int(11) NOT NULL COMMENT '商品分类',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`goods_id`,`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品浏览历史表';

/*Table structure for table `allwood_goods_class` */

DROP TABLE IF EXISTS `allwood_goods_class`;

CREATE TABLE `allwood_goods_class` (
  `gc_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '索引ID',
  `gc_name` varchar(100) NOT NULL COMMENT '分类名称',
  `type_id` int(10) unsigned NOT NULL COMMENT '类型id',
  `type_name` varchar(100) NOT NULL COMMENT '类型名称',
  `gc_parent_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父ID',
  `gc_sort` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `gc_title` varchar(200) NOT NULL COMMENT '名称',
  `gc_keywords` varchar(255) NOT NULL DEFAULT '' COMMENT '关键词',
  `gc_description` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
  `gc_deep` tinyint(1) DEFAULT '0' COMMENT '深度',
  `gc_url` varchar(255) DEFAULT '' COMMENT '专题分类地址',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`gc_id`),
  KEY `store_id` (`gc_parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1527 DEFAULT CHARSET=utf8 COMMENT='商品分类表';

/*Table structure for table `allwood_goods_class_staple` */

DROP TABLE IF EXISTS `allwood_goods_class_staple`;

CREATE TABLE `allwood_goods_class_staple` (
  `staple_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '常用分类id',
  `staple_name` varchar(255) NOT NULL COMMENT '常用分类名称',
  `gc_id_1` int(10) unsigned NOT NULL COMMENT '一级分类id',
  `gc_id_2` int(10) unsigned NOT NULL COMMENT '二级商品分类',
  `gc_id_3` int(10) unsigned NOT NULL COMMENT '三级商品分类',
  `type_id` int(10) unsigned NOT NULL COMMENT '类型id',
  `member_id` int(10) unsigned NOT NULL COMMENT '会员id',
  `counter` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '计数器',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`staple_id`),
  KEY `store_id` (`member_id`)
) ENGINE=InnoDB AUTO_INCREMENT=614 DEFAULT CHARSET=utf8 COMMENT='店铺常用分类表';

/*Table structure for table `allwood_goods_class_tag` */

DROP TABLE IF EXISTS `allwood_goods_class_tag`;

CREATE TABLE `allwood_goods_class_tag` (
  `gc_tag_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'TAGid',
  `gc_id_1` int(10) unsigned NOT NULL COMMENT '一级分类id',
  `gc_id_2` int(10) unsigned NOT NULL COMMENT '二级分类id',
  `gc_id_3` int(10) unsigned NOT NULL COMMENT '三级分类id',
  `gc_tag_name` varchar(255) NOT NULL COMMENT '分类TAG名称',
  `gc_tag_value` text NOT NULL COMMENT '分类TAG值',
  `gc_id` int(10) unsigned NOT NULL COMMENT '商品分类id',
  `type_id` int(10) unsigned NOT NULL COMMENT '类型id',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`gc_tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品分类TAG表';

/*Table structure for table `allwood_goods_common` */

DROP TABLE IF EXISTS `allwood_goods_common`;

CREATE TABLE `allwood_goods_common` (
  `goods_commonid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '商品公共表id',
  `goods_name` varchar(50) NOT NULL COMMENT '商品名称',
  `goods_jingle` varchar(50) NOT NULL COMMENT '商品广告词',
  `gc_id` int(10) unsigned NOT NULL COMMENT '商品分类',
  `gc_name` varchar(200) NOT NULL COMMENT '商品分类',
  `store_id` int(10) unsigned NOT NULL COMMENT '店铺id',
  `store_name` varchar(50) NOT NULL COMMENT '店铺名称',
  `spec_name` varchar(255) NOT NULL COMMENT '规格名称',
  `spec_value` text NOT NULL COMMENT '规格值',
  `brand_id` int(10) unsigned NOT NULL COMMENT '品牌id',
  `brand_name` varchar(100) NOT NULL COMMENT '品牌名称',
  `type_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '类型id',
  `goods_image` varchar(100) NOT NULL COMMENT '商品主图',
  `goods_attr` text NOT NULL COMMENT '商品属性',
  `goods_body` text NOT NULL COMMENT '商品内容',
  `goods_state` tinyint(3) unsigned NOT NULL COMMENT '商品状态 0下架，1正常，10违规（禁售）',
  `goods_stateremark` varchar(255) DEFAULT NULL COMMENT '违规原因',
  `goods_verify` tinyint(3) unsigned NOT NULL COMMENT '商品审核 1通过，0未通过，10审核中',
  `goods_verifyremark` varchar(255) DEFAULT NULL COMMENT '审核失败原因',
  `goods_lock` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '商品锁定 0未锁，1已锁',
  `goods_addtime` int(10) unsigned NOT NULL COMMENT '商品添加时间',
  `goods_selltime` int(10) unsigned NOT NULL COMMENT '上架时间',
  `goods_specname` text NOT NULL COMMENT '规格名称序列化（下标为规格id）',
  `goods_price` decimal(10,2) NOT NULL COMMENT '商品价格',
  `goods_marketprice` decimal(10,2) NOT NULL COMMENT '市场价',
  `goods_costprice` decimal(10,2) NOT NULL COMMENT '成本价',
  `goods_discount` float unsigned NOT NULL COMMENT '折扣',
  `goods_serial` varchar(50) NOT NULL COMMENT '商家编号',
  `transport_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '运费模板',
  `transport_title` varchar(60) NOT NULL DEFAULT '' COMMENT '运费模板名称',
  `goods_commend` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '商品推荐 1是，0否，默认为0',
  `goods_freight` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '运费 0为免运费',
  `goods_vat` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否开具增值税发票 1是，0否',
  `areaid_1` int(10) unsigned NOT NULL COMMENT '一级地区id',
  `areaid_2` int(10) unsigned NOT NULL COMMENT '二级地区id',
  `goods_stcids` varchar(255) NOT NULL DEFAULT '' COMMENT '店铺分类id 首尾用,隔开',
  `plateid_top` int(10) unsigned DEFAULT NULL COMMENT '顶部关联板式',
  `plateid_bottom` int(10) unsigned DEFAULT NULL COMMENT '底部关联板式',
  `tesu_offline_time` int(11) NOT NULL DEFAULT '0' COMMENT '商品下架时间',
  `goods_weight` varchar(40) DEFAULT NULL COMMENT '商品重量',
  `goods_volume` varchar(40) DEFAULT NULL COMMENT '商品体积',
  `customization` varchar(20) DEFAULT NULL COMMENT '是否可定制',
  `brick_store` varchar(20) DEFAULT NULL COMMENT '体验店id',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  `is_offline` int(3) DEFAULT NULL COMMENT '商品表增加线下标志，线上商品在网站上对外展示，线下商品只在店内pad上展示，0：线上，1：线下',
  PRIMARY KEY (`goods_commonid`)
) ENGINE=InnoDB AUTO_INCREMENT=101505 DEFAULT CHARSET=utf8 COMMENT='商品公共内容表';

/*Table structure for table `allwood_goods_hit` */

DROP TABLE IF EXISTS `allwood_goods_hit`;

CREATE TABLE `allwood_goods_hit` (
  `hit_id` int(11) NOT NULL AUTO_INCREMENT,
  `goods_id` int(11) DEFAULT '0' COMMENT '产品ID',
  `user_id` int(11) DEFAULT '0' COMMENT '分享者ID',
  `add_time` int(11) DEFAULT '0' COMMENT '添加的时间',
  `way1` int(11) DEFAULT '0' COMMENT '第三方分享',
  `way2` int(11) DEFAULT '0' COMMENT '链接分享',
  `way3` int(11) DEFAULT '0' COMMENT '二维码分享',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`hit_id`),
  UNIQUE KEY `goods_id_user_id_add_time` (`goods_id`,`user_id`,`add_time`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='分享点击数目';

/*Table structure for table `allwood_goods_hit_list` */

DROP TABLE IF EXISTS `allwood_goods_hit_list`;

CREATE TABLE `allwood_goods_hit_list` (
  `list_id` int(11) NOT NULL AUTO_INCREMENT,
  `hit_id` int(11) NOT NULL DEFAULT '0' COMMENT '点击对应的id',
  `order_id` int(11) NOT NULL DEFAULT '0' COMMENT '订单号',
  `rec_id` int(11) NOT NULL DEFAULT '0' COMMENT '订单明细',
  `orders_cnt` int(11) NOT NULL DEFAULT '0' COMMENT '订单数',
  `goods_pay_price` decimal(11,2) NOT NULL DEFAULT '0.00' COMMENT '有效订单金额',
  `cps_percent` decimal(4,2) NOT NULL DEFAULT '0.00' COMMENT '策略比率',
  `percent` decimal(4,2) NOT NULL DEFAULT '0.00' COMMENT '产品分佣比率',
  `r_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '预估收益',
  `addtime` int(11) NOT NULL COMMENT '购买时间',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`list_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='通过点击引进来的订单';

/*Table structure for table `allwood_goods_images` */

DROP TABLE IF EXISTS `allwood_goods_images`;

CREATE TABLE `allwood_goods_images` (
  `goods_image_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '商品图片id',
  `goods_commonid` int(10) unsigned NOT NULL COMMENT '商品公共内容id',
  `store_id` int(10) unsigned NOT NULL COMMENT '店铺id',
  `color_id` int(10) unsigned NOT NULL COMMENT '颜色规格值id',
  `goods_image` varchar(1000) NOT NULL COMMENT '商品图片',
  `goods_image_sort` tinyint(3) unsigned NOT NULL COMMENT '排序',
  `is_default` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '默认主题，1是，0否',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`goods_image_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8534 DEFAULT CHARSET=utf8 COMMENT='商品图片';

/*Table structure for table `allwood_goods_share` */

DROP TABLE IF EXISTS `allwood_goods_share`;

CREATE TABLE `allwood_goods_share` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shop_id` int(11) NOT NULL DEFAULT '0' COMMENT '店铺ID',
  `goods_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品的ID',
  `add_time` int(11) NOT NULL DEFAULT '0' COMMENT '建立表时间',
  `start_time` int(11) NOT NULL DEFAULT '0' COMMENT '活动起始时间',
  `end_time` int(11) NOT NULL DEFAULT '0' COMMENT '活动结束时间',
  `money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '单价',
  `share_money` decimal(6,4) NOT NULL DEFAULT '0.0000' COMMENT '佣金比例',
  `goods_status` int(11) DEFAULT '1' COMMENT '0:下架 1：上架',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `goods_id` (`goods_id`),
  KEY `shop_id_goods_satus` (`shop_id`,`goods_status`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='用于记录参与分销活动的产品';

/*Table structure for table `allwood_goods_share_seller` */

DROP TABLE IF EXISTS `allwood_goods_share_seller`;

CREATE TABLE `allwood_goods_share_seller` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `goods_share_id` int(11) NOT NULL DEFAULT '0' COMMENT '与goods_share_id关联',
  `store_id` int(11) NOT NULL DEFAULT '0' COMMENT '店铺ID',
  `goods_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `order_id` int(11) NOT NULL DEFAULT '0' COMMENT '订单号',
  `userid` int(11) NOT NULL DEFAULT '0' COMMENT '分销者ID',
  `seller_id` int(11) NOT NULL DEFAULT '0' COMMENT '购买者ID',
  `num` int(11) NOT NULL DEFAULT '0' COMMENT '购买数量',
  `rec_id` int(11) NOT NULL DEFAULT '0' COMMENT '订单详情',
  `goods_value` int(11) NOT NULL DEFAULT '0' COMMENT '商品单价',
  `goods_pay_price` decimal(11,2) NOT NULL DEFAULT '0.00' COMMENT '实际成交金额',
  `cps_percent` decimal(4,2) NOT NULL DEFAULT '0.00' COMMENT '策略佣金比例',
  `percent` decimal(4,2) NOT NULL DEFAULT '0.00' COMMENT '当前的分佣比例',
  `addtime` int(11) NOT NULL DEFAULT '0' COMMENT '增加时间',
  `status` int(11) NOT NULL DEFAULT '0' COMMENT '0： 已经付款 1：验收:2退款',
  `r_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '预计返佣',
  `tesu_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) NOT NULL COMMENT '描述',
  `tesu_created` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `goods_share_id` (`goods_share_id`),
  KEY `show_user_id` (`userid`),
  KEY `rec_id` (`rec_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='记录分销卖出的产品';

/*Table structure for table `allwood_groupbuy` */

DROP TABLE IF EXISTS `allwood_groupbuy`;

CREATE TABLE `allwood_groupbuy` (
  `groupbuy_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '团购ID',
  `groupbuy_name` varchar(255) NOT NULL COMMENT '活动名称',
  `start_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始时间',
  `end_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '结束时间',
  `goods_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '商品ID',
  `goods_commonid` int(10) unsigned NOT NULL COMMENT '商品公共表ID',
  `goods_name` varchar(200) NOT NULL COMMENT '商品名称',
  `store_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '店铺ID',
  `store_name` varchar(50) NOT NULL COMMENT '店铺名称',
  `goods_price` decimal(10,2) NOT NULL COMMENT '商品原价',
  `groupbuy_price` decimal(10,2) NOT NULL COMMENT '团购价格',
  `groupbuy_rebate` decimal(10,2) NOT NULL COMMENT '折扣',
  `virtual_quantity` int(10) unsigned NOT NULL COMMENT '虚拟购买数量',
  `upper_limit` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '购买上限',
  `buyer_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '已购买人数',
  `buy_quantity` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '购买数量',
  `groupbuy_intro` text COMMENT '本团介绍',
  `state` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '团购状态 1.未发布 2.已取消 3.进行中 4.已完成 5.已结束',
  `recommended` tinyint(1) unsigned NOT NULL COMMENT '是否推荐 0.未推荐 1.已推荐',
  `views` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '查看次数',
  `class_id` int(10) unsigned NOT NULL COMMENT '团购类别编号',
  `area_id` int(10) unsigned NOT NULL COMMENT '团购地区编号',
  `groupbuy_image` varchar(100) NOT NULL COMMENT '团购图片',
  `groupbuy_image1` varchar(100) DEFAULT NULL COMMENT '团购图片1',
  `remark` varchar(255) NOT NULL COMMENT '备注',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`groupbuy_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='团购商品表';

/*Table structure for table `allwood_groupbuy_area` */

DROP TABLE IF EXISTS `allwood_groupbuy_area`;

CREATE TABLE `allwood_groupbuy_area` (
  `area_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '地区编号',
  `area_name` varchar(50) NOT NULL COMMENT '地区名称',
  `area_parent_id` int(10) unsigned NOT NULL COMMENT '父地区编号',
  `area_sort` tinyint(1) unsigned NOT NULL COMMENT '排序',
  `area_deep` tinyint(1) unsigned NOT NULL COMMENT '深度',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`area_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='团购地区表';

/*Table structure for table `allwood_groupbuy_class` */

DROP TABLE IF EXISTS `allwood_groupbuy_class`;

CREATE TABLE `allwood_groupbuy_class` (
  `class_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '类别编号',
  `class_name` varchar(20) NOT NULL COMMENT '类别名称',
  `class_parent_id` int(10) unsigned NOT NULL COMMENT '父类别编号',
  `sort` tinyint(1) unsigned NOT NULL COMMENT '排序',
  `deep` tinyint(1) unsigned NOT NULL COMMENT '深度',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`class_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='团购类别表';

/*Table structure for table `allwood_groupbuy_price_range` */

DROP TABLE IF EXISTS `allwood_groupbuy_price_range`;

CREATE TABLE `allwood_groupbuy_price_range` (
  `range_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '价格区间编号',
  `range_name` varchar(20) NOT NULL COMMENT '区间名称',
  `range_start` int(10) unsigned NOT NULL COMMENT '区间下限',
  `range_end` int(10) unsigned NOT NULL COMMENT '区间上限',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`range_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='团购价格区间表';

/*Table structure for table `allwood_groupbuy_quota` */

DROP TABLE IF EXISTS `allwood_groupbuy_quota`;

CREATE TABLE `allwood_groupbuy_quota` (
  `quota_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '团购套餐编号',
  `member_id` int(10) unsigned NOT NULL COMMENT '用户编号',
  `store_id` int(10) unsigned NOT NULL COMMENT '店铺编号',
  `member_name` varchar(50) NOT NULL COMMENT '用户名',
  `store_name` varchar(50) NOT NULL COMMENT '店铺名称',
  `start_time` int(10) unsigned NOT NULL COMMENT '套餐开始时间',
  `end_time` int(10) unsigned NOT NULL COMMENT '套餐结束时间',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`quota_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='团购套餐表';

/*Table structure for table `allwood_hot_word` */

DROP TABLE IF EXISTS `allwood_hot_word`;

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
) ENGINE=InnoDB AUTO_INCREMENT=117 DEFAULT CHARSET=utf8 COMMENT='商品分类表';

/*Table structure for table `allwood_inform` */

DROP TABLE IF EXISTS `allwood_inform`;

CREATE TABLE `allwood_inform` (
  `inform_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '举报id',
  `inform_member_id` int(11) NOT NULL COMMENT '举报人id',
  `inform_member_name` varchar(50) NOT NULL COMMENT '举报人会员名',
  `inform_goods_id` int(11) NOT NULL COMMENT '被举报的商品id',
  `inform_goods_name` varchar(100) NOT NULL COMMENT '被举报的商品名称',
  `inform_subject_id` int(11) NOT NULL COMMENT '举报主题id',
  `inform_subject_content` varchar(50) NOT NULL COMMENT '举报主题',
  `inform_content` varchar(100) NOT NULL COMMENT '举报信息',
  `inform_pic1` varchar(100) NOT NULL COMMENT '图片1',
  `inform_pic2` varchar(100) NOT NULL COMMENT '图片2',
  `inform_pic3` varchar(100) NOT NULL COMMENT '图片3',
  `inform_datetime` int(11) NOT NULL COMMENT '举报时间',
  `inform_store_id` int(11) NOT NULL COMMENT '被举报商品的店铺id',
  `inform_state` tinyint(4) NOT NULL COMMENT '举报状态(1未处理/2已处理)',
  `inform_handle_type` tinyint(4) NOT NULL COMMENT '举报处理结果(1无效举报/2恶意举报/3有效举报)',
  `inform_handle_message` varchar(100) NOT NULL COMMENT '举报处理信息',
  `inform_handle_datetime` int(11) NOT NULL DEFAULT '0' COMMENT '举报处理时间',
  `inform_handle_member_id` int(11) NOT NULL DEFAULT '0' COMMENT '管理员id',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`inform_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='举报表';

/*Table structure for table `allwood_inform_subject` */

DROP TABLE IF EXISTS `allwood_inform_subject`;

CREATE TABLE `allwood_inform_subject` (
  `inform_subject_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '举报主题id',
  `inform_subject_content` varchar(100) NOT NULL COMMENT '举报主题内容',
  `inform_subject_type_id` int(11) NOT NULL COMMENT '举报类型id',
  `inform_subject_type_name` varchar(50) NOT NULL COMMENT '举报类型名称 ',
  `inform_subject_state` tinyint(11) NOT NULL COMMENT '举报主题状态(1可用/2失效)',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`inform_subject_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='举报主题表';

/*Table structure for table `allwood_inform_subject_type` */

DROP TABLE IF EXISTS `allwood_inform_subject_type`;

CREATE TABLE `allwood_inform_subject_type` (
  `inform_type_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '举报类型id',
  `inform_type_name` varchar(50) NOT NULL COMMENT '举报类型名称 ',
  `inform_type_desc` varchar(100) NOT NULL COMMENT '举报类型描述',
  `inform_type_state` tinyint(4) NOT NULL COMMENT '举报类型状态(1有效/2失效)',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`inform_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='举报类型表';

/*Table structure for table `allwood_invoice` */

DROP TABLE IF EXISTS `allwood_invoice`;

CREATE TABLE `allwood_invoice` (
  `inv_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '索引id',
  `member_id` int(10) unsigned NOT NULL COMMENT '会员ID',
  `inv_state` enum('1','2') DEFAULT NULL COMMENT '1普通发票2增值税发票',
  `inv_title` varchar(50) DEFAULT '' COMMENT '发票抬头[普通发票]',
  `inv_content` varchar(10) DEFAULT '' COMMENT '发票内容[普通发票]',
  `inv_company` varchar(50) DEFAULT '' COMMENT '单位名称',
  `inv_code` varchar(50) DEFAULT '' COMMENT '纳税人识别号',
  `inv_reg_addr` varchar(50) DEFAULT '' COMMENT '注册地址',
  `inv_reg_phone` varchar(30) DEFAULT '' COMMENT '注册电话',
  `inv_reg_bname` varchar(30) DEFAULT '' COMMENT '开户银行',
  `inv_reg_baccount` varchar(30) DEFAULT '' COMMENT '银行帐户',
  `inv_rec_name` varchar(20) DEFAULT '' COMMENT '收票人姓名',
  `inv_rec_mobphone` varchar(15) DEFAULT '' COMMENT '收票人手机号',
  `inv_rec_province` varchar(30) DEFAULT '' COMMENT '收票人省份',
  `inv_goto_addr` varchar(50) DEFAULT '' COMMENT '送票地址',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`inv_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COMMENT='买家发票信息表';

/*Table structure for table `allwood_lock` */

DROP TABLE IF EXISTS `allwood_lock`;

CREATE TABLE `allwood_lock` (
  `pid` bigint(20) unsigned NOT NULL COMMENT 'IP+TYPE',
  `pvalue` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '次数',
  `expiretime` int(11) NOT NULL DEFAULT '0' COMMENT '锁定截止时间',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  KEY `ip` (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='防灌水表';

/*Table structure for table `allwood_mail_msg_temlates` */

DROP TABLE IF EXISTS `allwood_mail_msg_temlates`;

CREATE TABLE `allwood_mail_msg_temlates` (
  `name` varchar(100) NOT NULL COMMENT '模板名称',
  `title` varchar(100) DEFAULT NULL COMMENT '模板标题',
  `code` varchar(100) NOT NULL COMMENT '模板调用代码',
  `content` text NOT NULL COMMENT '模板内容',
  `type` tinyint(1) NOT NULL COMMENT '模板类别，0为邮件，1为短信息，默认为0',
  `mail_switch` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否开启',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='邮件模板表';

/*Table structure for table `allwood_mb_ad` */

DROP TABLE IF EXISTS `allwood_mb_ad`;

CREATE TABLE `allwood_mb_ad` (
  `link_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '索引id',
  `link_title` varchar(100) DEFAULT NULL COMMENT '标题',
  `link_pic` varchar(150) DEFAULT NULL COMMENT '图片',
  `link_sort` tinyint(3) unsigned NOT NULL DEFAULT '255' COMMENT '排序',
  `link_keyword` varchar(10) DEFAULT NULL COMMENT '关键字',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`link_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COMMENT='手机端广告';

/*Table structure for table `allwood_mb_category` */

DROP TABLE IF EXISTS `allwood_mb_category`;

CREATE TABLE `allwood_mb_category` (
  `gc_id` smallint(5) unsigned DEFAULT NULL COMMENT '商城系统的分类ID',
  `gc_thumb` varchar(150) DEFAULT NULL COMMENT '缩略图',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='一级分类缩略图[手机端]';

/*Table structure for table `allwood_mb_feedback` */

DROP TABLE IF EXISTS `allwood_mb_feedback`;

CREATE TABLE `allwood_mb_feedback` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `content` text,
  `type` enum('1','2') DEFAULT '2' COMMENT '1来自手机端2来自PC端',
  `ftime` int(10) unsigned DEFAULT '0' COMMENT '反馈时间',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='意见反馈';

/*Table structure for table `allwood_mb_home` */

DROP TABLE IF EXISTS `allwood_mb_home`;

CREATE TABLE `allwood_mb_home` (
  `h_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '索引',
  `h_title` varchar(6) NOT NULL COMMENT '标题',
  `h_desc` varchar(10) NOT NULL COMMENT '描述',
  `h_img` varchar(100) NOT NULL COMMENT '图片',
  `h_keyword` varchar(6) NOT NULL COMMENT '关键字',
  `h_sort` tinyint(3) unsigned NOT NULL DEFAULT '255' COMMENT '排序',
  `h_type` varchar(10) NOT NULL COMMENT '类型 (type1 type2)',
  `h_multi_keyword` varchar(50) DEFAULT NULL COMMENT '多关键字',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`h_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COMMENT='首页设置';

/*Table structure for table `allwood_mb_user_token` */

DROP TABLE IF EXISTS `allwood_mb_user_token`;

CREATE TABLE `allwood_mb_user_token` (
  `token_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '令牌编号',
  `member_id` int(10) unsigned NOT NULL COMMENT '用户编号',
  `member_name` varchar(50) NOT NULL COMMENT '用户名',
  `token` varchar(50) NOT NULL COMMENT '登录令牌',
  `login_time` int(10) unsigned NOT NULL COMMENT '登录时间',
  `client_type` varchar(10) NOT NULL COMMENT '客户端类型 android wap',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`token_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='移动端登陆令牌表';

/*Table structure for table `allwood_member` */

DROP TABLE IF EXISTS `allwood_member`;

CREATE TABLE `allwood_member` (
  `member_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '会员id(uid)',
  `member_name` varchar(50) NOT NULL COMMENT '会员名称(username)',
  `member_truename` varchar(20) DEFAULT NULL COMMENT '真实姓名',
  `member_avatar` varchar(50) DEFAULT NULL COMMENT '会员头像(img)',
  `member_sex` tinyint(1) DEFAULT NULL COMMENT '会员性别',
  `member_birthday` date DEFAULT NULL COMMENT '生日',
  `member_passwd` varchar(32) NOT NULL COMMENT '会员密码(password)',
  `member_paypwd` char(32) DEFAULT NULL COMMENT '支付密码',
  `member_email` varchar(100) NOT NULL COMMENT '会员邮箱(email)',
  `member_qq` varchar(100) DEFAULT NULL COMMENT 'qq',
  `member_ww` varchar(100) DEFAULT NULL COMMENT '阿里旺旺',
  `member_login_num` int(11) NOT NULL DEFAULT '1' COMMENT '登录次数',
  `member_time` varchar(10) NOT NULL COMMENT '会员注册时间(time)',
  `member_login_time` varchar(10) NOT NULL COMMENT '当前登录时间(login_time)',
  `member_old_login_time` varchar(10) NOT NULL COMMENT '上次登录时间',
  `member_login_ip` varchar(20) DEFAULT NULL COMMENT '当前登录ip(user_ip)',
  `member_old_login_ip` varchar(20) DEFAULT NULL COMMENT '上次登录ip',
  `member_qqopenid` varchar(100) DEFAULT NULL COMMENT 'qq互联id',
  `member_qqinfo` text COMMENT 'qq账号相关信息',
  `member_sinaopenid` varchar(100) DEFAULT NULL COMMENT '新浪微博登录id',
  `member_sinainfo` text COMMENT '新浪账号相关信息序列化值',
  `member_points` int(11) NOT NULL DEFAULT '0' COMMENT '会员积分',
  `available_predeposit` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '预存款可用金额(money)',
  `freeze_predeposit` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '预存款冻结金额',
  `inform_allow` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否允许举报(1可以/2不可以)',
  `is_buy` tinyint(1) NOT NULL DEFAULT '1' COMMENT '会员是否有购买权限 1为开启 0为关闭',
  `is_allowtalk` tinyint(1) NOT NULL DEFAULT '1' COMMENT '会员是否有咨询和发送站内信的权限 1为开启 0为关闭',
  `member_state` tinyint(1) NOT NULL DEFAULT '1' COMMENT '会员的开启状态 1为开启 0为关闭',
  `member_credit` int(11) NOT NULL DEFAULT '0' COMMENT '会员信用',
  `member_snsvisitnum` int(11) NOT NULL DEFAULT '0' COMMENT 'sns空间访问次数',
  `member_areaid` int(11) DEFAULT NULL COMMENT '地区ID',
  `member_cityid` int(11) DEFAULT NULL COMMENT '城市ID',
  `member_provinceid` int(11) DEFAULT NULL COMMENT '省份ID',
  `member_areainfo` varchar(255) DEFAULT NULL COMMENT '地区内容',
  `member_privacy` text COMMENT '隐私设定',
  `mobile` char(11) DEFAULT NULL,
  `qianming` varchar(255) DEFAULT NULL,
  `groupid` tinyint(4) unsigned DEFAULT '0',
  `addgroup` varchar(255) DEFAULT NULL,
  `emailcode` char(21) DEFAULT '-1',
  `mobilecode` char(21) DEFAULT '-1',
  `passcode` char(21) DEFAULT '-1',
  `reg_key` varchar(100) DEFAULT NULL,
  `jingyan` int(10) unsigned DEFAULT '0',
  `yaoqing` int(10) unsigned DEFAULT NULL,
  `band` varchar(255) DEFAULT NULL,
  `mid` int(11) NOT NULL COMMENT 'java统一接口返回的id',
  `is_attest` tinyint(1) unsigned DEFAULT '0' COMMENT '是否实名认证 0：没有 1：有',
  `idcard` char(18) DEFAULT NULL COMMENT '身份证',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`member_id`),
  KEY `member_name` (`member_name`)
) ENGINE=InnoDB AUTO_INCREMENT=430 DEFAULT CHARSET=utf8 COMMENT='会员表';

/*Table structure for table `allwood_message` */

DROP TABLE IF EXISTS `allwood_message`;

CREATE TABLE `allwood_message` (
  `message_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '短消息索引id',
  `message_parent_id` int(11) NOT NULL COMMENT '回复短消息message_id',
  `from_member_id` int(11) NOT NULL COMMENT '短消息发送人',
  `to_member_id` varchar(1000) NOT NULL COMMENT '短消息接收人',
  `message_title` varchar(50) DEFAULT NULL COMMENT '短消息标题',
  `message_body` varchar(255) NOT NULL COMMENT '短消息内容',
  `message_time` varchar(10) NOT NULL COMMENT '短消息发送时间',
  `message_update_time` varchar(10) DEFAULT NULL COMMENT '短消息回复更新时间',
  `message_open` tinyint(1) NOT NULL DEFAULT '0' COMMENT '短消息打开状态',
  `message_state` tinyint(1) NOT NULL DEFAULT '0' COMMENT '短消息状态，0为正常状态，1为发送人删除状态，2为接收人删除状态',
  `message_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0为私信、1为系统消息、2为留言',
  `read_member_id` varchar(1000) DEFAULT NULL COMMENT '已经读过该消息的会员id',
  `del_member_id` varchar(1000) DEFAULT NULL COMMENT '已经删除该消息的会员id',
  `message_ismore` tinyint(1) NOT NULL DEFAULT '0' COMMENT '站内信是否为一条发给多个用户 0为否 1为多条 ',
  `from_member_name` varchar(100) DEFAULT NULL COMMENT '发信息人用户名',
  `to_member_name` varchar(100) DEFAULT NULL COMMENT '接收人用户名',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`message_id`),
  KEY `from_member_id` (`from_member_id`),
  KEY `to_member_id` (`to_member_id`(255)),
  KEY `message_ismore` (`message_ismore`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COMMENT='短消息';

/*Table structure for table `allwood_micro_adv` */

DROP TABLE IF EXISTS `allwood_micro_adv`;

CREATE TABLE `allwood_micro_adv` (
  `adv_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '广告编号',
  `adv_type` varchar(50) DEFAULT '' COMMENT '广告类型',
  `adv_name` varchar(255) NOT NULL DEFAULT '' COMMENT '广告名称',
  `adv_image` varchar(255) NOT NULL DEFAULT '' COMMENT '广告图片',
  `adv_url` varchar(255) NOT NULL DEFAULT '' COMMENT '广告链接',
  `adv_sort` tinyint(1) unsigned NOT NULL DEFAULT '255' COMMENT '广告排序',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`adv_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='微商城广告表';

/*Table structure for table `allwood_micro_comment` */

DROP TABLE IF EXISTS `allwood_micro_comment`;

CREATE TABLE `allwood_micro_comment` (
  `comment_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '评论编号',
  `comment_type` tinyint(1) NOT NULL COMMENT '评论类型编号',
  `comment_object_id` int(10) unsigned NOT NULL COMMENT '推荐商品编号',
  `comment_message` varchar(255) NOT NULL COMMENT '评论内容',
  `comment_member_id` int(10) unsigned NOT NULL COMMENT '评论人编号',
  `comment_time` int(10) unsigned NOT NULL COMMENT '评论时间',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`comment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='微商城商品评论表';

/*Table structure for table `allwood_micro_goods` */

DROP TABLE IF EXISTS `allwood_micro_goods`;

CREATE TABLE `allwood_micro_goods` (
  `commend_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '推荐编号',
  `commend_member_id` int(10) unsigned NOT NULL COMMENT '推荐人用户编号',
  `commend_goods_id` int(10) unsigned NOT NULL COMMENT '推荐商品编号',
  `commend_goods_commonid` int(10) unsigned NOT NULL COMMENT '商品公共表id',
  `commend_goods_store_id` int(10) unsigned NOT NULL COMMENT '推荐商品店铺编号',
  `commend_goods_name` varchar(100) NOT NULL COMMENT '推荐商品名称',
  `commend_goods_price` decimal(11,2) NOT NULL COMMENT '推荐商品价格',
  `commend_goods_image` varchar(100) NOT NULL COMMENT '推荐商品图片',
  `commend_message` varchar(1000) NOT NULL COMMENT '推荐信息',
  `commend_time` int(10) unsigned NOT NULL COMMENT '推荐时间',
  `class_id` int(10) unsigned NOT NULL,
  `like_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '喜欢数',
  `comment_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评论数',
  `click_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点击数',
  `microshop_commend` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '首页推荐 0-否 1-推荐',
  `microshop_sort` tinyint(3) unsigned NOT NULL DEFAULT '255' COMMENT '排序',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`commend_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='微商城推荐商品表随心看';

/*Table structure for table `allwood_micro_goods_class` */

DROP TABLE IF EXISTS `allwood_micro_goods_class`;

CREATE TABLE `allwood_micro_goods_class` (
  `class_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '分类编号 ',
  `class_name` varchar(50) NOT NULL COMMENT '分类名称',
  `class_parent_id` int(11) unsigned NOT NULL COMMENT '父级分类编号',
  `class_sort` tinyint(4) unsigned NOT NULL COMMENT '排序',
  `class_keyword` varchar(500) NOT NULL DEFAULT '' COMMENT '分类关键字',
  `class_image` varchar(100) NOT NULL DEFAULT '' COMMENT '分类图片',
  `class_commend` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '推荐标志0-不推荐 1-推荐到首页',
  `class_default` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '默认标志，0-非默认 1-默认',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`class_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='微商城商品随心看分类表';

/*Table structure for table `allwood_micro_goods_relation` */

DROP TABLE IF EXISTS `allwood_micro_goods_relation`;

CREATE TABLE `allwood_micro_goods_relation` (
  `relation_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '关系编号',
  `class_id` int(10) unsigned NOT NULL COMMENT '微商城商品分类编号',
  `shop_class_id` int(10) unsigned NOT NULL COMMENT '商城商品分类编号',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`relation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='微商城商品分类和商城商品分类对应关系';

/*Table structure for table `allwood_micro_like` */

DROP TABLE IF EXISTS `allwood_micro_like`;

CREATE TABLE `allwood_micro_like` (
  `like_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '喜欢编号',
  `like_type` tinyint(1) NOT NULL COMMENT '喜欢类型编号',
  `like_object_id` int(10) unsigned NOT NULL COMMENT '喜欢对象编号',
  `like_member_id` int(10) unsigned NOT NULL COMMENT '喜欢人编号',
  `like_time` int(10) unsigned NOT NULL COMMENT '喜欢时间',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`like_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='微商城喜欢表';

/*Table structure for table `allwood_micro_member_info` */

DROP TABLE IF EXISTS `allwood_micro_member_info`;

CREATE TABLE `allwood_micro_member_info` (
  `member_id` int(11) unsigned NOT NULL COMMENT '用户编号',
  `visit_count` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '个人中心访问计数',
  `personal_count` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '已发布个人秀数量',
  `goods_count` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '已发布随心看数量',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='微商城用户信息表';

/*Table structure for table `allwood_micro_personal` */

DROP TABLE IF EXISTS `allwood_micro_personal`;

CREATE TABLE `allwood_micro_personal` (
  `personal_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '推荐编号',
  `commend_member_id` int(10) unsigned NOT NULL COMMENT '推荐人用户编号',
  `commend_image` text NOT NULL COMMENT '推荐图片',
  `commend_buy` text NOT NULL COMMENT '购买信息',
  `commend_message` varchar(1000) NOT NULL COMMENT '推荐信息',
  `commend_time` int(10) unsigned NOT NULL COMMENT '推荐时间',
  `class_id` int(10) unsigned NOT NULL,
  `like_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '喜欢数',
  `comment_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评论数',
  `click_count` int(10) unsigned NOT NULL DEFAULT '0',
  `microshop_commend` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '首页推荐 0-否 1-推荐',
  `microshop_sort` tinyint(3) unsigned NOT NULL DEFAULT '255' COMMENT '排序',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`personal_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='微商城个人秀表';

/*Table structure for table `allwood_micro_personal_class` */

DROP TABLE IF EXISTS `allwood_micro_personal_class`;

CREATE TABLE `allwood_micro_personal_class` (
  `class_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '分类编号 ',
  `class_name` varchar(50) NOT NULL COMMENT '分类名称',
  `class_sort` tinyint(4) unsigned NOT NULL COMMENT '排序',
  `class_image` varchar(100) NOT NULL DEFAULT '' COMMENT '分类图片',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`class_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='微商城个人秀分类表';

/*Table structure for table `allwood_micro_store` */

DROP TABLE IF EXISTS `allwood_micro_store`;

CREATE TABLE `allwood_micro_store` (
  `microshop_store_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '店铺街店铺编号',
  `shop_store_id` int(11) unsigned NOT NULL COMMENT '商城店铺编号',
  `microshop_sort` tinyint(1) unsigned DEFAULT '255' COMMENT '排序',
  `microshop_commend` tinyint(1) unsigned DEFAULT '1' COMMENT '推荐首页标志 1-正常 2-推荐',
  `like_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '喜欢数',
  `comment_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评论数',
  `click_count` int(10) unsigned NOT NULL DEFAULT '0',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`microshop_store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='微商城店铺街表';

/*Table structure for table `allwood_navigation` */

DROP TABLE IF EXISTS `allwood_navigation`;

CREATE TABLE `allwood_navigation` (
  `nav_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '索引ID',
  `nav_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '类别，0自定义导航，1商品分类，2文章导航，3活动导航，默认为0',
  `nav_title` varchar(100) DEFAULT NULL COMMENT '导航标题',
  `nav_url` varchar(255) DEFAULT NULL COMMENT '导航链接',
  `nav_location` tinyint(1) NOT NULL DEFAULT '0' COMMENT '导航位置，0头部，1中部，2底部，默认为0',
  `nav_new_open` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否以新窗口打开，0为否，1为是，默认为0',
  `nav_sort` tinyint(3) unsigned NOT NULL DEFAULT '255' COMMENT '排序',
  `item_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '类别ID，对应着nav_type中的内容，默认为0',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`nav_id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8 COMMENT='页面导航表';

/*Table structure for table `allwood_offline_provisional_customer` */

DROP TABLE IF EXISTS `allwood_offline_provisional_customer`;

CREATE TABLE `allwood_offline_provisional_customer` (
  `provisional_customer_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `usr_name` varchar(36) DEFAULT NULL COMMENT '客户姓名',
  `usr_tel` varchar(16) DEFAULT NULL COMMENT '客户电话',
  `add_time` datetime DEFAULT NULL COMMENT '增加时间',
  `remark` varchar(128) DEFAULT NULL COMMENT '备注',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`provisional_customer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=92 DEFAULT CHARSET=utf8;

/*Table structure for table `allwood_offline_provisional_order` */

DROP TABLE IF EXISTS `allwood_offline_provisional_order`;

CREATE TABLE `allwood_offline_provisional_order` (
  `provisional_order_id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '临时订单id',
  `order_name` varchar(48) DEFAULT NULL COMMENT '清单名称',
  `customer_id` bigint(20) DEFAULT NULL COMMENT '如果是清单则为临时客户id，如果为预订单则为买家id',
  `store_id` bigint(20) DEFAULT NULL COMMENT '商家id',
  `order_type` tinyint(4) DEFAULT NULL COMMENT '订单标志,0:清单，1：预订单',
  `add_time` datetime DEFAULT NULL COMMENT '添加到预订单时间',
  `terminal_usr_id` bigint(20) DEFAULT NULL COMMENT '终端用户id',
  `is_deal` tinyint(4) DEFAULT NULL COMMENT '订单状态：0：未成交，1：已成交。',
  `remark` varchar(128) DEFAULT NULL COMMENT '备注',
  `create_time` datetime DEFAULT NULL COMMENT '创建清单时间',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`provisional_order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=96 DEFAULT CHARSET=utf8;

/*Table structure for table `allwood_offline_provisional_order_goods` */

DROP TABLE IF EXISTS `allwood_offline_provisional_order_goods`;

CREATE TABLE `allwood_offline_provisional_order_goods` (
  `order_goods_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `provisional_order_id` bigint(20) DEFAULT NULL COMMENT '清单id',
  `goods_id` bigint(20) DEFAULT NULL COMMENT '商品id',
  `goods_name` varchar(48) DEFAULT NULL COMMENT '商品名称',
  `goods_price` decimal(10,0) DEFAULT NULL COMMENT '商品价格',
  `goods_num` int(11) DEFAULT NULL COMMENT '商品数量',
  `remark` varchar(48) DEFAULT NULL COMMENT '备注',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`order_goods_id`)
) ENGINE=InnoDB AUTO_INCREMENT=114 DEFAULT CHARSET=utf8;

/*Table structure for table `allwood_offline_provisional_order_goods_image` */

DROP TABLE IF EXISTS `allwood_offline_provisional_order_goods_image`;

CREATE TABLE `allwood_offline_provisional_order_goods_image` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `order_goods_id` bigint(20) DEFAULT NULL COMMENT '清单商品id',
  `goods_image` varchar(100) DEFAULT NULL COMMENT '商品图片',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8;

/*Table structure for table `allwood_offpay_area` */

DROP TABLE IF EXISTS `allwood_offpay_area`;

CREATE TABLE `allwood_offpay_area` (
  `store_id` int(8) NOT NULL COMMENT '商家ID',
  `area_id` text COMMENT '县ID组合',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='货到付款支持地区表';

/*Table structure for table `allwood_order` */

DROP TABLE IF EXISTS `allwood_order`;

CREATE TABLE `allwood_order` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '订单索引id',
  `order_sn` bigint(20) unsigned NOT NULL COMMENT '订单编号',
  `pay_sn` bigint(20) unsigned NOT NULL COMMENT '支付单号',
  `store_id` int(11) unsigned NOT NULL COMMENT '卖家店铺id',
  `store_name` varchar(50) NOT NULL COMMENT '卖家店铺名称',
  `buyer_id` int(11) unsigned NOT NULL COMMENT '买家id',
  `buyer_name` varchar(50) NOT NULL COMMENT '买家姓名',
  `buyer_email` varchar(80) NOT NULL COMMENT '买家电子邮箱',
  `add_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单生成时间',
  `payment_code` char(10) NOT NULL DEFAULT '' COMMENT '支付方式名称代码',
  `payment_time` int(10) unsigned DEFAULT '0' COMMENT '支付(付款)时间',
  `finnshed_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单完成时间',
  `goods_amount` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '商品总价格',
  `order_amount` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '订单总价格',
  `pd_amount` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '预存款支付金额',
  `shipping_fee` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '运费',
  `evaluation_state` enum('0','1') DEFAULT '0' COMMENT '评价状态 0未评价，1已评价',
  `order_state` enum('0','10','20','30','40','50') DEFAULT NULL COMMENT '订单状态：0(已取消)10(默认):未付款;20:已付款;30:已发货;40:已收货;50卖家处理中（分期购专用状态）',
  `refund_state` tinyint(1) unsigned DEFAULT '0' COMMENT '退款状态:0是无退款,1是部分退款,2是全部退款',
  `lock_state` tinyint(1) unsigned DEFAULT '0' COMMENT '锁定状态:0是正常,大于0是锁定,默认是0',
  `refund_amount` decimal(10,2) DEFAULT '0.00' COMMENT '退款金额',
  `delay_time` int(10) unsigned DEFAULT '0' COMMENT '延迟时间,默认为0',
  `order_from` enum('1','2') NOT NULL DEFAULT '1' COMMENT '1WEB2mobile',
  `shipping_code` varchar(50) DEFAULT '' COMMENT '物流单号',
  `order_charity` int(50) NOT NULL,
  `is_investpay` tinyint(4) NOT NULL DEFAULT '0',
  `tesu_seller_remark` varchar(300) DEFAULT NULL COMMENT '卖家备注',
  `order_type` tinyint(1) DEFAULT '1' COMMENT '订单类型 1 普通订单 2 分期购订单 3 其它',
  `order_amount_delta` int(11) DEFAULT '0' COMMENT '分期购订单支付差额',
  `period` tinyint(1) DEFAULT '0' COMMENT '分期购订单分期期数 默认为0',
  `interest_rate` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '利率',
  `factorage` int(11) DEFAULT '0' COMMENT '每一期的手续费',
  `interest_total` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '总利息',
  `gxht_code` char(15) DEFAULT '' COMMENT '购销合同编号',
  `fbxy_code` char(15) DEFAULT '' COMMENT '发标协议编号',
  `jkxy_code` char(15) DEFAULT '' COMMENT '借款协议编号',
  `down_payment_time` int(11) DEFAULT NULL,
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=469 DEFAULT CHARSET=utf8 COMMENT='订单表';

/*Table structure for table `allwood_order_bill` */

DROP TABLE IF EXISTS `allwood_order_bill`;

CREATE TABLE `allwood_order_bill` (
  `ob_no` int(11) NOT NULL AUTO_INCREMENT COMMENT '结算单编号(年月店铺ID)',
  `ob_start_date` int(11) NOT NULL COMMENT '开始日期',
  `ob_end_date` int(11) NOT NULL COMMENT '结束日期',
  `ob_order_totals` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '订单金额',
  `ob_shipping_totals` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '运费',
  `ob_order_return_totals` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '退单金额',
  `ob_commis_totals` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '佣金金额',
  `ob_commis_return_totals` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '退还佣金',
  `ob_store_cost_totals` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '店铺促销活动费用',
  `ob_result_totals` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '应结金额',
  `ob_create_date` int(11) DEFAULT '0' COMMENT '生成结算单日期',
  `os_month` mediumint(6) unsigned NOT NULL COMMENT '结算单年月份',
  `ob_state` enum('1','2','3','4') DEFAULT '1' COMMENT '1默认2店家已确认3平台已审核4结算完成',
  `ob_pay_date` int(11) DEFAULT '0' COMMENT '付款日期',
  `ob_pay_content` varchar(200) DEFAULT '' COMMENT '支付备注',
  `ob_store_id` int(11) NOT NULL COMMENT '店铺ID',
  `ob_store_name` varchar(50) DEFAULT NULL COMMENT '店铺名',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`ob_no`)
) ENGINE=InnoDB AUTO_INCREMENT=2147483647 DEFAULT CHARSET=utf8 COMMENT='结算表';

/*Table structure for table `allwood_order_common` */

DROP TABLE IF EXISTS `allwood_order_common`;

CREATE TABLE `allwood_order_common` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '订单索引id',
  `store_id` int(10) unsigned NOT NULL COMMENT '店铺ID',
  `shipping_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '配送时间',
  `shipping_express_id` tinyint(1) NOT NULL DEFAULT '0' COMMENT '配送公司ID',
  `evaluation_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评价时间',
  `evalseller_state` enum('0','1') NOT NULL DEFAULT '0' COMMENT '卖家是否已评价买家',
  `evalseller_time` int(10) unsigned NOT NULL COMMENT '卖家评价买家的时间',
  `order_message` varchar(300) DEFAULT NULL COMMENT '订单留言',
  `order_pointscount` int(11) NOT NULL DEFAULT '0' COMMENT '订单赠送积分',
  `voucher_price` int(11) DEFAULT NULL COMMENT '代金券面额',
  `voucher_code` varchar(32) DEFAULT NULL COMMENT '代金券编码',
  `deliver_explain` text COMMENT '发货备注',
  `daddress_id` mediumint(9) NOT NULL DEFAULT '0' COMMENT '发货地址ID',
  `reciver_name` varchar(50) NOT NULL COMMENT '收货人姓名',
  `reciver_info` varchar(500) NOT NULL COMMENT '收货人其它信息',
  `reciver_province_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '收货人省级ID',
  `invoice_info` varchar(500) DEFAULT '' COMMENT '发票信息',
  `promotion_info` varchar(500) DEFAULT '' COMMENT '促销信息备注',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=469 DEFAULT CHARSET=utf8 COMMENT='订单信息扩展表';

/*Table structure for table `allwood_order_goods` */

DROP TABLE IF EXISTS `allwood_order_goods`;

CREATE TABLE `allwood_order_goods` (
  `rec_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '订单商品表索引id',
  `order_id` int(11) NOT NULL COMMENT '订单id',
  `goods_id` int(11) NOT NULL COMMENT '商品id',
  `goods_name` varchar(50) NOT NULL COMMENT '商品名称',
  `goods_price` decimal(10,2) NOT NULL COMMENT '商品价格',
  `goods_num` smallint(5) unsigned NOT NULL DEFAULT '1' COMMENT '商品数量',
  `goods_image` varchar(100) DEFAULT NULL COMMENT '商品图片',
  `goods_pay_price` decimal(10,2) unsigned NOT NULL COMMENT '商品实际成交价',
  `store_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '店铺ID',
  `buyer_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '买家ID',
  `goods_type` enum('1','2','3','4','5') NOT NULL DEFAULT '1' COMMENT '1默认2团购商品3限时折扣商品4组合套装5赠品',
  `promotions_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '促销活动ID（团购ID/限时折扣ID/优惠套装ID）与goods_type搭配使用',
  `commis_rate` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '佣金比例',
  `shipping_code` char(32) DEFAULT NULL COMMENT '运单号',
  `express_id` char(32) DEFAULT NULL COMMENT '物流公司',
  `order_goods_state` tinyint(1) DEFAULT '30' COMMENT '未签收30  已签收40',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`rec_id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=489 DEFAULT CHARSET=utf8 COMMENT='订单商品表';

/*Table structure for table `allwood_order_log` */

DROP TABLE IF EXISTS `allwood_order_log`;

CREATE TABLE `allwood_order_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `order_id` int(11) NOT NULL COMMENT '订单id',
  `log_msg` varchar(150) DEFAULT '' COMMENT '文字描述',
  `log_time` int(10) unsigned NOT NULL COMMENT '处理时间',
  `log_role` char(2) NOT NULL COMMENT '操作角色',
  `log_user` varchar(30) DEFAULT '' COMMENT '操作人',
  `log_orderstate` enum('0','10','20','30','40') DEFAULT NULL COMMENT '订单状态：0(已取消)10:未付款;20:已付款;30:已发货;40:已收货;',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB AUTO_INCREMENT=914 DEFAULT CHARSET=utf8 COMMENT='订单处理历史表';

/*Table structure for table `allwood_order_pay` */

DROP TABLE IF EXISTS `allwood_order_pay`;

CREATE TABLE `allwood_order_pay` (
  `pay_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pay_sn` bigint(20) unsigned NOT NULL COMMENT '支付单号',
  `buyer_id` int(10) unsigned NOT NULL COMMENT '买家ID',
  `api_pay_state` enum('0','1') DEFAULT '0' COMMENT '0默认未支付1已支付(只有第三方支付接口通知到时才会更改此状态)',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`pay_id`)
) ENGINE=InnoDB AUTO_INCREMENT=469 DEFAULT CHARSET=utf8 COMMENT='订单支付表';

/*Table structure for table `allwood_order_statis` */

DROP TABLE IF EXISTS `allwood_order_statis`;

CREATE TABLE `allwood_order_statis` (
  `os_month` int(9) unsigned NOT NULL DEFAULT '0' COMMENT '统计编号(年月)',
  `os_year` smallint(6) DEFAULT '0' COMMENT '年',
  `os_start_date` int(11) NOT NULL COMMENT '开始日期',
  `os_end_date` int(11) NOT NULL COMMENT '结束日期',
  `os_order_totals` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '订单金额',
  `os_shipping_totals` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '运费',
  `os_order_return_totals` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '退单金额',
  `os_commis_totals` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '佣金金额',
  `os_commis_return_totals` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '退还佣金',
  `os_store_cost_totals` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '店铺促销活动费用',
  `os_result_totals` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '本期应结',
  `os_create_date` int(11) DEFAULT NULL COMMENT '创建记录日期',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`os_month`),
  KEY `os_month` (`os_month`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='月销量统计表';

/*Table structure for table `allwood_order_store` */

DROP TABLE IF EXISTS `allwood_order_store`;

CREATE TABLE `allwood_order_store` (
  `order_store_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL DEFAULT '0' COMMENT '订单号',
  `rec_id` int(11) NOT NULL DEFAULT '0' COMMENT '明细索引',
  `user1_id` int(11) NOT NULL DEFAULT '0' COMMENT '一级用户ID',
  `user2_id` int(11) NOT NULL DEFAULT '0' COMMENT '二级用户ID',
  `orders_cnt` int(11) NOT NULL DEFAULT '0' COMMENT '购买数量',
  `percent` decimal(4,2) NOT NULL DEFAULT '0.00' COMMENT '当前店铺返佣比率',
  `cps_percent` decimal(4,2) NOT NULL DEFAULT '0.00' COMMENT '策略返佣比率',
  `goods_pay_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '实际成交价格',
  `r_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '预计返佣',
  `addtime` int(11) NOT NULL COMMENT '购买时间',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`order_store_id`),
  KEY `rec_id` (`rec_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='店铺卖出的产品';

/*Table structure for table `allwood_p_booth_goods` */

DROP TABLE IF EXISTS `allwood_p_booth_goods`;

CREATE TABLE `allwood_p_booth_goods` (
  `booth_goods_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '套餐商品id',
  `store_id` int(10) unsigned NOT NULL COMMENT '店铺id',
  `goods_id` int(10) unsigned NOT NULL COMMENT '商品id',
  `gc_id` int(10) unsigned NOT NULL COMMENT '商品分类id',
  `booth_state` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '套餐状态 1开启 0关闭 默认1',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`booth_goods_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COMMENT='展位商品表';

/*Table structure for table `allwood_p_booth_quota` */

DROP TABLE IF EXISTS `allwood_p_booth_quota`;

CREATE TABLE `allwood_p_booth_quota` (
  `booth_quota_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '套餐id',
  `store_id` int(10) unsigned NOT NULL COMMENT '店铺id',
  `store_name` varchar(50) NOT NULL COMMENT '店铺名称',
  `booth_quota_starttime` int(10) unsigned NOT NULL COMMENT '开始时间',
  `booth_quota_endtime` int(10) unsigned NOT NULL COMMENT '结束时间',
  `booth_state` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '套餐状态 1开启 0关闭 默认1',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`booth_quota_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='展位套餐表';

/*Table structure for table `allwood_p_bundling` */

DROP TABLE IF EXISTS `allwood_p_bundling`;

CREATE TABLE `allwood_p_bundling` (
  `bl_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '组合ID',
  `bl_name` varchar(50) NOT NULL COMMENT '组合名称',
  `store_id` int(11) NOT NULL COMMENT '店铺名称',
  `store_name` varchar(50) NOT NULL COMMENT '店铺名称',
  `bl_discount_price` decimal(10,2) NOT NULL COMMENT '组合价格',
  `bl_freight_choose` tinyint(1) NOT NULL COMMENT '运费承担方式',
  `bl_freight` decimal(10,2) NOT NULL COMMENT '运费',
  `bl_state` tinyint(1) NOT NULL DEFAULT '1' COMMENT '组合状态 0-关闭/1-开启',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`bl_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='组合销售活动表';

/*Table structure for table `allwood_p_bundling_goods` */

DROP TABLE IF EXISTS `allwood_p_bundling_goods`;

CREATE TABLE `allwood_p_bundling_goods` (
  `bl_goods_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '组合商品id',
  `bl_id` int(11) NOT NULL COMMENT '组合id',
  `goods_id` int(10) unsigned NOT NULL COMMENT '商品id',
  `goods_name` varchar(50) NOT NULL COMMENT '商品名称',
  `goods_image` varchar(100) NOT NULL COMMENT '商品图片',
  `bl_goods_price` decimal(10,2) NOT NULL COMMENT '商品价格',
  `bl_appoint` tinyint(3) unsigned NOT NULL COMMENT '指定商品 1是，0否',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`bl_goods_id`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8 COMMENT='组合销售活动商品表';

/*Table structure for table `allwood_p_bundling_quota` */

DROP TABLE IF EXISTS `allwood_p_bundling_quota`;

CREATE TABLE `allwood_p_bundling_quota` (
  `bl_quota_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '套餐ID',
  `store_id` int(11) NOT NULL COMMENT '店铺id',
  `store_name` varchar(50) NOT NULL COMMENT '店铺名称',
  `member_id` int(11) NOT NULL COMMENT '会员id',
  `member_name` varchar(50) NOT NULL COMMENT '会员名称',
  `bl_quota_month` tinyint(3) unsigned NOT NULL COMMENT '购买数量（单位月）',
  `bl_quota_starttime` varchar(10) NOT NULL COMMENT '套餐开始时间',
  `bl_quota_endtime` varchar(10) NOT NULL COMMENT '套餐结束时间',
  `bl_state` tinyint(1) unsigned NOT NULL COMMENT '套餐状态：0关闭，1开启。默认为 1',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`bl_quota_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='组合销售套餐表';

/*Table structure for table `allwood_p_mansong` */

DROP TABLE IF EXISTS `allwood_p_mansong`;

CREATE TABLE `allwood_p_mansong` (
  `mansong_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '满送活动编号',
  `mansong_name` varchar(50) NOT NULL COMMENT '活动名称',
  `quota_id` int(10) unsigned NOT NULL COMMENT '套餐编号',
  `start_time` int(10) unsigned NOT NULL COMMENT '活动开始时间',
  `end_time` int(10) unsigned NOT NULL COMMENT '活动结束时间',
  `member_id` int(10) unsigned NOT NULL COMMENT '用户编号',
  `store_id` int(10) unsigned NOT NULL COMMENT '店铺编号',
  `member_name` varchar(50) NOT NULL COMMENT '用户名',
  `store_name` varchar(50) NOT NULL COMMENT '店铺名称',
  `state` tinyint(1) unsigned NOT NULL COMMENT '活动状态(1-未发布/2-正常/3-取消/4-失效/5-结束)',
  `remark` varchar(200) NOT NULL COMMENT '备注',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`mansong_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COMMENT='满就送活动表';

/*Table structure for table `allwood_p_mansong_quota` */

DROP TABLE IF EXISTS `allwood_p_mansong_quota`;

CREATE TABLE `allwood_p_mansong_quota` (
  `quota_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '满就送套餐编号',
  `apply_id` int(10) unsigned NOT NULL COMMENT '申请编号',
  `member_id` int(10) unsigned NOT NULL COMMENT '用户编号',
  `store_id` int(10) unsigned NOT NULL COMMENT '店铺编号',
  `member_name` varchar(50) NOT NULL COMMENT '用户名',
  `store_name` varchar(50) NOT NULL COMMENT '店铺名称',
  `start_time` int(10) unsigned NOT NULL COMMENT '开始时间',
  `end_time` int(10) unsigned NOT NULL COMMENT '结束时间',
  `state` tinyint(1) unsigned NOT NULL COMMENT '配额状态(1-可用/2-取消/3-结束)',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`quota_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='满就送套餐表';

/*Table structure for table `allwood_p_mansong_rule` */

DROP TABLE IF EXISTS `allwood_p_mansong_rule`;

CREATE TABLE `allwood_p_mansong_rule` (
  `rule_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '规则编号',
  `mansong_id` int(10) unsigned NOT NULL COMMENT '活动编号',
  `price` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '级别价格',
  `discount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '减现金优惠金额',
  `mansong_goods_name` varchar(50) NOT NULL COMMENT '礼品名称',
  `goods_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '商品编号',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`rule_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COMMENT='满就送活动规则表';

/*Table structure for table `allwood_p_xianshi` */

DROP TABLE IF EXISTS `allwood_p_xianshi`;

CREATE TABLE `allwood_p_xianshi` (
  `xianshi_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '限时编号',
  `xianshi_name` varchar(50) NOT NULL COMMENT '活动名称',
  `xianshi_title` varchar(10) DEFAULT NULL COMMENT '活动标题',
  `xianshi_explain` varchar(50) DEFAULT NULL COMMENT '活动说明',
  `quota_id` int(10) unsigned NOT NULL COMMENT '套餐编号',
  `start_time` int(10) unsigned NOT NULL COMMENT '活动开始时间',
  `end_time` int(10) unsigned NOT NULL COMMENT '活动结束时间',
  `member_id` int(10) unsigned NOT NULL COMMENT '用户编号',
  `store_id` int(10) unsigned NOT NULL COMMENT '店铺编号',
  `member_name` varchar(50) NOT NULL COMMENT '用户名',
  `store_name` varchar(50) NOT NULL COMMENT '店铺名称',
  `lower_limit` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '购买下限，1为不限制',
  `state` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态，0-取消 1-正常',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`xianshi_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COMMENT='限时折扣活动表';

/*Table structure for table `allwood_p_xianshi_goods` */

DROP TABLE IF EXISTS `allwood_p_xianshi_goods`;

CREATE TABLE `allwood_p_xianshi_goods` (
  `xianshi_goods_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '限时折扣商品表',
  `xianshi_id` int(10) unsigned NOT NULL COMMENT '限时活动编号',
  `xianshi_name` varchar(50) NOT NULL COMMENT '活动名称',
  `xianshi_title` varchar(10) DEFAULT NULL COMMENT '活动标题',
  `xianshi_explain` varchar(50) DEFAULT NULL COMMENT '活动说明',
  `goods_id` int(10) unsigned NOT NULL COMMENT '商品编号',
  `store_id` int(10) unsigned NOT NULL COMMENT '店铺编号',
  `goods_name` varchar(100) NOT NULL COMMENT '商品名称',
  `goods_price` decimal(10,2) NOT NULL COMMENT '店铺价格',
  `xianshi_price` decimal(10,2) NOT NULL COMMENT '限时折扣价格',
  `goods_image` varchar(100) NOT NULL COMMENT '商品图片',
  `start_time` int(10) unsigned NOT NULL COMMENT '开始时间',
  `end_time` int(10) unsigned NOT NULL COMMENT '结束时间',
  `lower_limit` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '购买下限，0为不限制',
  `state` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态，0-取消 1-正常',
  `xianshi_recommend` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '推荐标志 0-未推荐 1-已推荐',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`xianshi_goods_id`)
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8 COMMENT='限时折扣商品表';

/*Table structure for table `allwood_p_xianshi_quota` */

DROP TABLE IF EXISTS `allwood_p_xianshi_quota`;

CREATE TABLE `allwood_p_xianshi_quota` (
  `quota_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '限时折扣套餐编号',
  `member_id` int(10) unsigned NOT NULL COMMENT '用户编号',
  `store_id` int(10) unsigned NOT NULL COMMENT '店铺编号',
  `member_name` varchar(50) NOT NULL COMMENT '用户名',
  `store_name` varchar(50) NOT NULL COMMENT '店铺名称',
  `start_time` int(10) unsigned NOT NULL COMMENT '套餐开始时间',
  `end_time` int(10) unsigned NOT NULL COMMENT '套餐结束时间',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`quota_id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 COMMENT='限时折扣套餐表';

/*Table structure for table `allwood_payment` */

DROP TABLE IF EXISTS `allwood_payment`;

CREATE TABLE `allwood_payment` (
  `payment_id` tinyint(1) unsigned NOT NULL COMMENT '支付索引id',
  `payment_code` char(10) NOT NULL COMMENT '支付代码名称',
  `payment_name` char(10) NOT NULL COMMENT '支付名称',
  `payment_config` text COMMENT '支付接口配置信息',
  `payment_state` enum('0','1') NOT NULL DEFAULT '0' COMMENT '接口状态0禁用1启用',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`payment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='支付方式表';

/*Table structure for table `allwood_pd_cash` */

DROP TABLE IF EXISTS `allwood_pd_cash`;

CREATE TABLE `allwood_pd_cash` (
  `pdc_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增编号',
  `pdc_sn` bigint(20) NOT NULL COMMENT '记录唯一标示',
  `pdc_member_id` int(11) NOT NULL COMMENT '会员编号',
  `pdc_member_name` varchar(50) NOT NULL COMMENT '会员名称',
  `pdc_amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
  `pdc_bank_name` varchar(40) NOT NULL COMMENT '收款银行',
  `pdc_bank_no` varchar(30) DEFAULT NULL COMMENT '收款账号',
  `pdc_bank_user` varchar(10) DEFAULT NULL COMMENT '开户人姓名',
  `pdc_add_time` int(11) NOT NULL COMMENT '添加时间',
  `pdc_payment_time` int(11) DEFAULT NULL COMMENT '付款时间',
  `pdc_payment_state` enum('0','1') NOT NULL DEFAULT '0' COMMENT '提现支付状态 0默认1支付完成',
  `pdc_payment_admin` varchar(30) DEFAULT NULL COMMENT '支付管理员',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`pdc_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COMMENT='预存款提现记录表';

/*Table structure for table `allwood_pd_log` */

DROP TABLE IF EXISTS `allwood_pd_log`;

CREATE TABLE `allwood_pd_log` (
  `lg_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增编号',
  `lg_member_id` int(11) NOT NULL COMMENT '会员编号',
  `lg_member_name` varchar(50) NOT NULL COMMENT '会员名称',
  `lg_admin_name` varchar(50) DEFAULT NULL COMMENT '管理员名称',
  `lg_type` varchar(15) NOT NULL DEFAULT '' COMMENT 'order_pay下单支付预存款,order_freeze下单冻结预存款,order_cancel取消订单解冻预存款,order_comb_pay下单支付被冻结的预存款,recharge充值,cash_apply申请提现冻结预存款,cash_pay提现成功,cash_del取消提现申请，解冻预存款,refund退款',
  `lg_av_amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '可用金额变更0表示未变更',
  `lg_freeze_amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '冻结金额变更0表示未变更',
  `lg_add_time` int(11) NOT NULL COMMENT '添加时间',
  `lg_desc` varchar(150) DEFAULT NULL COMMENT '描述',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`lg_id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8 COMMENT='预存款变更日志表';

/*Table structure for table `allwood_pd_recharge` */

DROP TABLE IF EXISTS `allwood_pd_recharge`;

CREATE TABLE `allwood_pd_recharge` (
  `pdr_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增编号',
  `pdr_sn` bigint(20) unsigned NOT NULL COMMENT '记录唯一标示',
  `pdr_member_id` int(11) NOT NULL COMMENT '会员编号',
  `pdr_member_name` varchar(50) NOT NULL COMMENT '会员名称',
  `pdr_amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '充值金额',
  `pdr_payment_code` varchar(20) DEFAULT '' COMMENT '支付方式',
  `pdr_payment_name` varchar(15) DEFAULT '' COMMENT '支付方式',
  `pdr_trade_sn` varchar(50) DEFAULT '' COMMENT '第三方支付接口交易号',
  `pdr_add_time` int(11) NOT NULL COMMENT '添加时间',
  `pdr_payment_state` enum('0','1') NOT NULL DEFAULT '0' COMMENT '支付状态 0未支付1支付',
  `pdr_payment_time` int(11) NOT NULL DEFAULT '0' COMMENT '支付时间',
  `pdr_admin` varchar(30) DEFAULT '' COMMENT '管理员名',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`pdr_id`)
) ENGINE=InnoDB AUTO_INCREMENT=111 DEFAULT CHARSET=utf8 COMMENT='预存款充值表';

/*Table structure for table `allwood_platform_param` */

DROP TABLE IF EXISTS `allwood_platform_param`;

CREATE TABLE `allwood_platform_param` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `money_total` decimal(18,0) DEFAULT NULL COMMENT '账户总金额',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='全木行平台参数表';

/*Table structure for table `allwood_points_cart` */

DROP TABLE IF EXISTS `allwood_points_cart`;

CREATE TABLE `allwood_points_cart` (
  `pcart_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `pmember_id` int(11) NOT NULL COMMENT '会员编号',
  `pgoods_id` int(11) NOT NULL COMMENT '积分礼品序号',
  `pgoods_name` varchar(100) NOT NULL COMMENT '积分礼品名称',
  `pgoods_points` int(11) NOT NULL COMMENT '积分礼品兑换积分',
  `pgoods_choosenum` int(11) NOT NULL COMMENT '选择积分礼品数量',
  `pgoods_image` varchar(100) DEFAULT NULL COMMENT '积分礼品图片',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`pcart_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='积分礼品兑换购物车';

/*Table structure for table `allwood_points_goods` */

DROP TABLE IF EXISTS `allwood_points_goods`;

CREATE TABLE `allwood_points_goods` (
  `pgoods_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '积分礼品索引id',
  `pgoods_name` varchar(100) NOT NULL COMMENT '积分礼品名称',
  `pgoods_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '积分礼品原价',
  `pgoods_points` int(11) NOT NULL COMMENT '积分礼品兑换所需积分',
  `pgoods_image` varchar(100) NOT NULL COMMENT '积分礼品默认封面图片',
  `pgoods_tag` varchar(100) NOT NULL COMMENT '积分礼品标签',
  `pgoods_serial` varchar(50) NOT NULL COMMENT '积分礼品货号',
  `pgoods_storage` int(11) NOT NULL DEFAULT '0' COMMENT '积分礼品库存数',
  `pgoods_show` tinyint(1) NOT NULL COMMENT '积分礼品上架 0表示下架 1表示上架',
  `pgoods_commend` tinyint(1) NOT NULL COMMENT '积分礼品推荐',
  `pgoods_add_time` int(11) NOT NULL COMMENT '积分礼品添加时间',
  `pgoods_keywords` varchar(100) DEFAULT NULL COMMENT '积分礼品关键字',
  `pgoods_description` varchar(200) DEFAULT NULL COMMENT '积分礼品描述',
  `pgoods_body` text NOT NULL COMMENT '积分礼品详细内容',
  `pgoods_state` tinyint(1) NOT NULL DEFAULT '0' COMMENT '积分礼品状态，0开启，1禁售',
  `pgoods_close_reason` varchar(255) DEFAULT NULL COMMENT '积分礼品禁售原因',
  `pgoods_salenum` int(11) NOT NULL DEFAULT '0' COMMENT '积分礼品售出数量',
  `pgoods_view` int(11) NOT NULL DEFAULT '0' COMMENT '积分商品浏览次数',
  `pgoods_islimit` tinyint(1) NOT NULL COMMENT '是否限制每会员兑换数量',
  `pgoods_limitnum` int(11) DEFAULT NULL COMMENT '每会员限制兑换数量',
  `pgoods_islimittime` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否限制兑换时间 0为不限制 1为限制',
  `pgoods_starttime` int(11) DEFAULT NULL COMMENT '兑换开始时间',
  `pgoods_endtime` int(11) DEFAULT NULL COMMENT '兑换结束时间',
  `pgoods_sort` int(11) NOT NULL DEFAULT '0' COMMENT '礼品排序',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`pgoods_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='积分礼品表';

/*Table structure for table `allwood_points_log` */

DROP TABLE IF EXISTS `allwood_points_log`;

CREATE TABLE `allwood_points_log` (
  `pl_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '积分日志编号',
  `pl_memberid` int(11) NOT NULL COMMENT '会员编号',
  `pl_membername` varchar(100) NOT NULL COMMENT '会员名称',
  `pl_adminid` int(11) DEFAULT NULL COMMENT '管理员编号',
  `pl_adminname` varchar(100) DEFAULT NULL COMMENT '管理员名称',
  `pl_points` int(11) NOT NULL DEFAULT '0' COMMENT '积分数负数表示扣除',
  `pl_addtime` int(11) NOT NULL COMMENT '添加时间',
  `pl_desc` varchar(100) NOT NULL COMMENT '操作描述',
  `pl_stage` varchar(50) NOT NULL COMMENT '操作阶段',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`pl_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1377 DEFAULT CHARSET=utf8 COMMENT='会员积分日志表';

/*Table structure for table `allwood_points_order` */

DROP TABLE IF EXISTS `allwood_points_order`;

CREATE TABLE `allwood_points_order` (
  `point_orderid` int(11) NOT NULL AUTO_INCREMENT COMMENT '兑换订单编号',
  `point_ordersn` varchar(100) NOT NULL COMMENT '兑换订单编号',
  `point_buyerid` int(11) NOT NULL COMMENT '兑换会员id',
  `point_buyername` varchar(50) NOT NULL COMMENT '兑换会员姓名',
  `point_buyeremail` varchar(100) NOT NULL COMMENT '兑换会员email',
  `point_addtime` int(11) NOT NULL COMMENT '兑换订单生成时间',
  `point_paymentid` int(11) NOT NULL COMMENT '支付方式id',
  `point_paymentname` varchar(50) NOT NULL COMMENT '支付方式名称',
  `point_paymentcode` varchar(50) NOT NULL COMMENT '支付方式名称代码',
  `point_paymentdirect` tinyint(1) DEFAULT '1' COMMENT '支付类型:1是即时到帐,2是但保交易',
  `point_outsn` varchar(100) NOT NULL COMMENT '订单编号，外部支付时使用，有些外部支付系统要求特定的订单编号',
  `point_paymenttime` int(11) DEFAULT NULL COMMENT '支付(付款)时间',
  `point_paymessage` varchar(300) DEFAULT NULL COMMENT '支付留言',
  `point_shippingtime` int(11) DEFAULT NULL COMMENT '配送时间',
  `point_shippingcode` varchar(50) DEFAULT NULL COMMENT '物流单号',
  `point_shippingdesc` varchar(500) DEFAULT NULL COMMENT '发货描述',
  `point_outpaymentcode` varchar(255) DEFAULT NULL COMMENT '外部交易平台单独使用的标识字符串',
  `point_finnshedtime` int(11) DEFAULT NULL COMMENT '订单完成时间',
  `point_allpoint` int(11) NOT NULL DEFAULT '0' COMMENT '兑换总积分',
  `point_orderamount` decimal(10,2) NOT NULL COMMENT '兑换订单总金额',
  `point_shippingcharge` tinyint(1) NOT NULL DEFAULT '0' COMMENT '运费承担方式 0表示卖家 1表示买家',
  `point_shippingfee` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '运费金额',
  `point_ordermessage` varchar(300) DEFAULT NULL COMMENT '订单留言',
  `point_orderstate` int(11) NOT NULL DEFAULT '10' COMMENT '订单状态：10(默认):未付款;11已付款;20:确认付款;30:已发货;40:已收货;50已完成;2已取消',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`point_orderid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='兑换订单表';

/*Table structure for table `allwood_points_orderaddress` */

DROP TABLE IF EXISTS `allwood_points_orderaddress`;

CREATE TABLE `allwood_points_orderaddress` (
  `point_oaid` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `point_orderid` int(11) NOT NULL COMMENT '订单id',
  `point_truename` varchar(50) NOT NULL COMMENT '收货人姓名',
  `point_areaid` int(11) NOT NULL COMMENT '地区id',
  `point_areainfo` varchar(100) NOT NULL COMMENT '地区内容',
  `point_address` varchar(200) NOT NULL COMMENT '详细地址',
  `point_zipcode` varchar(20) NOT NULL COMMENT '邮政编码',
  `point_telphone` varchar(20) NOT NULL COMMENT '电话号码',
  `point_mobphone` varchar(20) NOT NULL COMMENT '手机号码',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`point_oaid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='兑换订单地址表';

/*Table structure for table `allwood_points_ordergoods` */

DROP TABLE IF EXISTS `allwood_points_ordergoods`;

CREATE TABLE `allwood_points_ordergoods` (
  `point_recid` int(11) NOT NULL AUTO_INCREMENT COMMENT '订单礼品表索引',
  `point_orderid` int(11) NOT NULL COMMENT '订单id',
  `point_goodsid` int(11) NOT NULL COMMENT '礼品id',
  `point_goodsname` varchar(100) NOT NULL COMMENT '礼品名称',
  `point_goodspoints` int(11) NOT NULL COMMENT '礼品兑换积分',
  `point_goodsnum` int(11) NOT NULL COMMENT '礼品数量',
  `point_goodsimage` varchar(100) DEFAULT NULL COMMENT '礼品图片',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`point_recid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='兑换订单商品表';

/*Table structure for table `allwood_rec_position` */

DROP TABLE IF EXISTS `allwood_rec_position`;

CREATE TABLE `allwood_rec_position` (
  `rec_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `pic_type` enum('1','2','0') NOT NULL DEFAULT '1' COMMENT '0文字1本地图片2远程',
  `title` varchar(200) NOT NULL DEFAULT '' COMMENT '标题',
  `content` text NOT NULL COMMENT '序列化推荐位内容',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`rec_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='推荐位';

/*Table structure for table `allwood_refund_return` */

DROP TABLE IF EXISTS `allwood_refund_return`;

CREATE TABLE `allwood_refund_return` (
  `refund_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '记录ID',
  `order_id` int(10) unsigned NOT NULL COMMENT '订单ID',
  `order_sn` varchar(50) NOT NULL COMMENT '订单编号',
  `refund_sn` varchar(50) NOT NULL COMMENT '申请编号',
  `store_id` int(10) unsigned NOT NULL COMMENT '店铺ID',
  `store_name` varchar(20) NOT NULL COMMENT '店铺名称',
  `buyer_id` int(10) unsigned NOT NULL COMMENT '买家ID',
  `buyer_name` varchar(50) NOT NULL COMMENT '买家会员名',
  `goods_id` int(10) unsigned NOT NULL COMMENT '商品ID,全部退款是0',
  `order_goods_id` int(10) unsigned DEFAULT '0' COMMENT '订单商品ID,全部退款是0',
  `goods_name` varchar(50) NOT NULL COMMENT '商品名称',
  `goods_num` int(10) unsigned DEFAULT '1' COMMENT '商品数量',
  `refund_amount` decimal(10,2) DEFAULT '0.00' COMMENT '退款金额',
  `goods_image` varchar(100) DEFAULT NULL COMMENT '商品图片',
  `order_goods_type` tinyint(1) unsigned DEFAULT '1' COMMENT '订单商品类型:1默认2团购商品3限时折扣商品4组合套装',
  `refund_type` tinyint(1) unsigned DEFAULT '1' COMMENT '申请类型:1为退款,2为退货,默认为1',
  `seller_state` tinyint(1) unsigned DEFAULT '1' COMMENT '卖家处理状态:1为待审核,2为同意,3为不同意,默认为1',
  `refund_state` tinyint(1) unsigned DEFAULT '1' COMMENT '申请状态:1为处理中,2为待管理员处理,3为已完成,默认为1',
  `return_type` tinyint(1) unsigned DEFAULT '1' COMMENT '退货类型:1为不用退货,2为需要退货,默认为1',
  `order_lock` tinyint(1) unsigned DEFAULT '1' COMMENT '订单锁定类型:1为不用锁定,2为需要锁定,默认为1',
  `goods_state` tinyint(1) unsigned DEFAULT '1' COMMENT '物流状态:1为待发货,2为待收货,3为未收到,4为已收货,默认为1',
  `add_time` int(10) unsigned NOT NULL COMMENT '添加时间',
  `seller_time` int(10) unsigned DEFAULT '0' COMMENT '卖家处理时间',
  `admin_time` int(10) unsigned DEFAULT '0' COMMENT '管理员处理时间,默认为0',
  `buyer_message` varchar(300) DEFAULT NULL COMMENT '申请原因',
  `seller_message` varchar(300) DEFAULT NULL COMMENT '卖家备注',
  `admin_message` varchar(300) DEFAULT NULL COMMENT '管理员备注',
  `express_id` tinyint(1) unsigned DEFAULT '0' COMMENT '物流公司编号',
  `invoice_no` varchar(50) DEFAULT NULL COMMENT '物流单号',
  `ship_time` int(10) unsigned DEFAULT '0' COMMENT '发货时间,默认为0',
  `delay_time` int(10) unsigned DEFAULT '0' COMMENT '收货延迟时间,默认为0',
  `receive_time` int(10) unsigned DEFAULT '0' COMMENT '收货时间,默认为0',
  `receive_message` varchar(300) DEFAULT NULL COMMENT '收货备注',
  `commis_rate` smallint(6) DEFAULT '0' COMMENT '佣金比例',
  `tesu_buyer_remark` varchar(300) DEFAULT NULL COMMENT '买家退款退货说明',
  `tesu_is_received` tinyint(1) DEFAULT '0' COMMENT '买家退款退货说明',
  `tesu_refuse_reason` varchar(300) DEFAULT NULL COMMENT '拒绝原因',
  `tesu_refuse_voucher` varchar(500) DEFAULT NULL COMMENT '拒绝收货凭证',
  `tesu_voucher` varchar(1000) DEFAULT NULL COMMENT '上传退货退款凭证',
  `tesu_wl_voucher` varchar(1000) DEFAULT NULL COMMENT '上传物流信息凭证',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`refund_id`)
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8 COMMENT='退款退货表';

/*Table structure for table `allwood_salenum` */

DROP TABLE IF EXISTS `allwood_salenum`;

CREATE TABLE `allwood_salenum` (
  `date` int(8) unsigned NOT NULL COMMENT '销售日期',
  `salenum` int(11) unsigned NOT NULL COMMENT '销量',
  `goods_id` int(11) unsigned NOT NULL COMMENT '商品ID',
  `store_id` int(11) unsigned NOT NULL COMMENT '店铺ID',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='销量统计表';

/*Table structure for table `allwood_seller` */

DROP TABLE IF EXISTS `allwood_seller`;

CREATE TABLE `allwood_seller` (
  `seller_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '卖家编号',
  `seller_name` varchar(50) NOT NULL COMMENT '卖家用户名',
  `member_id` int(10) unsigned NOT NULL COMMENT '用户编号',
  `seller_group_id` int(10) unsigned NOT NULL COMMENT '卖家组编号',
  `store_id` int(10) unsigned NOT NULL COMMENT '店铺编号',
  `is_admin` tinyint(3) unsigned NOT NULL COMMENT '是否管理员(0-不是 1-是)',
  `seller_quicklink` varchar(255) DEFAULT NULL COMMENT '卖家快捷操作',
  `last_login_time` int(10) unsigned DEFAULT NULL COMMENT '最后登录时间',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`seller_id`)
) ENGINE=InnoDB AUTO_INCREMENT=155 DEFAULT CHARSET=utf8 COMMENT='卖家用户表';

/*Table structure for table `allwood_seller_account_log` */

DROP TABLE IF EXISTS `allwood_seller_account_log`;

CREATE TABLE `allwood_seller_account_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `time` int(11) NOT NULL COMMENT '时间',
  `member_id` int(11) NOT NULL COMMENT '户主',
  `operation_type` int(11) NOT NULL COMMENT '操作类型',
  `amount` decimal(11,3) DEFAULT '0.000' COMMENT '金额',
  `remaining` decimal(11,3) DEFAULT '0.000' COMMENT '剩余金额',
  `order_sn` bigint(20) unsigned NOT NULL COMMENT '订单单号',
  `bank_ser` int(11) DEFAULT '0' COMMENT '银行流水号',
  `tesu_deleted` int(10) NOT NULL DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(300) DEFAULT NULL COMMENT '字段描述',
  `tesu_created` varchar(300) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='卖家财务表';

/*Table structure for table `allwood_seller_account_type` */

DROP TABLE IF EXISTS `allwood_seller_account_type`;

CREATE TABLE `allwood_seller_account_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `name` varchar(300) NOT NULL COMMENT '操作名称',
  `in_or_out` int(11) NOT NULL COMMENT '入账还是出账，1为入账操作，0为出账操作',
  `tesu_deleted` int(11) DEFAULT NULL COMMENT '逻辑删除字段',
  `tesu_description` varchar(300) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `allwood_seller_group` */

DROP TABLE IF EXISTS `allwood_seller_group`;

CREATE TABLE `allwood_seller_group` (
  `group_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '卖家组编号',
  `group_name` varchar(50) NOT NULL COMMENT '组名',
  `limits` text NOT NULL COMMENT '权限',
  `store_id` int(10) unsigned NOT NULL COMMENT '店铺编号',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`group_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='卖家用户组表';

/*Table structure for table `allwood_seller_log` */

DROP TABLE IF EXISTS `allwood_seller_log`;

CREATE TABLE `allwood_seller_log` (
  `log_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '日志编号',
  `log_content` varchar(50) NOT NULL COMMENT '日志内容',
  `log_time` int(10) unsigned NOT NULL COMMENT '日志时间',
  `log_seller_id` int(10) unsigned NOT NULL COMMENT '卖家编号',
  `log_seller_name` varchar(50) NOT NULL COMMENT '卖家帐号',
  `log_store_id` int(10) unsigned NOT NULL COMMENT '店铺编号',
  `log_seller_ip` varchar(50) NOT NULL COMMENT '卖家ip',
  `log_url` varchar(50) NOT NULL COMMENT '日志url',
  `log_state` tinyint(3) unsigned NOT NULL COMMENT '日志状态(0-失败 1-成功)',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4056 DEFAULT CHARSET=utf8 COMMENT='卖家日志表';

/*Table structure for table `allwood_seller_withdraw` */

DROP TABLE IF EXISTS `allwood_seller_withdraw`;

CREATE TABLE `allwood_seller_withdraw` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `withdraw_sn` bigint(20) unsigned NOT NULL COMMENT '单号',
  `member_id` int(11) NOT NULL COMMENT '用户id',
  `time` int(11) NOT NULL COMMENT '操作时间',
  `amount` decimal(11,2) NOT NULL COMMENT '操作金额',
  `tesu_deleted` int(11) DEFAULT NULL COMMENT '是否删除',
  `tesu_created` int(11) DEFAULT NULL COMMENT '创建时间',
  `tesu_description` varchar(300) DEFAULT NULL COMMENT '描述',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='提现表';

/*Table structure for table `allwood_sensitive_word` */

DROP TABLE IF EXISTS `allwood_sensitive_word`;

CREATE TABLE `allwood_sensitive_word` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `word` varchar(255) DEFAULT NULL,
  `tesu_deleted` int(10) NOT NULL DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(300) DEFAULT NULL,
  `tesu_created` varchar(300) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `allwood_seo` */

DROP TABLE IF EXISTS `allwood_seo`;

CREATE TABLE `allwood_seo` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `title` varchar(255) NOT NULL COMMENT '标题',
  `keywords` varchar(255) NOT NULL COMMENT '关键词',
  `description` text NOT NULL COMMENT '描述',
  `type` varchar(20) NOT NULL COMMENT '类型',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COMMENT='SEO信息存放表';

/*Table structure for table `allwood_setting` */

DROP TABLE IF EXISTS `allwood_setting`;

CREATE TABLE `allwood_setting` (
  `name` varchar(50) NOT NULL COMMENT '名称',
  `value` text COMMENT '值',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='系统设置表';

/*Table structure for table `allwood_sns_albumclass` */

DROP TABLE IF EXISTS `allwood_sns_albumclass`;

CREATE TABLE `allwood_sns_albumclass` (
  `ac_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '相册id',
  `ac_name` varchar(100) NOT NULL COMMENT '相册名称',
  `member_id` int(10) unsigned NOT NULL COMMENT '所属会员id',
  `ac_des` varchar(255) NOT NULL COMMENT '相册描述',
  `ac_sort` tinyint(3) unsigned NOT NULL COMMENT '排序',
  `ac_cover` varchar(255) NOT NULL COMMENT '相册封面',
  `upload_time` int(10) unsigned NOT NULL COMMENT '图片上传时间',
  `is_default` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否为买家秀相册  1为是,0为否',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`ac_id`)
) ENGINE=InnoDB AUTO_INCREMENT=412 DEFAULT CHARSET=utf8 COMMENT='相册表';

/*Table structure for table `allwood_sns_albumpic` */

DROP TABLE IF EXISTS `allwood_sns_albumpic`;

CREATE TABLE `allwood_sns_albumpic` (
  `ap_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '相册图片表id',
  `ap_name` varchar(100) NOT NULL COMMENT '图片名称',
  `ac_id` int(10) unsigned NOT NULL COMMENT '相册id',
  `ap_cover` varchar(255) NOT NULL COMMENT '图片路径',
  `ap_size` int(10) unsigned NOT NULL COMMENT '图片大小',
  `ap_spec` varchar(100) NOT NULL COMMENT '图片规格',
  `member_id` int(10) unsigned NOT NULL COMMENT '所属店铺id',
  `upload_time` int(10) unsigned NOT NULL COMMENT '图片上传时间',
  `ap_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '图片类型，0为无、1为买家秀',
  `item_id` tinyint(4) NOT NULL DEFAULT '0' COMMENT '信息ID',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`ap_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='相册图片表';

/*Table structure for table `allwood_sns_binding` */

DROP TABLE IF EXISTS `allwood_sns_binding`;

CREATE TABLE `allwood_sns_binding` (
  `snsbind_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `snsbind_memberid` int(11) NOT NULL COMMENT '会员编号',
  `snsbind_membername` varchar(100) NOT NULL COMMENT '会员名称',
  `snsbind_appsign` varchar(50) NOT NULL COMMENT '应用标志',
  `snsbind_updatetime` int(11) NOT NULL COMMENT '绑定更新时间',
  `snsbind_openid` varchar(100) NOT NULL COMMENT '应用用户编号',
  `snsbind_openinfo` text COMMENT '应用用户信息',
  `snsbind_accesstoken` varchar(100) NOT NULL COMMENT '访问第三方资源的凭证',
  `snsbind_expiresin` int(11) NOT NULL COMMENT 'accesstoken过期时间，以返回的时间的准，单位为秒，注意过期时提醒用户重新授权',
  `snsbind_refreshtoken` varchar(100) DEFAULT NULL COMMENT '刷新token',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`snsbind_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='分享应用用户绑定记录表';

/*Table structure for table `allwood_sns_comment` */

DROP TABLE IF EXISTS `allwood_sns_comment`;

CREATE TABLE `allwood_sns_comment` (
  `comment_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `comment_memberid` int(11) NOT NULL COMMENT '会员ID',
  `comment_membername` varchar(100) NOT NULL COMMENT '会员名称',
  `comment_memberavatar` varchar(100) DEFAULT NULL COMMENT '会员头像',
  `comment_originalid` int(11) NOT NULL COMMENT '原帖ID',
  `comment_originaltype` tinyint(1) NOT NULL DEFAULT '0' COMMENT '原帖类型 0表示动态信息 1表示分享商品 默认为0',
  `comment_content` varchar(500) NOT NULL COMMENT '评论内容',
  `comment_addtime` int(11) NOT NULL COMMENT '添加时间',
  `comment_ip` varchar(50) NOT NULL COMMENT '来源IP',
  `comment_state` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 0正常 1屏蔽',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`comment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='评论表';

/*Table structure for table `allwood_sns_friend` */

DROP TABLE IF EXISTS `allwood_sns_friend`;

CREATE TABLE `allwood_sns_friend` (
  `friend_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id值',
  `friend_frommid` int(11) NOT NULL COMMENT '会员id',
  `friend_frommname` varchar(100) DEFAULT NULL COMMENT '会员名称',
  `friend_frommavatar` varchar(100) DEFAULT NULL COMMENT '会员头像',
  `friend_tomid` int(11) NOT NULL COMMENT '朋友id',
  `friend_tomname` varchar(100) NOT NULL COMMENT '好友会员名称',
  `friend_tomavatar` varchar(100) DEFAULT NULL COMMENT '朋友头像',
  `friend_addtime` int(11) NOT NULL COMMENT '添加时间',
  `friend_followstate` tinyint(1) NOT NULL DEFAULT '1' COMMENT '关注状态 1为单方关注 2为双方关注',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`friend_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='好友数据表';

/*Table structure for table `allwood_sns_goods` */

DROP TABLE IF EXISTS `allwood_sns_goods`;

CREATE TABLE `allwood_sns_goods` (
  `snsgoods_goodsid` int(11) NOT NULL COMMENT '商品ID',
  `snsgoods_goodsname` varchar(100) NOT NULL COMMENT '商品名称',
  `snsgoods_goodsimage` varchar(100) DEFAULT NULL COMMENT '商品图片',
  `snsgoods_goodsprice` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '商品价格',
  `snsgoods_storeid` int(11) NOT NULL COMMENT '店铺ID',
  `snsgoods_storename` varchar(100) NOT NULL COMMENT '店铺名称',
  `snsgoods_addtime` int(11) NOT NULL COMMENT '添加时间',
  `snsgoods_likenum` int(11) NOT NULL DEFAULT '0' COMMENT '喜欢数',
  `snsgoods_likemember` text COMMENT '喜欢过的会员ID，用逗号分隔',
  `snsgoods_sharenum` int(11) NOT NULL DEFAULT '0' COMMENT '分享数',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  UNIQUE KEY `snsgoods_goodsid` (`snsgoods_goodsid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='SNS商品表';

/*Table structure for table `allwood_sns_membertag` */

DROP TABLE IF EXISTS `allwood_sns_membertag`;

CREATE TABLE `allwood_sns_membertag` (
  `mtag_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '会员标签id',
  `mtag_name` varchar(20) NOT NULL COMMENT '会员标签名称',
  `mtag_sort` tinyint(4) NOT NULL DEFAULT '0' COMMENT '会员标签排序',
  `mtag_recommend` tinyint(4) NOT NULL DEFAULT '0' COMMENT '标签推荐 0未推荐（默认），1为已推荐',
  `mtag_desc` varchar(50) NOT NULL COMMENT '标签描述',
  `mtag_img` varchar(50) DEFAULT NULL COMMENT '标签图片',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`mtag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='会员标签表';

/*Table structure for table `allwood_sns_mtagmember` */

DROP TABLE IF EXISTS `allwood_sns_mtagmember`;

CREATE TABLE `allwood_sns_mtagmember` (
  `mtag_id` int(11) NOT NULL COMMENT '会员标签表id',
  `member_id` int(11) NOT NULL COMMENT '会员id',
  `recommend` tinyint(4) NOT NULL DEFAULT '0' COMMENT '推荐，默认为0',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`mtag_id`,`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='会员标签会员对照表';

/*Table structure for table `allwood_sns_setting` */

DROP TABLE IF EXISTS `allwood_sns_setting`;

CREATE TABLE `allwood_sns_setting` (
  `member_id` int(11) NOT NULL COMMENT '会员id',
  `setting_skin` varchar(50) DEFAULT NULL COMMENT '皮肤',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='个人中心设置表';

/*Table structure for table `allwood_sns_sharegoods` */

DROP TABLE IF EXISTS `allwood_sns_sharegoods`;

CREATE TABLE `allwood_sns_sharegoods` (
  `share_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `share_goodsid` int(11) NOT NULL COMMENT '商品ID',
  `share_memberid` int(11) NOT NULL COMMENT '所属会员ID',
  `share_membername` varchar(100) NOT NULL COMMENT '会员名称',
  `share_content` varchar(500) DEFAULT NULL COMMENT '描述内容',
  `share_addtime` int(11) NOT NULL COMMENT '分享操作时间',
  `share_likeaddtime` int(11) NOT NULL DEFAULT '0' COMMENT '喜欢操作时间',
  `share_privacy` tinyint(1) NOT NULL DEFAULT '0' COMMENT '隐私可见度 0所有人可见 1好友可见 2仅自己可见',
  `share_commentcount` int(11) NOT NULL DEFAULT '0' COMMENT '评论数',
  `share_isshare` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否分享 0为未分享 1为分享',
  `share_islike` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否喜欢 0为未喜欢 1为喜欢',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`share_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COMMENT='共享商品表';

/*Table structure for table `allwood_sns_sharestore` */

DROP TABLE IF EXISTS `allwood_sns_sharestore`;

CREATE TABLE `allwood_sns_sharestore` (
  `share_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `share_storeid` int(11) NOT NULL COMMENT '店铺编号',
  `share_storename` varchar(100) NOT NULL COMMENT '店铺名称',
  `share_memberid` int(11) NOT NULL COMMENT '所属会员ID',
  `share_membername` varchar(100) NOT NULL COMMENT '所属会员名称',
  `share_content` varchar(500) DEFAULT NULL COMMENT '描述内容',
  `share_addtime` int(11) NOT NULL COMMENT '添加时间',
  `share_privacy` tinyint(1) NOT NULL DEFAULT '0' COMMENT '隐私可见度 0所有人可见 1好友可见 2仅自己可见',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`share_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='共享店铺表';

/*Table structure for table `allwood_sns_tracelog` */

DROP TABLE IF EXISTS `allwood_sns_tracelog`;

CREATE TABLE `allwood_sns_tracelog` (
  `trace_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `trace_originalid` int(11) NOT NULL DEFAULT '0' COMMENT '原动态ID 默认为0',
  `trace_originalmemberid` int(11) NOT NULL DEFAULT '0' COMMENT '原帖会员编号',
  `trace_originalstate` tinyint(1) NOT NULL DEFAULT '0' COMMENT '原帖的删除状态 0为正常 1为删除',
  `trace_memberid` int(11) NOT NULL COMMENT '会员ID',
  `trace_membername` varchar(100) NOT NULL COMMENT '会员名称',
  `trace_memberavatar` varchar(100) DEFAULT NULL COMMENT '会员头像',
  `trace_title` varchar(500) DEFAULT NULL COMMENT '动态标题',
  `trace_content` text NOT NULL COMMENT '动态内容',
  `trace_addtime` int(11) NOT NULL COMMENT '添加时间',
  `trace_state` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态  0正常 1为禁止显示 默认为0',
  `trace_privacy` tinyint(1) NOT NULL DEFAULT '0' COMMENT '隐私可见度 0所有人可见 1好友可见 2仅自己可见',
  `trace_commentcount` int(11) NOT NULL DEFAULT '0' COMMENT '评论数',
  `trace_copycount` int(11) NOT NULL DEFAULT '0' COMMENT '转发数',
  `trace_orgcommentcount` int(11) NOT NULL DEFAULT '0' COMMENT '原帖评论次数',
  `trace_orgcopycount` int(11) NOT NULL DEFAULT '0' COMMENT '原帖转帖次数',
  `trace_from` tinyint(4) DEFAULT '1' COMMENT '来源 1=shop 2=storetracelog 3=microshop 4=cms 5=circle',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`trace_id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8 COMMENT='动态信息表';

/*Table structure for table `allwood_sns_visitor` */

DROP TABLE IF EXISTS `allwood_sns_visitor`;

CREATE TABLE `allwood_sns_visitor` (
  `v_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `v_mid` int(11) NOT NULL COMMENT '访客会员ID',
  `v_mname` varchar(100) NOT NULL COMMENT '访客会员名称',
  `v_mavatar` varchar(100) DEFAULT NULL COMMENT '访客会员头像',
  `v_ownermid` int(11) NOT NULL COMMENT '主人会员ID',
  `v_ownermname` varchar(100) NOT NULL COMMENT '主人会员名称',
  `v_ownermavatar` varchar(100) DEFAULT NULL COMMENT '主人会员头像',
  `v_addtime` int(11) NOT NULL COMMENT '访问时间',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`v_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='sns访客表';

/*Table structure for table `allwood_spec` */

DROP TABLE IF EXISTS `allwood_spec`;

CREATE TABLE `allwood_spec` (
  `sp_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '规格id',
  `sp_name` varchar(100) NOT NULL COMMENT '规格名称',
  `sp_sort` tinyint(1) unsigned NOT NULL COMMENT '排序',
  `class_id` int(10) unsigned DEFAULT '0' COMMENT '所属分类id',
  `class_name` varchar(100) DEFAULT NULL COMMENT '所属分类名称',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`sp_id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8 COMMENT='商品规格表';

/*Table structure for table `allwood_spec_value` */

DROP TABLE IF EXISTS `allwood_spec_value`;

CREATE TABLE `allwood_spec_value` (
  `sp_value_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '规格值id',
  `sp_value_name` varchar(100) NOT NULL COMMENT '规格值名称',
  `sp_id` int(10) unsigned NOT NULL COMMENT '所属规格id',
  `gc_id` int(10) unsigned NOT NULL COMMENT '分类id',
  `store_id` int(10) unsigned NOT NULL COMMENT '店铺id',
  `sp_value_color` varchar(10) DEFAULT NULL COMMENT '规格颜色',
  `sp_value_sort` tinyint(1) unsigned NOT NULL COMMENT '排序',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`sp_value_id`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1358 DEFAULT CHARSET=utf8 COMMENT='商品规格值表';

/*Table structure for table `allwood_stat_member` */

DROP TABLE IF EXISTS `allwood_stat_member`;

CREATE TABLE `allwood_stat_member` (
  `statm_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `statm_memberid` int(11) NOT NULL COMMENT '会员ID',
  `statm_membername` varchar(100) NOT NULL COMMENT '会员名称',
  `statm_time` int(11) NOT NULL COMMENT '统计时间，当前按照最小时间单位为天',
  `statm_ordernum` int(11) NOT NULL DEFAULT '0' COMMENT '下单量',
  `statm_orderamount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '下单金额',
  `statm_goodsnum` int(11) NOT NULL DEFAULT '0' COMMENT '下单商品件数',
  `statm_predincrease` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '预存款增加额',
  `statm_predreduce` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '预存款减少额',
  `statm_pointsincrease` int(11) NOT NULL DEFAULT '0' COMMENT '积分增加额',
  `statm_pointsreduce` int(11) NOT NULL DEFAULT '0' COMMENT '积分减少额',
  `statm_updatetime` int(11) NOT NULL COMMENT '记录更新时间',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`statm_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='会员相关数据统计';

/*Table structure for table `allwood_store` */

DROP TABLE IF EXISTS `allwood_store`;

CREATE TABLE `allwood_store` (
  `store_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '店铺索引id',
  `store_name` varchar(50) NOT NULL COMMENT '店铺名称',
  `store_auth` tinyint(1) DEFAULT '0' COMMENT '店铺认证',
  `name_auth` tinyint(1) DEFAULT '0' COMMENT '店主认证',
  `grade_id` int(11) NOT NULL COMMENT '店铺等级',
  `member_id` int(11) NOT NULL COMMENT '会员id',
  `member_name` varchar(50) NOT NULL COMMENT '会员名称',
  `seller_name` varchar(50) DEFAULT NULL COMMENT '店主卖家用户名',
  `store_owner_card` varchar(50) NOT NULL COMMENT '身份证',
  `sc_id` int(11) NOT NULL COMMENT '店铺分类',
  `store_company_name` varchar(50) DEFAULT NULL COMMENT '店铺公司名称',
  `area_id` int(11) NOT NULL COMMENT '地区id',
  `area_info` varchar(100) NOT NULL COMMENT '地区内容，冗余数据',
  `store_address` varchar(100) NOT NULL COMMENT '详细地区',
  `store_zip` varchar(10) NOT NULL COMMENT '邮政编码',
  `store_tel` varchar(50) NOT NULL COMMENT '电话号码',
  `store_image` varchar(100) DEFAULT NULL COMMENT '证件上传',
  `store_image1` varchar(100) DEFAULT NULL COMMENT '执照上传',
  `store_state` tinyint(1) NOT NULL DEFAULT '2' COMMENT '店铺状态，0关闭，1开启，2审核中',
  `store_close_info` varchar(255) DEFAULT NULL COMMENT '店铺关闭原因',
  `store_sort` int(11) NOT NULL DEFAULT '0' COMMENT '店铺排序',
  `store_time` varchar(10) NOT NULL COMMENT '店铺时间',
  `store_end_time` varchar(10) DEFAULT NULL COMMENT '店铺关闭时间',
  `store_label` varchar(255) DEFAULT NULL COMMENT '店铺logo',
  `store_banner` varchar(255) DEFAULT NULL COMMENT '店铺横幅',
  `store_keywords` varchar(255) NOT NULL DEFAULT '' COMMENT '店铺seo关键字',
  `store_description` varchar(255) NOT NULL DEFAULT '' COMMENT '店铺seo描述',
  `store_qq` varchar(50) DEFAULT NULL COMMENT 'QQ',
  `store_ww` varchar(50) DEFAULT NULL COMMENT '阿里旺旺',
  `description` text COMMENT '店铺简介',
  `store_zy` text COMMENT '主营商品',
  `store_domain` varchar(50) DEFAULT NULL COMMENT '店铺二级域名',
  `store_domain_times` tinyint(1) unsigned DEFAULT '0' COMMENT '二级域名修改次数',
  `store_recommend` tinyint(1) NOT NULL DEFAULT '0' COMMENT '推荐，0为否，1为是，默认为0',
  `store_theme` varchar(50) NOT NULL DEFAULT 'default' COMMENT '店铺当前主题',
  `store_credit` int(10) NOT NULL DEFAULT '0' COMMENT '店铺信用',
  `praise_rate` float NOT NULL DEFAULT '0' COMMENT '店铺好评率',
  `store_desccredit` float NOT NULL DEFAULT '0' COMMENT '描述相符度分数',
  `store_servicecredit` float NOT NULL DEFAULT '0' COMMENT '服务态度分数',
  `store_deliverycredit` float NOT NULL DEFAULT '0' COMMENT '发货速度分数',
  `store_collect` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '店铺收藏数量',
  `store_slide` text COMMENT '店铺幻灯片',
  `store_slide_url` text COMMENT '店铺幻灯片链接',
  `store_stamp` varchar(200) DEFAULT NULL COMMENT '店铺印章',
  `store_printdesc` varchar(500) DEFAULT NULL COMMENT '打印订单页面下方说明文字',
  `store_sales` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '店铺销量',
  `store_presales` text COMMENT '售前客服',
  `store_aftersales` text COMMENT '售后客服',
  `store_workingtime` varchar(100) DEFAULT NULL COMMENT '工作时间',
  `store_free_price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '超出该金额免运费，大于0才表示该值有效',
  `store_storage_alarm` tinyint(3) unsigned NOT NULL DEFAULT '10' COMMENT '库存警报',
  `deposit_avaiable` decimal(11,2) DEFAULT '0.00' COMMENT '可用账户',
  `deposit_freeze` decimal(11,2) DEFAULT '0.00' COMMENT '保证金账户',
  `is_modify_name` tinyint(1) DEFAULT '0' COMMENT '是否修改过店铺名',
  `is_discount` tinyint(1) DEFAULT '0' COMMENT '0：不贴息 1：贴息',
  `ser_charge` decimal(4,2) DEFAULT '0.00' COMMENT '服务费',
  `downpayment` decimal(4,2) DEFAULT '0.00' COMMENT '首期款比例',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`store_id`),
  KEY `store_name` (`store_name`),
  KEY `sc_id` (`sc_id`),
  KEY `area_id` (`area_id`),
  KEY `store_state` (`store_state`)
) ENGINE=InnoDB AUTO_INCREMENT=154 DEFAULT CHARSET=utf8 COMMENT='店铺数据表';

/*Table structure for table `allwood_store_bind_class` */

DROP TABLE IF EXISTS `allwood_store_bind_class`;

CREATE TABLE `allwood_store_bind_class` (
  `bid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int(11) unsigned DEFAULT '0' COMMENT '店铺ID',
  `commis_rate` tinyint(4) unsigned DEFAULT '0' COMMENT '佣金比例',
  `class_1` mediumint(9) unsigned DEFAULT '0' COMMENT '一级分类',
  `class_2` mediumint(9) unsigned DEFAULT '0' COMMENT '二级分类',
  `class_3` mediumint(9) unsigned DEFAULT '0' COMMENT '三级分类',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`bid`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15611 DEFAULT CHARSET=utf8 COMMENT='店铺可发布商品类目表';

/*Table structure for table `allwood_store_class` */

DROP TABLE IF EXISTS `allwood_store_class`;

CREATE TABLE `allwood_store_class` (
  `sc_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '索引ID',
  `sc_name` varchar(100) NOT NULL COMMENT '分类名称',
  `sc_parent_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父ID',
  `sc_sort` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`sc_id`),
  KEY `sc_parent_id` (`sc_parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8 COMMENT='店铺分类表';

/*Table structure for table `allwood_store_cost` */

DROP TABLE IF EXISTS `allwood_store_cost`;

CREATE TABLE `allwood_store_cost` (
  `cost_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '费用编号',
  `cost_store_id` int(10) unsigned NOT NULL COMMENT '店铺编号',
  `cost_seller_id` int(10) unsigned NOT NULL COMMENT '卖家编号',
  `cost_price` int(10) unsigned NOT NULL COMMENT '价格',
  `cost_remark` varchar(255) NOT NULL COMMENT '费用备注',
  `cost_state` tinyint(3) unsigned NOT NULL COMMENT '费用状态(0-未结算 1-已结算)',
  `cost_time` int(10) unsigned NOT NULL COMMENT '费用发生时间',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`cost_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COMMENT='店铺费用表';

/*Table structure for table `allwood_store_extend` */

DROP TABLE IF EXISTS `allwood_store_extend`;

CREATE TABLE `allwood_store_extend` (
  `store_id` mediumint(8) unsigned NOT NULL COMMENT '店铺ID',
  `express` text COMMENT '快递公司ID的组合',
  `navigation_autocreate` int(11) DEFAULT '0' COMMENT '分类id',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='店铺信息扩展表';

/*Table structure for table `allwood_store_fund` */

DROP TABLE IF EXISTS `allwood_store_fund`;

CREATE TABLE `allwood_store_fund` (
  `store_fund_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增编号',
  `store_fund_sn` bigint(20) unsigned NOT NULL COMMENT '记录唯一标示',
  `store_fund_member_id` int(11) NOT NULL COMMENT '会员编号',
  `store_fund_member_name` varchar(50) NOT NULL COMMENT '会员名称',
  `store_fund_amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '充值金额',
  `store_fund_payment_code` varchar(20) DEFAULT '' COMMENT '支付方式',
  `store_fund_payment_name` varchar(15) DEFAULT '' COMMENT '支付方式',
  `store_fund_trade_sn` varchar(50) DEFAULT '' COMMENT '第三方支付接口交易号',
  `store_fund_add_time` int(11) NOT NULL COMMENT '添加时间',
  `store_fund_payment_state` enum('0','1') NOT NULL DEFAULT '0' COMMENT '支付状态 0未支付1支付',
  `store_fund_payment_time` int(11) NOT NULL DEFAULT '0' COMMENT '支付时间',
  `store_fund_admin` varchar(30) DEFAULT '' COMMENT '管理员名',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`store_fund_id`)
) ENGINE=InnoDB AUTO_INCREMENT=235 DEFAULT CHARSET=utf8 COMMENT='开店缴费充值表';

/*Table structure for table `allwood_store_fund_log` */

DROP TABLE IF EXISTS `allwood_store_fund_log`;

CREATE TABLE `allwood_store_fund_log` (
  `lg_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增编号',
  `lg_member_id` int(11) NOT NULL COMMENT '会员编号',
  `lg_member_name` varchar(50) NOT NULL COMMENT '会员名称',
  `lg_admin_name` varchar(50) DEFAULT NULL COMMENT '管理员名称',
  `lg_type` varchar(15) NOT NULL DEFAULT '' COMMENT 'order_pay下单支付预存款,order_freeze下单冻结预存款,order_cancel取消订单解冻预存款,order_comb_pay下单支付被冻结的预存款,recharge充值,cash_apply申请提现冻结预存款,cash_pay提现成功,cash_del取消提现申请，解冻预存款,refund退款',
  `lg_av_amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '可用金额变更0表示未变更',
  `lg_freeze_amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '冻结金额变更0表示未变更',
  `lg_add_time` int(11) NOT NULL COMMENT '添加时间',
  `lg_desc` varchar(150) DEFAULT NULL COMMENT '描述',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`lg_id`)
) ENGINE=InnoDB AUTO_INCREMENT=174 DEFAULT CHARSET=utf8 COMMENT='开店缴费日志表';

/*Table structure for table `allwood_store_goods_class` */

DROP TABLE IF EXISTS `allwood_store_goods_class`;

CREATE TABLE `allwood_store_goods_class` (
  `stc_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '索引ID',
  `stc_name` varchar(50) NOT NULL COMMENT '店铺商品分类名称',
  `stc_parent_id` int(11) NOT NULL COMMENT '父级id',
  `stc_state` tinyint(1) NOT NULL DEFAULT '0' COMMENT '店铺商品分类状态',
  `store_id` int(11) NOT NULL DEFAULT '0' COMMENT '店铺id',
  `stc_sort` int(11) NOT NULL DEFAULT '0' COMMENT '商品分类排序',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`stc_id`),
  KEY `stc_parent_id` (`stc_parent_id`,`stc_sort`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='店铺商品分类表';

/*Table structure for table `allwood_store_grade` */

DROP TABLE IF EXISTS `allwood_store_grade`;

CREATE TABLE `allwood_store_grade` (
  `sg_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '索引ID',
  `sg_name` char(50) DEFAULT NULL COMMENT '等级名称',
  `sg_goods_limit` mediumint(10) unsigned NOT NULL DEFAULT '0' COMMENT '允许发布的商品数量',
  `sg_album_limit` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '允许上传图片数量',
  `sg_space_limit` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上传空间大小，单位MB',
  `sg_template_number` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '选择店铺模板套数',
  `sg_template` varchar(255) DEFAULT NULL COMMENT '模板内容',
  `sg_price` varchar(100) DEFAULT NULL COMMENT '费用',
  `sg_confirm` tinyint(1) NOT NULL DEFAULT '1' COMMENT '审核，0为否，1为是，默认为1',
  `sg_description` text COMMENT '申请说明',
  `sg_function` varchar(255) DEFAULT NULL COMMENT '附加功能',
  `sg_sort` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '级别，数目越大级别越高',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`sg_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='店铺等级表';

/*Table structure for table `allwood_store_joinin` */

DROP TABLE IF EXISTS `allwood_store_joinin`;

CREATE TABLE `allwood_store_joinin` (
  `member_id` int(10) unsigned NOT NULL COMMENT '用户编号',
  `member_name` varchar(50) DEFAULT NULL COMMENT '店主用户名',
  `company_name` varchar(50) DEFAULT NULL COMMENT '公司名称',
  `company_address` varchar(50) DEFAULT NULL COMMENT '公司地址',
  `company_address_detail` varchar(50) DEFAULT NULL COMMENT '公司详细地址',
  `company_phone` varchar(20) DEFAULT NULL COMMENT '公司电话',
  `company_employee_count` int(10) unsigned DEFAULT NULL COMMENT '员工总数',
  `company_registered_capital` int(10) unsigned DEFAULT NULL COMMENT '注册资金',
  `contacts_name` varchar(50) DEFAULT NULL COMMENT '联系人姓名',
  `contacts_phone` varchar(20) DEFAULT NULL COMMENT '联系人电话',
  `contacts_email` varchar(50) DEFAULT NULL COMMENT '联系人邮箱',
  `business_licence_number` varchar(50) DEFAULT NULL COMMENT '营业执照号',
  `business_licence_address` varchar(50) DEFAULT NULL COMMENT '营业执所在地',
  `business_licence_start` date DEFAULT NULL COMMENT '营业执照有效期开始',
  `business_licence_end` date DEFAULT NULL COMMENT '营业执照有效期结束',
  `business_sphere` varchar(1000) DEFAULT NULL COMMENT '法定经营范围',
  `business_licence_number_electronic` varchar(50) DEFAULT NULL COMMENT '营业执照电子版',
  `organization_code` varchar(50) DEFAULT NULL COMMENT '组织机构代码',
  `organization_code_electronic` varchar(50) DEFAULT NULL COMMENT '组织机构代码电子版',
  `general_taxpayer` varchar(50) DEFAULT NULL COMMENT '一般纳税人证明',
  `bank_account_name` varchar(50) DEFAULT NULL COMMENT '银行开户名',
  `bank_account_number` varchar(50) DEFAULT NULL COMMENT '公司银行账号',
  `bank_name` varchar(50) DEFAULT NULL COMMENT '开户银行支行名称',
  `bank_code` varchar(50) DEFAULT NULL COMMENT '支行联行号',
  `bank_address` varchar(50) DEFAULT NULL COMMENT '开户银行所在地',
  `bank_licence_electronic` varchar(50) DEFAULT NULL COMMENT '开户银行许可证电子版',
  `is_settlement_account` tinyint(1) DEFAULT NULL COMMENT '开户行账号是否为结算账号 1-开户行就是结算账号 2-独立的计算账号',
  `settlement_bank_account_name` varchar(50) DEFAULT NULL COMMENT '结算银行开户名',
  `settlement_bank_account_number` varchar(50) DEFAULT NULL COMMENT '结算公司银行账号',
  `settlement_bank_name` varchar(50) DEFAULT NULL COMMENT '结算开户银行支行名称',
  `settlement_bank_code` varchar(50) DEFAULT NULL COMMENT '结算支行联行号',
  `settlement_bank_address` varchar(50) DEFAULT NULL COMMENT '结算开户银行所在地',
  `tax_registration_certificate` varchar(50) DEFAULT NULL COMMENT '税务登记证号',
  `taxpayer_id` varchar(50) DEFAULT NULL COMMENT '纳税人识别号',
  `tax_registration_certificate_electronic` varchar(50) DEFAULT NULL COMMENT '税务登记证号电子版',
  `seller_name` varchar(50) DEFAULT NULL COMMENT '卖家帐号',
  `store_name` varchar(50) DEFAULT NULL COMMENT '店铺名称',
  `store_class_ids` varchar(1000) DEFAULT NULL COMMENT '店铺分类编号集合',
  `store_class_names` varchar(1000) DEFAULT NULL COMMENT '店铺分类名称集合',
  `joinin_state` varchar(50) DEFAULT NULL COMMENT '申请状态 10-已提交申请 11-缴费完成  20-审核成功 30-审核失败 31-缴费审核失败 40-审核通过开店',
  `joinin_message` varchar(200) DEFAULT NULL COMMENT '管理员审核信息',
  `sg_name` varchar(50) DEFAULT NULL COMMENT '店铺等级名称',
  `sg_id` int(10) unsigned DEFAULT NULL COMMENT '店铺等级编号',
  `sc_name` varchar(50) DEFAULT NULL COMMENT '店铺分类名称',
  `sc_id` int(10) unsigned DEFAULT NULL COMMENT '店铺分类编号',
  `store_class_commis_rates` varchar(200) DEFAULT NULL COMMENT '分类佣金比例',
  `paying_money_certificate` varchar(50) DEFAULT NULL COMMENT '付款凭证',
  `paying_money_certificate_explain` varchar(200) DEFAULT NULL COMMENT '付款凭证说明',
  `representive` varchar(50) DEFAULT NULL COMMENT '法人姓名',
  `representive_id` varchar(50) DEFAULT NULL COMMENT '法人ID',
  `representive_id_start` date DEFAULT NULL COMMENT '法人证件有效期start',
  `representive_id_end` date DEFAULT NULL COMMENT '法人证件有效期end',
  `representive_id_front_electronic` varchar(50) DEFAULT NULL COMMENT '法人身份证正面照',
  `representive_id_back_electronic` varchar(50) DEFAULT NULL COMMENT '法人身份证反面照',
  `province_id` int(11) DEFAULT '1' COMMENT '省ID',
  `city_id` int(11) DEFAULT '1' COMMENT '市ID',
  `area_id2` int(11) DEFAULT '1' COMMENT '区ID',
  `is_discount` tinyint(1) DEFAULT '0' COMMENT '0：不贴息 1：贴息',
  `ser_charge` decimal(4,2) DEFAULT '0.00' COMMENT '服务费',
  `downpayment` decimal(4,2) DEFAULT '0.00' COMMENT '首期款比例',
  `major_business` varchar(300) DEFAULT NULL COMMENT '主营类目',
  `hongmu_business` int(3) DEFAULT '0' COMMENT '经营材质是否是红木',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='店铺入住表';

/*Table structure for table `allwood_store_major_business` */

DROP TABLE IF EXISTS `allwood_store_major_business`;

CREATE TABLE `allwood_store_major_business` (
  `sc_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '索引ID',
  `sc_name` varchar(100) NOT NULL COMMENT '分类名称',
  `tesu_deleted` int(10) NOT NULL DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(300) DEFAULT NULL,
  `tesu_created` varchar(300) DEFAULT NULL,
  PRIMARY KEY (`sc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='新店入驻主营类目表';

/*Table structure for table `allwood_store_navigation` */

DROP TABLE IF EXISTS `allwood_store_navigation`;

CREATE TABLE `allwood_store_navigation` (
  `sn_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '导航ID',
  `sn_title` varchar(50) NOT NULL COMMENT '导航名称',
  `sn_store_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '卖家店铺ID',
  `sn_content` text COMMENT '导航内容',
  `sn_sort` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '导航排序',
  `sn_if_show` tinyint(1) NOT NULL DEFAULT '0' COMMENT '导航是否显示',
  `sn_add_time` int(10) NOT NULL COMMENT '导航',
  `sn_url` varchar(255) DEFAULT NULL COMMENT '店铺导航的外链URL',
  `sn_new_open` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '店铺导航外链是否在新窗口打开：0不开新窗口1开新窗口，默认是0',
  `sn_gc_id` int(11) DEFAULT '0' COMMENT '分类id',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`sn_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COMMENT='卖家店铺导航信息表';

/*Table structure for table `allwood_store_plate` */

DROP TABLE IF EXISTS `allwood_store_plate`;

CREATE TABLE `allwood_store_plate` (
  `plate_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '关联板式id',
  `plate_name` varchar(10) NOT NULL COMMENT '关联板式名称',
  `plate_position` tinyint(3) unsigned NOT NULL COMMENT '关联板式位置 1顶部，0底部',
  `plate_content` text NOT NULL COMMENT '关联板式内容',
  `store_id` int(10) unsigned NOT NULL COMMENT '所属店铺id',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`plate_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='关联板式表';

/*Table structure for table `allwood_store_sns_comment` */

DROP TABLE IF EXISTS `allwood_store_sns_comment`;

CREATE TABLE `allwood_store_sns_comment` (
  `scomm_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '店铺动态评论id',
  `strace_id` int(11) NOT NULL COMMENT '店铺动态id',
  `scomm_content` varchar(150) DEFAULT NULL COMMENT '评论内容',
  `scomm_memberid` int(11) DEFAULT NULL COMMENT '会员id',
  `scomm_membername` varchar(45) DEFAULT NULL COMMENT '会员名称',
  `scomm_memberavatar` varchar(50) DEFAULT NULL COMMENT '会员头像',
  `scomm_time` varchar(11) DEFAULT NULL COMMENT '评论时间',
  `scomm_state` tinyint(1) NOT NULL DEFAULT '1' COMMENT '评论状态 1正常，0屏蔽',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`scomm_id`),
  UNIQUE KEY `scomm_id` (`scomm_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='店铺动态评论表';

/*Table structure for table `allwood_store_sns_setting` */

DROP TABLE IF EXISTS `allwood_store_sns_setting`;

CREATE TABLE `allwood_store_sns_setting` (
  `sauto_storeid` int(11) NOT NULL COMMENT '店铺id',
  `sauto_new` tinyint(4) NOT NULL DEFAULT '1' COMMENT '新品,0为关闭/1为开启',
  `sauto_newtitle` varchar(150) NOT NULL COMMENT '新品内容',
  `sauto_coupon` tinyint(4) NOT NULL DEFAULT '1' COMMENT '优惠券,0为关闭/1为开启',
  `sauto_coupontitle` varchar(150) NOT NULL COMMENT '优惠券内容',
  `sauto_xianshi` tinyint(4) NOT NULL DEFAULT '1' COMMENT '限时折扣,0为关闭/1为开启',
  `sauto_xianshititle` varchar(150) NOT NULL COMMENT '限时折扣内容',
  `sauto_mansong` tinyint(4) NOT NULL DEFAULT '1' COMMENT '满即送,0为关闭/1为开启',
  `sauto_mansongtitle` varchar(150) NOT NULL COMMENT '满即送内容',
  `sauto_bundling` tinyint(4) NOT NULL DEFAULT '1' COMMENT '组合销售,0为关闭/1为开启',
  `sauto_bundlingtitle` varchar(150) NOT NULL COMMENT '组合销售内容',
  `sauto_groupbuy` tinyint(4) NOT NULL DEFAULT '1' COMMENT '团购,0为关闭/1为开启',
  `sauto_groupbuytitle` varchar(150) NOT NULL COMMENT '团购内容',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`sauto_storeid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='店铺自动发布动态设置表';

/*Table structure for table `allwood_store_sns_tracelog` */

DROP TABLE IF EXISTS `allwood_store_sns_tracelog`;

CREATE TABLE `allwood_store_sns_tracelog` (
  `strace_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '店铺动态id',
  `strace_storeid` int(11) DEFAULT NULL COMMENT '店铺id',
  `strace_storename` varchar(100) DEFAULT NULL COMMENT '店铺名称',
  `strace_storelogo` varchar(255) NOT NULL COMMENT '店标',
  `strace_title` varchar(150) DEFAULT NULL COMMENT '动态标题',
  `strace_content` text COMMENT '发表内容',
  `strace_time` varchar(11) DEFAULT NULL COMMENT '发表时间',
  `strace_cool` int(11) DEFAULT '0' COMMENT '赞数量',
  `strace_spread` int(11) DEFAULT '0' COMMENT '转播数量',
  `strace_comment` int(11) DEFAULT '0' COMMENT '评论数量',
  `strace_type` tinyint(4) DEFAULT '1' COMMENT '1=relay,2=normal,3=new,4=coupon,5=xianshi,6=mansong,7=bundling,8=groupbuy,9=recommend,10=hotsell',
  `strace_goodsdata` varchar(1000) DEFAULT NULL COMMENT '商品信息',
  `strace_state` tinyint(1) NOT NULL DEFAULT '1' COMMENT '动态状态 1正常，0屏蔽',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`strace_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='店铺动态表';

/*Table structure for table `allwood_store_terminal_inf` */

DROP TABLE IF EXISTS `allwood_store_terminal_inf`;

CREATE TABLE `allwood_store_terminal_inf` (
  `terminal_usr_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `usr_name` varchar(48) DEFAULT NULL COMMENT '登录账户',
  `usr_pass` char(32) DEFAULT NULL COMMENT '密码',
  `terminal_id` varchar(16) DEFAULT NULL COMMENT '设备id',
  `store_id` bigint(20) DEFAULT NULL COMMENT '店铺id',
  `possessor` varchar(16) DEFAULT NULL COMMENT '持有人',
  `physical_store` varchar(48) DEFAULT NULL COMMENT '实体店名称',
  `add_time` int(13) DEFAULT NULL COMMENT '添加时间',
  `state` tinyint(4) DEFAULT NULL COMMENT '状态：0=可用,1=禁用',
  `login_ip` varchar(16) DEFAULT NULL COMMENT '最后登录ip',
  `login_time` datetime DEFAULT NULL COMMENT '最后登录时间',
  `boot_update_flag` tinyint(4) DEFAULT NULL COMMENT '启动图片是否更新，0：未更新，1：已更新',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`terminal_usr_id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8;

/*Table structure for table `allwood_store_watermark` */

DROP TABLE IF EXISTS `allwood_store_watermark`;

CREATE TABLE `allwood_store_watermark` (
  `wm_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '水印id',
  `jpeg_quality` int(3) NOT NULL DEFAULT '90' COMMENT 'jpeg图片质量',
  `wm_image_name` varchar(255) DEFAULT NULL COMMENT '水印图片的路径以及文件名',
  `wm_image_pos` tinyint(1) NOT NULL DEFAULT '1' COMMENT '水印图片放置的位置',
  `wm_image_transition` int(3) NOT NULL DEFAULT '20' COMMENT '水印图片与原图片的融合度 ',
  `wm_text` text COMMENT '水印文字',
  `wm_text_size` int(3) NOT NULL DEFAULT '20' COMMENT '水印文字大小',
  `wm_text_angle` tinyint(1) NOT NULL DEFAULT '4' COMMENT '水印文字角度',
  `wm_text_pos` tinyint(1) NOT NULL DEFAULT '3' COMMENT '水印文字放置位置',
  `wm_text_font` varchar(50) DEFAULT NULL COMMENT '水印文字的字体',
  `wm_text_color` varchar(7) NOT NULL DEFAULT '#CCCCCC' COMMENT '水印字体的颜色值',
  `wm_is_open` tinyint(1) NOT NULL DEFAULT '0' COMMENT '水印是否开启 0关闭 1开启',
  `store_id` int(11) DEFAULT NULL COMMENT '店铺id',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`wm_id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8 COMMENT='店铺水印图片表';

/*Table structure for table `allwood_tender` */

DROP TABLE IF EXISTS `allwood_tender`;

CREATE TABLE `allwood_tender` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `date` char(20) NOT NULL COMMENT '发标日期',
  `count` int(10) unsigned NOT NULL COMMENT '当日发标数量',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='记录每日发标的期数';

/*Table structure for table `allwood_terminal_boot_image` */

DROP TABLE IF EXISTS `allwood_terminal_boot_image`;

CREATE TABLE `allwood_terminal_boot_image` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `boot_image` varchar(48) DEFAULT NULL COMMENT '启动图片',
  `add_time` datetime DEFAULT NULL COMMENT '添加日期',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

/*Table structure for table `allwood_theme_nav` */

DROP TABLE IF EXISTS `allwood_theme_nav`;

CREATE TABLE `allwood_theme_nav` (
  `type_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '类型id',
  `type_name` varchar(100) NOT NULL COMMENT '类型名称',
  `type_sort` tinyint(1) unsigned NOT NULL COMMENT '排序',
  `class_id` int(10) unsigned DEFAULT '0' COMMENT '所属分类id',
  `class_name` varchar(100) NOT NULL COMMENT '所属分类名称',
  `tesu_type` tinyint(1) DEFAULT '0' COMMENT '单独专题的类型',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='首页专题添加分类';

/*Table structure for table `allwood_transport` */

DROP TABLE IF EXISTS `allwood_transport`;

CREATE TABLE `allwood_transport` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '运费模板ID',
  `title` varchar(30) NOT NULL COMMENT '运费模板名称',
  `send_tpl_id` mediumint(8) unsigned DEFAULT NULL COMMENT '发货地区模板ID',
  `store_id` mediumint(8) unsigned NOT NULL COMMENT '店铺ID',
  `update_time` int(10) unsigned DEFAULT '0' COMMENT '最后更新时间',
  `area_id` int(11) DEFAULT NULL COMMENT '区域id',
  `city_id` int(11) DEFAULT NULL,
  `freightage_type` varchar(255) DEFAULT NULL COMMENT '运费类型 0自定义 1卖家承担',
  `cash_type` varchar(255) DEFAULT '0' COMMENT '计价方式 0按件数，1按体积，2按重量',
  `province_id` int(11) DEFAULT NULL COMMENT '省id',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8 COMMENT='运费模板';

/*Table structure for table `allwood_transport_extend` */

DROP TABLE IF EXISTS `allwood_transport_extend`;

CREATE TABLE `allwood_transport_extend` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '运费模板扩展ID',
  `area_id` text COMMENT '市级地区ID组成的串，以，隔开，两端也有，',
  `top_area_id` text COMMENT '省级地区ID组成的串，以，隔开，两端也有，',
  `area_name` text COMMENT '地区name组成的串，以，隔开',
  `snum` mediumint(8) unsigned DEFAULT '1' COMMENT '首件数量',
  `sprice` decimal(10,2) DEFAULT '0.00' COMMENT '首件运费',
  `xnum` mediumint(8) unsigned DEFAULT '1' COMMENT '续件数量',
  `xprice` decimal(10,2) DEFAULT '0.00' COMMENT '续件运费',
  `is_default` enum('1','2') DEFAULT '2' COMMENT '是否默认运费1是2否',
  `transport_id` mediumint(8) unsigned NOT NULL COMMENT '运费模板ID',
  `transport_title` varchar(60) DEFAULT NULL COMMENT '运费模板',
  `transport_type` varchar(11) DEFAULT '0' COMMENT '运送方式 0按件 1物流，2，快递 ',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=97 DEFAULT CHARSET=utf8 COMMENT='运费模板扩展表';

/*Table structure for table `allwood_type` */

DROP TABLE IF EXISTS `allwood_type`;

CREATE TABLE `allwood_type` (
  `type_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '类型id',
  `type_name` varchar(100) NOT NULL COMMENT '类型名称',
  `type_sort` tinyint(1) unsigned NOT NULL COMMENT '排序',
  `class_id` int(10) unsigned DEFAULT '0' COMMENT '所属分类id',
  `class_name` varchar(100) NOT NULL COMMENT '所属分类名称',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=147 DEFAULT CHARSET=utf8 COMMENT='商品类型表';

/*Table structure for table `allwood_type_brand` */

DROP TABLE IF EXISTS `allwood_type_brand`;

CREATE TABLE `allwood_type_brand` (
  `type_id` int(10) unsigned NOT NULL COMMENT '类型id',
  `brand_id` int(10) unsigned NOT NULL COMMENT '品牌id',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品类型与品牌对应表';

/*Table structure for table `allwood_type_spec` */

DROP TABLE IF EXISTS `allwood_type_spec`;

CREATE TABLE `allwood_type_spec` (
  `type_id` int(10) unsigned NOT NULL COMMENT '类型id',
  `sp_id` int(10) unsigned NOT NULL COMMENT '规格id',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品类型与规格对应表';

/*Table structure for table `allwood_upload` */

DROP TABLE IF EXISTS `allwood_upload`;

CREATE TABLE `allwood_upload` (
  `upload_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '索引ID',
  `file_name` varchar(100) DEFAULT NULL COMMENT '文件名',
  `file_thumb` varchar(100) DEFAULT NULL COMMENT '缩微图片',
  `file_wm` varchar(100) DEFAULT NULL COMMENT '水印图片',
  `file_size` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文件大小',
  `store_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '店铺ID，0为管理员',
  `upload_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '文件类别，0为无，1为文章图片，默认为0，2为商品切换图片，3为商品内容图片，4为系统文章图片，5为积分礼品切换图片，6为积分礼品内容图片',
  `upload_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `item_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '信息ID',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`upload_id`)
) ENGINE=InnoDB AUTO_INCREMENT=259 DEFAULT CHARSET=utf8 COMMENT='上传文件表';

/*Table structure for table `allwood_voucher` */

DROP TABLE IF EXISTS `allwood_voucher`;

CREATE TABLE `allwood_voucher` (
  `voucher_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '代金券编号',
  `voucher_code` varchar(32) NOT NULL COMMENT '代金券编码',
  `voucher_t_id` int(11) NOT NULL COMMENT '代金券模版编号',
  `voucher_title` varchar(50) NOT NULL COMMENT '代金券标题',
  `voucher_desc` varchar(255) NOT NULL COMMENT '代金券描述',
  `voucher_start_date` int(11) NOT NULL COMMENT '代金券有效期开始时间',
  `voucher_end_date` int(11) NOT NULL COMMENT '代金券有效期结束时间',
  `voucher_price` int(11) NOT NULL COMMENT '代金券面额',
  `voucher_limit` decimal(10,2) NOT NULL COMMENT '代金券使用时的订单限额',
  `voucher_store_id` int(11) NOT NULL COMMENT '代金券的店铺id',
  `voucher_state` tinyint(4) NOT NULL COMMENT '代金券状态(1-未用,2-已用,3-过期,4-收回)',
  `voucher_active_date` int(11) NOT NULL COMMENT '代金券发放日期',
  `voucher_type` tinyint(4) NOT NULL COMMENT '代金券类别',
  `voucher_owner_id` int(11) NOT NULL COMMENT '代金券所有者id',
  `voucher_owner_name` varchar(50) NOT NULL COMMENT '代金券所有者名称',
  `voucher_order_id` int(11) DEFAULT NULL COMMENT '使用该代金券的订单编号',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`voucher_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='代金券表';

/*Table structure for table `allwood_voucher_price` */

DROP TABLE IF EXISTS `allwood_voucher_price`;

CREATE TABLE `allwood_voucher_price` (
  `voucher_price_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '代金券面值编号',
  `voucher_price_describe` varchar(255) NOT NULL COMMENT '代金券描述',
  `voucher_price` int(11) NOT NULL COMMENT '代金券面值',
  `voucher_defaultpoints` int(11) NOT NULL DEFAULT '0' COMMENT '代金劵默认的兑换所需积分可以为0',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`voucher_price_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='代金券面额表';

/*Table structure for table `allwood_voucher_quota` */

DROP TABLE IF EXISTS `allwood_voucher_quota`;

CREATE TABLE `allwood_voucher_quota` (
  `quota_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '套餐编号',
  `quota_applyid` int(11) NOT NULL COMMENT '申请编号',
  `quota_memberid` int(11) NOT NULL COMMENT '会员编号',
  `quota_membername` varchar(100) NOT NULL COMMENT '会员名称',
  `quota_storeid` int(11) NOT NULL COMMENT '店铺编号',
  `quota_storename` varchar(100) NOT NULL COMMENT '店铺名称',
  `quota_starttime` int(11) NOT NULL COMMENT '开始时间',
  `quota_endtime` int(11) NOT NULL COMMENT '结束时间',
  `quota_state` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态(1-可用/2-取消/3-结束)',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`quota_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='代金券套餐表';

/*Table structure for table `allwood_voucher_template` */

DROP TABLE IF EXISTS `allwood_voucher_template`;

CREATE TABLE `allwood_voucher_template` (
  `voucher_t_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '代金券模版编号',
  `voucher_t_title` varchar(50) NOT NULL COMMENT '代金券模版名称',
  `voucher_t_desc` varchar(255) NOT NULL COMMENT '代金券模版描述',
  `voucher_t_start_date` int(11) NOT NULL COMMENT '代金券模版有效期开始时间',
  `voucher_t_end_date` int(11) NOT NULL COMMENT '代金券模版有效期结束时间',
  `voucher_t_price` int(11) NOT NULL COMMENT '代金券模版面额',
  `voucher_t_limit` decimal(10,2) NOT NULL COMMENT '代金券使用时的订单限额',
  `voucher_t_store_id` int(11) NOT NULL COMMENT '代金券模版的店铺id',
  `voucher_t_storename` varchar(100) DEFAULT NULL COMMENT '店铺名称',
  `voucher_t_creator_id` int(11) NOT NULL COMMENT '代金券模版的创建者id',
  `voucher_t_state` tinyint(4) NOT NULL COMMENT '代金券模版状态(1-有效,2-失效)',
  `voucher_t_total` int(11) NOT NULL COMMENT '模版可发放的代金券总数',
  `voucher_t_giveout` int(11) NOT NULL COMMENT '模版已发放的代金券数量',
  `voucher_t_used` int(11) NOT NULL COMMENT '模版已经使用过的代金券',
  `voucher_t_add_date` int(11) NOT NULL COMMENT '模版的创建时间',
  `voucher_t_quotaid` int(11) NOT NULL COMMENT '套餐编号',
  `voucher_t_points` int(11) NOT NULL DEFAULT '0' COMMENT '兑换所需积分',
  `voucher_t_eachlimit` int(11) NOT NULL DEFAULT '1' COMMENT '每人限领张数',
  `voucher_t_styleimg` varchar(200) DEFAULT NULL COMMENT '样式模版图片',
  `voucher_t_customimg` varchar(200) DEFAULT NULL COMMENT '自定义代金券模板图片',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`voucher_t_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='代金券模版表';

/*Table structure for table `allwood_web` */

DROP TABLE IF EXISTS `allwood_web`;

CREATE TABLE `allwood_web` (
  `web_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '模块ID',
  `web_name` varchar(20) DEFAULT '' COMMENT '模块名称',
  `style_name` varchar(20) DEFAULT 'orange' COMMENT '风格名称',
  `web_page` varchar(10) DEFAULT 'index' COMMENT '所在页面(暂时只有index)',
  `update_time` int(10) unsigned NOT NULL COMMENT '更新时间',
  `web_sort` tinyint(1) unsigned DEFAULT '9' COMMENT '排序',
  `web_show` tinyint(1) unsigned DEFAULT '1' COMMENT '是否显示，0为否，1为是，默认为1',
  `web_html` text COMMENT '模块html代码',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`web_id`)
) ENGINE=InnoDB AUTO_INCREMENT=131 DEFAULT CHARSET=utf8 COMMENT='页面模块表';

/*Table structure for table `allwood_web_code` */

DROP TABLE IF EXISTS `allwood_web_code`;

CREATE TABLE `allwood_web_code` (
  `code_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '内容ID',
  `web_id` int(10) unsigned NOT NULL COMMENT '模块ID',
  `code_type` varchar(10) NOT NULL DEFAULT 'array' COMMENT '数据类型:array,html,json',
  `var_name` varchar(20) NOT NULL COMMENT '变量名称',
  `code_info` text COMMENT '内容数据',
  `show_name` varchar(20) DEFAULT '' COMMENT '页面名称',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`code_id`),
  KEY `web_id` (`web_id`)
) ENGINE=InnoDB AUTO_INCREMENT=131 DEFAULT CHARSET=utf8 COMMENT='模块内容表';

/*Table structure for table `go_ad_area` */

DROP TABLE IF EXISTS `go_ad_area`;

CREATE TABLE `go_ad_area` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `width` smallint(6) unsigned DEFAULT NULL,
  `height` smallint(6) unsigned DEFAULT NULL,
  `des` varchar(255) DEFAULT NULL,
  `checked` tinyint(1) DEFAULT '0' COMMENT '1表示通过',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `checked` (`checked`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='广告位';

/*Table structure for table `go_ad_data` */

DROP TABLE IF EXISTS `go_ad_data`;

CREATE TABLE `go_ad_data` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `aid` int(10) unsigned NOT NULL,
  `title` varchar(100) NOT NULL,
  `type` char(10) DEFAULT NULL COMMENT 'code,text,img',
  `content` text,
  `checked` tinyint(1) DEFAULT '0' COMMENT '1表示通过',
  `addtime` int(10) unsigned NOT NULL,
  `endtime` int(10) unsigned NOT NULL,
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `type` (`type`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='广告';

/*Table structure for table `go_admin` */

DROP TABLE IF EXISTS `go_admin`;

CREATE TABLE `go_admin` (
  `uid` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `mid` tinyint(3) unsigned NOT NULL,
  `username` char(15) NOT NULL,
  `userpass` char(32) NOT NULL,
  `useremail` varchar(100) DEFAULT NULL,
  `addtime` int(10) unsigned DEFAULT NULL,
  `logintime` int(10) unsigned DEFAULT NULL,
  `loginip` varchar(15) DEFAULT NULL,
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`uid`),
  KEY `username` (`username`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='管理员表';

/*Table structure for table `go_article` */

DROP TABLE IF EXISTS `go_article`;

CREATE TABLE `go_article` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '文章id',
  `cateid` char(30) NOT NULL COMMENT '文章父ID',
  `author` char(20) DEFAULT NULL,
  `title` char(100) NOT NULL COMMENT '标题',
  `title_style` varchar(100) DEFAULT NULL,
  `thumb` varchar(3) DEFAULT NULL,
  `picarr` text,
  `keywords` varchar(100) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `content` mediumtext COMMENT '内容',
  `hit` int(10) unsigned DEFAULT '0',
  `order` tinyint(3) unsigned DEFAULT NULL,
  `posttime` int(10) unsigned DEFAULT NULL COMMENT '添加时间',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `cateid` (`cateid`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;

/*Table structure for table `go_brand` */

DROP TABLE IF EXISTS `go_brand`;

CREATE TABLE `go_brand` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cateid` varchar(255) DEFAULT NULL,
  `status` char(1) DEFAULT 'Y' COMMENT '显示隐藏',
  `name` varchar(255) DEFAULT NULL,
  `thumb` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `order` int(10) DEFAULT '1',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `order` (`order`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='品牌表';

/*Table structure for table `go_caches` */

DROP TABLE IF EXISTS `go_caches`;

CREATE TABLE `go_caches` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(100) NOT NULL,
  `value` text,
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `key` (`key`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

/*Table structure for table `go_category` */

DROP TABLE IF EXISTS `go_category`;

CREATE TABLE `go_category` (
  `cateid` smallint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT '栏目id',
  `parentid` smallint(6) DEFAULT NULL COMMENT '父ID',
  `channel` tinyint(4) NOT NULL DEFAULT '0',
  `model` tinyint(1) DEFAULT NULL COMMENT '栏目模型',
  `name` varchar(255) DEFAULT NULL COMMENT '栏目名称',
  `catdir` char(20) DEFAULT NULL COMMENT '英文名',
  `url` varchar(255) DEFAULT NULL,
  `info` text,
  `order` smallint(6) unsigned DEFAULT '1' COMMENT '排序',
  `ff` int(2) NOT NULL,
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`cateid`),
  KEY `name` (`name`),
  KEY `order` (`order`)
) ENGINE=MyISAM AUTO_INCREMENT=52 DEFAULT CHARSET=utf8 COMMENT='栏目表';

/*Table structure for table `go_config` */

DROP TABLE IF EXISTS `go_config`;

CREATE TABLE `go_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `value` mediumtext,
  `zhushi` text,
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=36 DEFAULT CHARSET=utf8;

/*Table structure for table `go_egglotter_award` */

DROP TABLE IF EXISTS `go_egglotter_award`;

CREATE TABLE `go_egglotter_award` (
  `award_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `user_id` int(11) DEFAULT NULL COMMENT '用户ID',
  `user_name` varchar(11) DEFAULT NULL COMMENT '用户名字',
  `rule_id` int(11) DEFAULT NULL COMMENT '活动ID',
  `subtime` int(11) DEFAULT NULL COMMENT '中奖时间',
  `spoil_id` int(11) DEFAULT NULL COMMENT '奖品等级',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`award_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `go_egglotter_rule` */

DROP TABLE IF EXISTS `go_egglotter_rule`;

CREATE TABLE `go_egglotter_rule` (
  `rule_id` int(11) NOT NULL AUTO_INCREMENT,
  `rule_name` varchar(200) DEFAULT NULL,
  `starttime` int(11) DEFAULT NULL COMMENT '活动开始时间',
  `endtime` int(11) DEFAULT NULL COMMENT '活动结束时间',
  `subtime` int(11) DEFAULT NULL COMMENT '活动编辑时间',
  `lotterytype` int(11) DEFAULT NULL COMMENT '抽奖按币分类',
  `lotterjb` int(11) DEFAULT NULL COMMENT '每一次抽奖使用的金币',
  `ruledesc` text COMMENT '规则介绍',
  `startusing` tinyint(4) DEFAULT NULL COMMENT '启用',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`rule_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `go_egglotter_spoil` */

DROP TABLE IF EXISTS `go_egglotter_spoil`;

CREATE TABLE `go_egglotter_spoil` (
  `spoil_id` int(11) NOT NULL AUTO_INCREMENT,
  `rule_id` int(11) DEFAULT NULL,
  `spoil_name` text COMMENT '名称',
  `spoil_jl` int(11) DEFAULT NULL COMMENT '机率',
  `spoil_dj` int(11) DEFAULT NULL,
  `urlimg` varchar(200) DEFAULT NULL,
  `subtime` int(11) DEFAULT NULL COMMENT '提交时间',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`spoil_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `go_fund` */

DROP TABLE IF EXISTS `go_fund`;

CREATE TABLE `go_fund` (
  `id` int(10) unsigned NOT NULL,
  `fund_off` tinyint(4) unsigned NOT NULL DEFAULT '1',
  `fund_money` decimal(10,2) unsigned NOT NULL,
  `fund_count_money` decimal(12,2) DEFAULT NULL COMMENT '云购基金',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `go_link` */

DROP TABLE IF EXISTS `go_link`;

CREATE TABLE `go_link` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '友情链接ID',
  `type` tinyint(1) unsigned NOT NULL COMMENT '链接类型',
  `name` char(20) NOT NULL COMMENT '名称',
  `logo` varchar(250) NOT NULL COMMENT '图片',
  `url` varchar(50) NOT NULL COMMENT '地址',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `type` (`type`)
) ENGINE=MyISAM AUTO_INCREMENT=40 DEFAULT CHARSET=utf8;

/*Table structure for table `go_member` */

DROP TABLE IF EXISTS `go_member`;

CREATE TABLE `go_member` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` char(20) NOT NULL COMMENT '用户名',
  `email` varchar(50) DEFAULT NULL COMMENT '用户邮箱',
  `mobile` char(11) DEFAULT NULL COMMENT '用户手机',
  `password` char(32) DEFAULT NULL COMMENT '密码',
  `user_ip` varchar(255) DEFAULT NULL,
  `img` varchar(255) DEFAULT NULL COMMENT '用户头像',
  `qianming` varchar(255) DEFAULT NULL COMMENT '用户签名',
  `groupid` tinyint(4) unsigned DEFAULT '0' COMMENT '用户权限组',
  `addgroup` varchar(255) DEFAULT NULL COMMENT '用户加入的圈子组1|2|3',
  `money` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '账户金额',
  `emailcode` char(21) DEFAULT '-1' COMMENT '邮箱认证码',
  `mobilecode` char(21) DEFAULT '-1' COMMENT '手机认证码',
  `passcode` char(21) DEFAULT '-1' COMMENT '找会密码认证码-1,1,码',
  `reg_key` varchar(100) DEFAULT NULL COMMENT '注册参数',
  `score` int(10) unsigned NOT NULL DEFAULT '0',
  `jingyan` int(10) unsigned DEFAULT '0',
  `yaoqing` int(10) unsigned DEFAULT NULL,
  `band` varchar(255) DEFAULT NULL,
  `time` int(10) DEFAULT NULL,
  `login_time` int(10) unsigned DEFAULT '0',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM AUTO_INCREMENT=13710 DEFAULT CHARSET=utf8 COMMENT='会员表';

/*Table structure for table `go_member_account` */

DROP TABLE IF EXISTS `go_member_account`;

CREATE TABLE `go_member_account` (
  `uid` int(10) unsigned NOT NULL COMMENT '用户id',
  `type` tinyint(1) DEFAULT NULL COMMENT '充值1/消费-1',
  `pay` char(20) DEFAULT NULL,
  `content` varchar(255) DEFAULT NULL COMMENT '详情',
  `money` mediumint(8) NOT NULL DEFAULT '0' COMMENT '金额',
  `time` char(20) NOT NULL,
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  KEY `uid` (`uid`),
  KEY `type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='会员账户明细';

/*Table structure for table `go_member_addmoney_record` */

DROP TABLE IF EXISTS `go_member_addmoney_record`;

CREATE TABLE `go_member_addmoney_record` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL,
  `code` char(20) NOT NULL,
  `money` decimal(10,2) unsigned NOT NULL,
  `pay_type` char(10) NOT NULL,
  `status` char(20) NOT NULL,
  `time` int(10) NOT NULL,
  `score` int(10) unsigned DEFAULT NULL,
  `scookies` text COMMENT '购物车cookie',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=147 DEFAULT CHARSET=utf8;

/*Table structure for table `go_member_band` */

DROP TABLE IF EXISTS `go_member_band`;

CREATE TABLE `go_member_band` (
  `b_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `b_uid` int(10) DEFAULT NULL COMMENT '用户ID',
  `b_type` char(10) DEFAULT NULL COMMENT '绑定登陆类型',
  `b_code` varchar(100) DEFAULT NULL COMMENT '返回数据1',
  `b_data` varchar(100) DEFAULT NULL COMMENT '返回数据2',
  `b_time` int(10) DEFAULT NULL,
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`b_id`),
  KEY `b_uid` (`b_uid`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

/*Table structure for table `go_member_cashout` */

DROP TABLE IF EXISTS `go_member_cashout`;

CREATE TABLE `go_member_cashout` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL COMMENT '用户id',
  `username` varchar(20) NOT NULL COMMENT '开户人',
  `bankname` varchar(255) NOT NULL COMMENT '银行名称',
  `branch` varchar(255) NOT NULL COMMENT '支行',
  `money` decimal(8,0) NOT NULL DEFAULT '0' COMMENT '申请提现金额',
  `time` char(20) NOT NULL COMMENT '申请时间',
  `banknumber` varchar(50) NOT NULL COMMENT '银行帐号',
  `linkphone` varchar(100) NOT NULL COMMENT '联系电话',
  `auditstatus` tinyint(4) NOT NULL COMMENT '1审核通过',
  `procefees` decimal(8,2) NOT NULL COMMENT '手续费',
  `reviewtime` char(20) NOT NULL COMMENT '审核通过时间',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `type` (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='会员账户明细';

/*Table structure for table `go_member_del` */

DROP TABLE IF EXISTS `go_member_del`;

CREATE TABLE `go_member_del` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` char(20) NOT NULL COMMENT '用户名',
  `email` varchar(50) DEFAULT NULL COMMENT '用户邮箱',
  `mobile` char(11) DEFAULT NULL COMMENT '用户手机',
  `password` char(32) DEFAULT NULL COMMENT '密码',
  `user_ip` varchar(255) DEFAULT NULL,
  `img` varchar(255) DEFAULT NULL COMMENT '用户头像',
  `qianming` varchar(255) DEFAULT NULL COMMENT '用户签名',
  `groupid` tinyint(4) unsigned DEFAULT '0' COMMENT '用户权限组',
  `addgroup` varchar(255) DEFAULT NULL COMMENT '用户加入的圈子组1|2|3',
  `money` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '账户金额',
  `emailcode` char(21) DEFAULT '-1' COMMENT '邮箱认证码',
  `mobilecode` char(21) DEFAULT '-1' COMMENT '手机认证码',
  `passcode` char(21) DEFAULT '-1' COMMENT '找会密码认证码-1,1,码',
  `reg_key` varchar(100) DEFAULT NULL COMMENT '注册参数',
  `score` int(10) unsigned NOT NULL DEFAULT '0',
  `jingyan` int(10) unsigned DEFAULT '0',
  `yaoqing` int(10) unsigned DEFAULT NULL,
  `band` varchar(255) DEFAULT NULL,
  `time` int(10) DEFAULT NULL,
  `login_time` int(10) unsigned DEFAULT '0',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM AUTO_INCREMENT=13710 DEFAULT CHARSET=utf8 COMMENT='会员表';

/*Table structure for table `go_member_dizhi` */

DROP TABLE IF EXISTS `go_member_dizhi`;

CREATE TABLE `go_member_dizhi` (
  `id` tinyint(4) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `uid` int(10) NOT NULL COMMENT '用户id',
  `sheng` varchar(15) DEFAULT NULL COMMENT '省',
  `shi` varchar(15) DEFAULT NULL COMMENT '市',
  `xian` varchar(15) DEFAULT NULL COMMENT '县',
  `jiedao` varchar(255) DEFAULT NULL COMMENT '街道地址',
  `youbian` mediumint(8) DEFAULT NULL COMMENT '邮编',
  `shouhuoren` varchar(15) DEFAULT NULL COMMENT '收货人',
  `mobile` char(11) DEFAULT NULL COMMENT '手机',
  `tell` varchar(15) DEFAULT NULL COMMENT '座机号',
  `default` char(1) DEFAULT 'N' COMMENT '是否默认',
  `time` int(10) unsigned NOT NULL,
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COMMENT='会员地址表';

/*Table structure for table `go_member_go_record` */

DROP TABLE IF EXISTS `go_member_go_record`;

CREATE TABLE `go_member_go_record` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` char(20) DEFAULT NULL COMMENT '订单号',
  `code_tmp` tinyint(3) unsigned DEFAULT NULL COMMENT '相同订单',
  `username` varchar(30) NOT NULL,
  `uphoto` varchar(255) DEFAULT NULL,
  `uid` int(10) unsigned NOT NULL COMMENT '会员id',
  `shopid` int(6) unsigned NOT NULL COMMENT '商品id',
  `shopname` varchar(255) NOT NULL COMMENT '商品名',
  `shopqishu` smallint(6) NOT NULL DEFAULT '0' COMMENT '期数',
  `gonumber` smallint(5) unsigned DEFAULT NULL COMMENT '购买次数',
  `goucode` longtext NOT NULL COMMENT '云购码',
  `moneycount` decimal(10,2) NOT NULL,
  `huode` char(50) NOT NULL DEFAULT '0' COMMENT '中奖码',
  `pay_type` char(10) DEFAULT NULL COMMENT '付款方式',
  `ip` varchar(255) DEFAULT NULL,
  `status` char(30) DEFAULT NULL COMMENT '订单状态',
  `company_money` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  `company_code` char(20) DEFAULT NULL,
  `company` char(10) DEFAULT NULL,
  `time` char(21) NOT NULL COMMENT '购买时间',
  `haoma` varchar(9999) NOT NULL DEFAULT '0',
  `charity` varchar(100) DEFAULT NULL,
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `shopid` (`shopid`),
  KEY `time` (`time`)
) ENGINE=MyISAM AUTO_INCREMENT=361 DEFAULT CHARSET=utf8 COMMENT='云购记录表';

/*Table structure for table `go_member_group` */

DROP TABLE IF EXISTS `go_member_group`;

CREATE TABLE `go_member_group` (
  `groupid` tinyint(4) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(15) NOT NULL COMMENT '会员组名',
  `jingyan_start` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '需要的经验值',
  `jingyan_end` int(10) NOT NULL,
  `icon` varchar(255) DEFAULT NULL COMMENT '图标',
  `type` char(1) NOT NULL DEFAULT 'N' COMMENT '是否是系统组',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`groupid`),
  KEY `jingyan` (`jingyan_start`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='会员权限组';

/*Table structure for table `go_member_message` */

DROP TABLE IF EXISTS `go_member_message`;

CREATE TABLE `go_member_message` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL COMMENT '用户id',
  `type` tinyint(1) DEFAULT '0' COMMENT '消息来源,0系统,1私信',
  `sendid` int(10) unsigned DEFAULT '0' COMMENT '发送人ID',
  `sendname` char(20) DEFAULT NULL COMMENT '发送人名',
  `content` varchar(255) DEFAULT NULL COMMENT '发送内容',
  `time` int(10) DEFAULT NULL,
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='会员消息表';

/*Table structure for table `go_member_recodes` */

DROP TABLE IF EXISTS `go_member_recodes`;

CREATE TABLE `go_member_recodes` (
  `uid` int(10) unsigned NOT NULL COMMENT '用户id',
  `type` tinyint(1) NOT NULL COMMENT '收取1//充值-2/提现-3',
  `content` varchar(255) NOT NULL COMMENT '详情',
  `shopid` int(11) DEFAULT NULL COMMENT '商品id',
  `money` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT '佣金',
  `time` char(20) NOT NULL,
  `ygmoney` decimal(8,2) NOT NULL COMMENT '云购金额',
  `cashoutid` int(11) DEFAULT NULL COMMENT '申请提现记录表id',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  KEY `uid` (`uid`),
  KEY `type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='会员账户明细';

/*Table structure for table `go_model` */

DROP TABLE IF EXISTS `go_model`;

CREATE TABLE `go_model` (
  `modelid` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(10) NOT NULL,
  `table` char(20) NOT NULL,
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`modelid`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='模型表';

/*Table structure for table `go_navigation` */

DROP TABLE IF EXISTS `go_navigation`;

CREATE TABLE `go_navigation` (
  `cid` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `parentid` smallint(6) unsigned DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `type` char(10) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `status` char(1) DEFAULT 'Y' COMMENT '显示/隐藏',
  `order` smallint(6) unsigned DEFAULT '1',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`cid`),
  KEY `status` (`status`),
  KEY `order` (`order`),
  KEY `type` (`type`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;

/*Table structure for table `go_pay` */

DROP TABLE IF EXISTS `go_pay`;

CREATE TABLE `go_pay` (
  `pay_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pay_name` char(20) NOT NULL,
  `pay_class` char(20) NOT NULL,
  `pay_type` tinyint(3) NOT NULL,
  `pay_thumb` varchar(255) DEFAULT NULL,
  `pay_des` text,
  `pay_start` tinyint(4) NOT NULL,
  `pay_key` text,
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`pay_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

/*Table structure for table `go_position` */

DROP TABLE IF EXISTS `go_position`;

CREATE TABLE `go_position` (
  `pos_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pos_model` tinyint(3) unsigned NOT NULL,
  `pos_name` varchar(30) NOT NULL,
  `pos_num` tinyint(3) unsigned NOT NULL,
  `pos_maxnum` tinyint(3) unsigned NOT NULL,
  `pos_this_num` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `pos_time` int(10) unsigned NOT NULL,
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`pos_id`),
  KEY `pos_id` (`pos_id`),
  KEY `pos_model` (`pos_model`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `go_position_data` */

DROP TABLE IF EXISTS `go_position_data`;

CREATE TABLE `go_position_data` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `con_id` int(10) unsigned NOT NULL,
  `mod_id` tinyint(3) unsigned NOT NULL,
  `mod_name` char(20) NOT NULL,
  `pos_id` int(10) unsigned NOT NULL,
  `pos_data` mediumtext NOT NULL,
  `pos_order` int(10) unsigned NOT NULL DEFAULT '1',
  `pos_time` int(10) unsigned NOT NULL,
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `go_qqset` */

DROP TABLE IF EXISTS `go_qqset`;

CREATE TABLE `go_qqset` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `qq` varchar(11) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `type` varchar(20) DEFAULT NULL,
  `province` varchar(50) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `county` varchar(50) DEFAULT NULL,
  `qqurl` varchar(250) DEFAULT NULL,
  `full` varchar(6) DEFAULT NULL COMMENT '是否已满',
  `subtime` int(11) DEFAULT NULL,
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;

/*Table structure for table `go_quanzi` */

DROP TABLE IF EXISTS `go_quanzi`;

CREATE TABLE `go_quanzi` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `title` char(15) NOT NULL COMMENT '标题',
  `img` varchar(255) DEFAULT NULL COMMENT '图片地址',
  `chengyuan` mediumint(8) unsigned DEFAULT '0' COMMENT '成员数',
  `tiezi` mediumint(8) unsigned DEFAULT '0' COMMENT '帖子数',
  `guanli` mediumint(8) unsigned NOT NULL COMMENT '管理员',
  `jinhua` smallint(5) unsigned DEFAULT NULL COMMENT '精华帖',
  `jianjie` varchar(255) DEFAULT '暂无介绍' COMMENT '简介',
  `gongao` varchar(255) DEFAULT '暂无' COMMENT '公告',
  `jiaru` char(1) DEFAULT 'Y' COMMENT '申请加入',
  `glfatie` char(1) DEFAULT 'N' COMMENT '发帖权限',
  `time` int(11) NOT NULL COMMENT '时间',
  `huifu` char(1) NOT NULL DEFAULT 'Y',
  `shenhe` char(1) DEFAULT 'N',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

/*Table structure for table `go_quanzi_hueifu` */

DROP TABLE IF EXISTS `go_quanzi_hueifu`;

CREATE TABLE `go_quanzi_hueifu` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tzid` int(11) DEFAULT NULL COMMENT '帖子ID匹配',
  `hueifu` text COMMENT '回复内容',
  `hueiyuan` varchar(255) DEFAULT NULL COMMENT '会员',
  `hftime` int(11) DEFAULT NULL COMMENT '时间',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `go_quanzi_tiezi` */

DROP TABLE IF EXISTS `go_quanzi_tiezi`;

CREATE TABLE `go_quanzi_tiezi` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `qzid` int(10) unsigned DEFAULT NULL COMMENT '圈子ID匹配',
  `hueiyuan` varchar(255) DEFAULT NULL COMMENT '会员信息',
  `title` varchar(255) DEFAULT NULL COMMENT '标题',
  `neirong` text COMMENT '内容',
  `hueifu` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '回复',
  `dianji` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点击量',
  `zhiding` char(1) DEFAULT 'N' COMMENT '置顶',
  `jinghua` char(1) DEFAULT 'N' COMMENT '精华',
  `zuihou` varchar(255) DEFAULT NULL COMMENT '最后回复',
  `time` int(10) unsigned DEFAULT NULL COMMENT '时间',
  `tiezi` int(10) unsigned NOT NULL DEFAULT '0',
  `shenhe` char(1) NOT NULL DEFAULT 'Y',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

/*Table structure for table `go_recom` */

DROP TABLE IF EXISTS `go_recom`;

CREATE TABLE `go_recom` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '推荐位id',
  `img` varchar(50) DEFAULT NULL COMMENT '推荐位图片',
  `title` varchar(30) DEFAULT NULL COMMENT '推荐位标题',
  `link` varchar(255) DEFAULT NULL,
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `go_send` */

DROP TABLE IF EXISTS `go_send`;

CREATE TABLE `go_send` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL,
  `gid` int(10) unsigned NOT NULL,
  `username` varchar(30) NOT NULL,
  `shoptitle` varchar(200) NOT NULL,
  `send_type` tinyint(4) NOT NULL,
  `send_time` int(10) unsigned NOT NULL,
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `gid` (`gid`),
  KEY `send_type` (`send_type`)
) ENGINE=MyISAM AUTO_INCREMENT=118 DEFAULT CHARSET=utf8;

/*Table structure for table `go_shaidan` */

DROP TABLE IF EXISTS `go_shaidan`;

CREATE TABLE `go_shaidan` (
  `sd_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '晒单id',
  `sd_userid` int(10) unsigned DEFAULT NULL COMMENT '用户ID',
  `sd_shopid` int(10) unsigned DEFAULT NULL COMMENT '商品ID',
  `sd_qishu` int(10) DEFAULT NULL COMMENT '商品期数',
  `sd_ip` varchar(255) DEFAULT NULL,
  `sd_title` varchar(255) DEFAULT NULL COMMENT '晒单标题',
  `sd_thumbs` varchar(255) DEFAULT NULL COMMENT '缩略图',
  `sd_content` text COMMENT '晒单内容',
  `sd_photolist` text COMMENT '晒单图片',
  `sd_zhan` int(10) unsigned DEFAULT '0' COMMENT '点赞',
  `sd_ping` int(10) unsigned DEFAULT '0' COMMENT '评论',
  `sd_time` int(10) unsigned DEFAULT NULL COMMENT '晒单时间',
  `sd_shopsid` int(10) unsigned DEFAULT NULL,
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`sd_id`),
  KEY `sd_userid` (`sd_userid`),
  KEY `sd_shopid` (`sd_shopid`),
  KEY `sd_zhan` (`sd_zhan`),
  KEY `sd_ping` (`sd_ping`),
  KEY `sd_time` (`sd_time`)
) ENGINE=MyISAM AUTO_INCREMENT=28 DEFAULT CHARSET=utf8 COMMENT='晒单';

/*Table structure for table `go_shaidan_hueifu` */

DROP TABLE IF EXISTS `go_shaidan_hueifu`;

CREATE TABLE `go_shaidan_hueifu` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sdhf_id` int(11) NOT NULL COMMENT '晒单ID',
  `sdhf_userid` int(11) DEFAULT NULL COMMENT '晒单回复会员ID',
  `sdhf_content` text COMMENT '晒单回复内容',
  `sdhf_time` int(11) DEFAULT NULL,
  `sdhf_username` char(20) DEFAULT NULL,
  `sdhf_img` varchar(255) DEFAULT NULL,
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

/*Table structure for table `go_shopcodes_1` */

DROP TABLE IF EXISTS `go_shopcodes_1`;

CREATE TABLE `go_shopcodes_1` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `s_id` int(10) unsigned NOT NULL,
  `s_cid` smallint(5) unsigned NOT NULL,
  `s_len` smallint(5) DEFAULT NULL,
  `s_codes` text,
  `s_codes_tmp` text,
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `s_id` (`s_id`),
  KEY `s_cid` (`s_cid`),
  KEY `s_len` (`s_len`)
) ENGINE=MyISAM AUTO_INCREMENT=498 DEFAULT CHARSET=utf8;

/*Table structure for table `go_shoplist` */

DROP TABLE IF EXISTS `go_shoplist`;

CREATE TABLE `go_shoplist` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '商品id',
  `sid` int(10) unsigned NOT NULL COMMENT '同一个商品',
  `cateid` smallint(6) unsigned DEFAULT NULL COMMENT '所属栏目ID',
  `brandid` smallint(6) unsigned DEFAULT NULL COMMENT '所属品牌ID',
  `title` varchar(100) DEFAULT NULL COMMENT '商品标题',
  `title_style` varchar(100) DEFAULT NULL,
  `title2` varchar(100) DEFAULT NULL COMMENT '副标题',
  `keywords` varchar(100) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `money` decimal(10,2) DEFAULT '0.00' COMMENT '金额',
  `yunjiage` decimal(4,2) unsigned DEFAULT '1.00' COMMENT '云购人次价格',
  `zongrenshu` int(10) unsigned DEFAULT '0' COMMENT '总需人数',
  `canyurenshu` int(10) unsigned DEFAULT '0' COMMENT '已参与人数',
  `shenyurenshu` int(10) unsigned DEFAULT NULL,
  `def_renshu` int(10) unsigned DEFAULT '0',
  `qishu` smallint(6) unsigned DEFAULT '0' COMMENT '期数',
  `maxqishu` smallint(5) unsigned DEFAULT '1' COMMENT ' 最大期数',
  `thumb` varchar(255) DEFAULT NULL,
  `picarr` text COMMENT '商品图片',
  `content` mediumtext COMMENT '商品内容详情',
  `codes_table` char(20) DEFAULT NULL,
  `xsjx_time` int(10) unsigned DEFAULT NULL,
  `pos` tinyint(4) unsigned DEFAULT NULL COMMENT '是否推荐',
  `renqi` tinyint(4) unsigned DEFAULT '0' COMMENT '是否人气商品0否1是',
  `time` int(10) unsigned DEFAULT NULL COMMENT '时间',
  `order` int(10) unsigned DEFAULT '1',
  `q_uid` int(10) unsigned DEFAULT NULL COMMENT '中奖人ID',
  `q_user` text COMMENT '中奖人信息',
  `q_user_code` char(20) DEFAULT NULL COMMENT '中奖码',
  `q_content` mediumtext COMMENT '揭晓内容',
  `q_counttime` char(20) DEFAULT NULL COMMENT '总时间相加',
  `q_end_time` char(20) DEFAULT NULL COMMENT '揭晓时间',
  `q_showtime` char(1) DEFAULT 'N' COMMENT 'Y/N揭晓动画',
  `zhiding` int(10) NOT NULL DEFAULT '0',
  `haoma` varchar(9999) NOT NULL DEFAULT '1',
  `jieshu` int(10) NOT NULL DEFAULT '0',
  `shuliang` varchar(9999) NOT NULL DEFAULT '0',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `renqi` (`renqi`),
  KEY `order` (`yunjiage`),
  KEY `q_uid` (`q_uid`),
  KEY `sid` (`sid`),
  KEY `shenyurenshu` (`shenyurenshu`),
  KEY `q_showtime` (`q_showtime`)
) ENGINE=MyISAM AUTO_INCREMENT=17108 DEFAULT CHARSET=utf8 COMMENT='商品表';

/*Table structure for table `go_shoplist_del` */

DROP TABLE IF EXISTS `go_shoplist_del`;

CREATE TABLE `go_shoplist_del` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sid` int(10) NOT NULL COMMENT '同一个商品',
  `cateid` smallint(6) unsigned DEFAULT NULL,
  `brandid` smallint(6) unsigned DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `title_style` varchar(100) DEFAULT NULL,
  `title2` varchar(100) DEFAULT NULL,
  `keywords` varchar(100) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `money` decimal(10,2) DEFAULT '0.00',
  `yunjiage` decimal(4,2) unsigned DEFAULT '1.00',
  `zongrenshu` int(10) unsigned DEFAULT '0',
  `canyurenshu` int(10) unsigned DEFAULT '0',
  `shenyurenshu` int(10) unsigned DEFAULT NULL,
  `def_renshu` int(10) unsigned DEFAULT '0',
  `qishu` smallint(6) unsigned DEFAULT '0',
  `maxqishu` smallint(5) unsigned DEFAULT '1',
  `thumb` varchar(255) DEFAULT NULL,
  `picarr` text,
  `content` mediumtext,
  `codes_table` char(20) DEFAULT NULL,
  `xsjx_time` int(10) unsigned DEFAULT NULL,
  `pos` tinyint(4) unsigned DEFAULT NULL,
  `renqi` tinyint(4) unsigned DEFAULT '0',
  `time` int(10) unsigned DEFAULT NULL,
  `order` int(10) unsigned DEFAULT '1',
  `q_uid` int(10) unsigned DEFAULT NULL,
  `q_user` text COMMENT '中奖人信息',
  `q_user_code` char(20) DEFAULT NULL,
  `q_content` mediumtext,
  `q_counttime` char(20) DEFAULT NULL,
  `q_end_time` char(20) DEFAULT NULL,
  `q_showtime` char(1) DEFAULT 'N' COMMENT 'Y/N揭晓动画',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `renqi` (`renqi`),
  KEY `order` (`yunjiage`),
  KEY `q_uid` (`q_uid`),
  KEY `sid` (`sid`),
  KEY `shenyurenshu` (`shenyurenshu`),
  KEY `q_showtime` (`q_showtime`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `go_slide` */

DROP TABLE IF EXISTS `go_slide`;

CREATE TABLE `go_slide` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `img` varchar(50) DEFAULT NULL COMMENT '幻灯片',
  `title` varchar(30) DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `img` (`img`)
) ENGINE=MyISAM AUTO_INCREMENT=22 DEFAULT CHARSET=utf8 COMMENT='幻灯片表';

/*Table structure for table `go_template` */

DROP TABLE IF EXISTS `go_template`;

CREATE TABLE `go_template` (
  `template_name` char(25) NOT NULL,
  `template` char(25) NOT NULL,
  `des` varchar(100) DEFAULT NULL,
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  KEY `template` (`template`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `go_vote_activer` */

DROP TABLE IF EXISTS `go_vote_activer`;

CREATE TABLE `go_vote_activer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `option_id` int(11) NOT NULL,
  `vote_id` int(11) DEFAULT NULL,
  `userid` int(11) DEFAULT NULL,
  `ip` char(20) DEFAULT NULL,
  `subtime` int(11) DEFAULT NULL,
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `go_vote_option` */

DROP TABLE IF EXISTS `go_vote_option`;

CREATE TABLE `go_vote_option` (
  `option_id` int(11) NOT NULL AUTO_INCREMENT,
  `vote_id` int(11) DEFAULT NULL,
  `option_title` varchar(100) DEFAULT NULL,
  `option_number` int(11) unsigned DEFAULT '0',
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`option_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Table structure for table `go_vote_subject` */

DROP TABLE IF EXISTS `go_vote_subject`;

CREATE TABLE `go_vote_subject` (
  `vote_id` int(11) NOT NULL AUTO_INCREMENT,
  `vote_title` varchar(100) DEFAULT NULL,
  `vote_starttime` int(11) DEFAULT NULL,
  `vote_endtime` int(11) DEFAULT NULL,
  `vote_sendtime` int(11) DEFAULT NULL,
  `vote_description` text,
  `vote_allowview` tinyint(1) DEFAULT NULL,
  `vote_allowguest` tinyint(1) DEFAULT NULL,
  `vote_interval` int(11) DEFAULT '0',
  `vote_enabled` tinyint(1) DEFAULT NULL,
  `vote_number` int(11) DEFAULT NULL,
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`vote_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Table structure for table `go_wap` */

DROP TABLE IF EXISTS `go_wap`;

CREATE TABLE `go_wap` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `img` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `title` varchar(30) CHARACTER SET utf8 DEFAULT NULL,
  `link` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `color` varchar(30) CHARACTER SET utf8 DEFAULT NULL,
  `tesu_deleted` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `tesu_description` varchar(50) DEFAULT NULL COMMENT '描述',
  `tesu_created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=gbk;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
