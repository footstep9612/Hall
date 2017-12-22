/*
Navicat MySQL Data Transfer

Source Server         : 172.18.18.193_3306
Source Server Version : 50505
Source Host           : 172.18.18.193:3306
Source Database       : erui_stock

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2017-12-22 10:58:38
*/

SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for `home_country`
-- ----------------------------
DROP TABLE IF EXISTS `home_country`;
CREATE TABLE `home_country` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `country_bn` varchar(96) NOT NULL COMMENT '国家简码',
  `show_flag` char(1) NOT NULL DEFAULT 'Y' COMMENT '显示标志 Y表示显示',
  `display_position` varchar(100) DEFAULT NULL COMMENT '显示位置',
  `created_at` datetime NOT NULL COMMENT '添加时间',
  `created_by` bigint(20) NOT NULL COMMENT '添加人',
  `updated_at` datetime DEFAULT NULL COMMENT '维护时间',
  `deleted_at` datetime DEFAULT NULL COMMENT '删除时间',
  `deleted_by` bigint(20) DEFAULT '0' COMMENT '删除人ID',
  `updated_by` bigint(20) DEFAULT '0' COMMENT '维护人',
  PRIMARY KEY (`id`),
  KEY `country_bn` (`country_bn`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='首页国家 不存在 调用默认国家 China';

-- ----------------------------
-- Records of home_country
-- ----------------------------
INSERT INTO home_country VALUES ('1', 'China', 'N', 'LEFT', '2017-12-15 18:30:31', '38699', '2017-12-15 18:39:27', null, '0', '38699');

-- ----------------------------
-- Table structure for `home_country_ads`
-- ----------------------------
DROP TABLE IF EXISTS `home_country_ads`;
CREATE TABLE `home_country_ads` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `lang` char(2) DEFAULT 'en' COMMENT '语言',
  `country_bn` varchar(96) NOT NULL COMMENT '国家简称',
  `sort_order` smallint(6) DEFAULT '0' COMMENT '排序',
  `group` varchar(200) DEFAULT 'BANNER' COMMENT '图片分组 BANNER ,OTHER ',
  `img_url` varchar(200) DEFAULT NULL COMMENT '图片地址',
  `img_name` varchar(200) DEFAULT '' COMMENT '图片名称',
  `link` varchar(200) DEFAULT NULL COMMENT '广告链接',
  `status` varchar(20) DEFAULT 'VALID' COMMENT '状态',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `created_by` bigint(20) NOT NULL DEFAULT '0' COMMENT '创建人',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `updated_by` bigint(20) DEFAULT NULL COMMENT '更新人ID',
  `deleted_flag` char(1) DEFAULT 'N' COMMENT '删除标志',
  `deleted_by` bigint(20) DEFAULT '0',
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `country_bn` (`country_bn`),
  KEY `lang` (`lang`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='首页广告图片';

-- ----------------------------
-- Records of home_country_ads
-- ----------------------------
INSERT INTO home_country_ads VALUES ('1', 'en', 'China', '0', 'BANNER', 'group1/M00/00/7D/rBISxFozpm6AM-2RAAMMDzn30rA052.jpg', 'index_banner', null, 'VALID', '2017-12-15 18:42:48', '38699', null, null, 'N', '0', null);
INSERT INTO home_country_ads VALUES ('2', 'en', 'China', '0', 'BANNER', 'group1/M00/00/7D/rBISxFozpm6AM-2RAAMMDzn30rA052.jpg', 'index_banner', null, 'VALID', '2017-12-15 18:43:03', '38699', null, null, 'Y', '38699', '2017-12-15 18:52:28');
INSERT INTO home_country_ads VALUES ('3', 'en', 'China', '0', 'BANNER', 'group1/M00/00/81/rBISxFo0qhCAXbW8AAHxhkP0ydI885.jpg', 'nowgoods_banner_01.jpg', null, 'VALID', '2017-12-16 13:15:22', '38699', null, null, 'N', '0', null);
INSERT INTO home_country_ads VALUES ('4', 'en', 'China', '0', 'HOT', 'group1/M00/00/81/rBISxFo0rNKABAf1AAH741tkdvQ945.png', 'index_advert_01.png', null, 'VALID', '2017-12-16 13:19:42', '38699', null, null, 'N', '0', null);
INSERT INTO home_country_ads VALUES ('6', 'en', 'China', '0', 'HOT', 'group1/M00/00/81/rBISxFo0rRGAIVf-AAC1stziL9k738.png', 'index_advert_02', '/en/rfq/find.html', 'VALID', '2017-12-16 13:28:04', '38699', null, null, 'N', '0', null);

-- ----------------------------
-- Table structure for `home_country_nav`
-- ----------------------------
DROP TABLE IF EXISTS `home_country_nav`;
CREATE TABLE `home_country_nav` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `lang` char(2) DEFAULT 'en' COMMENT '语言',
  `country_bn` varchar(96) NOT NULL COMMENT '国家简称',
  `sort_order` smallint(6) DEFAULT '0' COMMENT '排序',
  `nav_name` varchar(200) DEFAULT NULL COMMENT '导航名称',
  `nav_url` varchar(200) DEFAULT '' COMMENT '导航链接',
  `status` varchar(20) DEFAULT 'VALID' COMMENT '状态',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `created_by` bigint(20) NOT NULL DEFAULT '0' COMMENT '创建人',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `deleted_at` datetime DEFAULT NULL COMMENT '删除时间',
  `deleted_by` bigint(20) DEFAULT '0' COMMENT '删除人ID',
  `updated_by` bigint(20) DEFAULT NULL COMMENT '更新人ID',
  `deleted_flag` char(1) DEFAULT 'N' COMMENT '删除标志',
  PRIMARY KEY (`id`),
  KEY `country_bn` (`country_bn`),
  KEY `lang` (`lang`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='首页国家导航';

-- ----------------------------
-- Records of home_country_nav
-- ----------------------------
INSERT INTO home_country_nav VALUES ('1', 'en', 'China', '0', 'PPE', '/en/product/index/keyword/Shoe.html', 'VALID', '2017-12-15 21:34:40', '38699', '2017-12-16 10:04:25', '2017-12-15 21:48:20', '38699', '38699', 'N');
INSERT INTO home_country_nav VALUES ('2', 'en', 'China', '0', 'Helmet', '/en/product/index/keyword/Helmet.html', 'VALID', '2017-12-15 21:35:19', '38699', '2017-12-16 10:03:22', null, '0', '38699', 'N');
INSERT INTO home_country_nav VALUES ('3', 'en', 'China', '0', 'Mud pump', '/en/product/index/keyword/Mud+pump.html', 'VALID', '2017-12-15 21:35:55', '38699', '2017-12-16 10:03:47', null, '0', '38699', 'N');

-- ----------------------------
-- Table structure for `home_floor`
-- ----------------------------
DROP TABLE IF EXISTS `home_floor`;
CREATE TABLE `home_floor` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `lang` char(6) NOT NULL DEFAULT 'en' COMMENT '语言',
  `floor_name` varchar(100) NOT NULL COMMENT '楼层名称',
  `country_bn` varchar(100) NOT NULL COMMENT '国家简码',
  `onshelf_flag` char(1) NOT NULL DEFAULT 'N' COMMENT '上下架标志',
  `description` tinytext COMMENT '描述',
  `sort_order` smallint(6) NOT NULL DEFAULT '0' COMMENT '排序',
  `spu_count` smallint(6) NOT NULL DEFAULT '0' COMMENT '产品数量',
  `created_at` datetime NOT NULL COMMENT '添加时间',
  `created_by` bigint(20) NOT NULL COMMENT '添加人',
  `updated_at` datetime DEFAULT NULL COMMENT '维护时间',
  `deleted_at` datetime DEFAULT NULL COMMENT '删除时间',
  `deleted_by` bigint(20) DEFAULT '0' COMMENT '删除人ID',
  `updated_by` bigint(20) DEFAULT '0' COMMENT '维护人',
  `deleted_flag` char(1) DEFAULT 'N' COMMENT '删除标志',
  PRIMARY KEY (`id`),
  KEY `country_bn` (`country_bn`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='首页楼层';

-- ----------------------------
-- Records of home_floor
-- ----------------------------
INSERT INTO home_floor VALUES ('1', 'en', 'Petroleum special pipes', 'China', 'Y', 'Petroleum special pipes', '0', '0', '2017-12-15 19:49:32', '38699', null, null, '0', '0', 'N');
INSERT INTO home_floor VALUES ('2', 'en', 'Steel', 'China', 'Y', 'Steel', '0', '8', '2017-12-15 19:49:51', '38699', '2017-12-15 19:52:34', null, '0', '38699', 'N');
INSERT INTO home_floor VALUES ('3', 'en', 'Hardwares', 'China', 'Y', 'Steel', '0', '0', '2017-12-15 19:50:05', '38699', null, null, '0', '0', 'N');
INSERT INTO home_floor VALUES ('4', 'en', 'Chemical products', 'China', 'Y', 'Steel', '0', '0', '2017-12-15 19:50:14', '38699', null, null, '0', '0', 'N');
INSERT INTO home_floor VALUES ('5', 'en', 'Rubber products', 'China', 'Y', 'Steel', '0', '0', '2017-12-15 19:50:25', '38699', null, null, '0', '0', 'N');
INSERT INTO home_floor VALUES ('6', 'en', 'Labour protection', 'China', 'Y', 'Steel', '0', '0', '2017-12-15 19:50:34', '38699', null, null, '0', '0', 'N');

-- ----------------------------
-- Table structure for `home_floor_ads`
-- ----------------------------
DROP TABLE IF EXISTS `home_floor_ads`;
CREATE TABLE `home_floor_ads` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `lang` char(2) DEFAULT 'en' COMMENT '语言',
  `country_bn` varchar(96) NOT NULL COMMENT '国家简称',
  `floor_id` bigint(20) DEFAULT '0' COMMENT '楼层ID',
  `sort_order` smallint(6) DEFAULT '0' COMMENT '排序',
  `group` varchar(200) DEFAULT 'BACKGROUP' COMMENT '图片分组',
  `img_url` varchar(200) DEFAULT NULL COMMENT '图片地址',
  `img_name` varchar(200) DEFAULT '' COMMENT '图片名称',
  `link` varchar(200) DEFAULT NULL COMMENT '广告链接',
  `status` varchar(20) DEFAULT 'VALID' COMMENT '状态',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `created_by` bigint(20) NOT NULL DEFAULT '0' COMMENT '创建人',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `deleted_at` datetime DEFAULT NULL COMMENT '删除时间',
  `deleted_by` bigint(20) DEFAULT '0' COMMENT '删除人ID',
  `updated_by` bigint(20) DEFAULT NULL COMMENT '更新人ID',
  `deleted_flag` char(1) DEFAULT 'N' COMMENT '删除标志',
  PRIMARY KEY (`id`),
  KEY `country_bn` (`country_bn`),
  KEY `lang` (`lang`),
  KEY `floor_id` (`floor_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='首页广告图片';

-- ----------------------------
-- Records of home_floor_ads
-- ----------------------------
INSERT INTO home_floor_ads VALUES ('1', 'en', 'China', '1', '0', 'BACKGROUP', 'group1/M00/00/7D/rBISxFozpm6AM-2RAAMMDzn30rA052.jpg', 'index_banner', null, 'VALID', '2017-12-15 19:38:27', '38699', '2017-12-15 19:39:02', '2017-12-15 19:39:15', '38699', '38699', 'N');

-- ----------------------------
-- Table structure for `home_floor_keyword`
-- ----------------------------
DROP TABLE IF EXISTS `home_floor_keyword`;
CREATE TABLE `home_floor_keyword` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `lang` char(2) DEFAULT 'en' COMMENT '语言',
  `country_bn` varchar(96) NOT NULL COMMENT '国家简称',
  `floor_id` bigint(20) DEFAULT '0' COMMENT '楼层ID',
  `keyword` varchar(200) NOT NULL COMMENT '关键词',
  `sort_order` smallint(6) DEFAULT '0' COMMENT '排序',
  `status` varchar(20) DEFAULT 'VALID' COMMENT '状态',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `created_by` bigint(20) NOT NULL DEFAULT '0' COMMENT '创建人',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `deleted_at` datetime DEFAULT NULL COMMENT '删除时间',
  `deleted_by` bigint(20) DEFAULT '0' COMMENT '删除人ID',
  `updated_by` bigint(20) DEFAULT NULL COMMENT '更新人ID',
  `deleted_flag` char(1) DEFAULT 'N' COMMENT '删除标志',
  PRIMARY KEY (`id`),
  KEY `country_bn` (`country_bn`),
  KEY `lang` (`lang`),
  KEY `floor_id` (`floor_id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8 COMMENT='首页楼层关键词';

-- ----------------------------
-- Records of home_floor_keyword
-- ----------------------------
INSERT INTO home_floor_keyword VALUES ('1', 'en', 'China', '6', 'protective clothing', '0', 'VALID', '2017-12-15 20:01:56', '38699', null, null, '0', null, 'N');
INSERT INTO home_floor_keyword VALUES ('2', 'en', 'China', '6', 'hats gloves shoes', '0', 'VALID', '2017-12-15 20:01:56', '38699', null, null, '0', null, 'N');
INSERT INTO home_floor_keyword VALUES ('3', 'en', 'China', '6', 'protective equipment', '0', 'VALID', '2017-12-15 20:01:56', '38699', null, null, '0', null, 'N');
INSERT INTO home_floor_keyword VALUES ('4', 'en', 'China', '6', 'medical health protective equipment', '0', 'VALID', '2017-12-15 20:01:56', '38699', null, null, '0', null, 'N');
INSERT INTO home_floor_keyword VALUES ('5', 'en', 'China', '6', 'industrial clothing', '0', 'VALID', '2017-12-15 20:01:56', '38699', null, null, '0', null, 'N');
INSERT INTO home_floor_keyword VALUES ('6', 'en', 'China', '6', 'cleaning product', '0', 'VALID', '2017-12-15 20:01:56', '38699', null, null, '0', null, 'N');
INSERT INTO home_floor_keyword VALUES ('7', 'en', 'China', '6', 'bed clothes', '0', 'VALID', '2017-12-15 20:01:56', '38699', null, null, '0', null, 'N');
INSERT INTO home_floor_keyword VALUES ('8', 'en', 'China', '1', 'hardwares', '0', 'VALID', '2017-12-16 14:10:34', '38699', null, null, '0', null, 'N');
INSERT INTO home_floor_keyword VALUES ('9', 'en', 'China', '1', 'steel', '0', 'VALID', '2017-12-16 14:10:34', '38699', null, null, '0', null, 'N');
INSERT INTO home_floor_keyword VALUES ('10', 'en', 'China', '1', 'cement and cement products', '0', 'VALID', '2017-12-16 14:10:34', '38699', null, null, '0', null, 'N');
INSERT INTO home_floor_keyword VALUES ('11', 'en', 'China', '1', 'casing pipe', '0', 'VALID', '2017-12-16 14:12:44', '38699', null, null, '0', null, 'N');
INSERT INTO home_floor_keyword VALUES ('12', 'en', 'China', '1', 'coupling and pup joint', '0', 'VALID', '2017-12-16 14:12:44', '38699', null, null, '0', null, 'N');
INSERT INTO home_floor_keyword VALUES ('13', 'en', 'China', '1', 'coupling,pup joint', '0', 'VALID', '2017-12-16 14:12:44', '38699', null, null, '0', null, 'N');
INSERT INTO home_floor_keyword VALUES ('14', 'en', 'China', '1', 'drill collar', '0', 'VALID', '2017-12-16 14:12:44', '38699', null, null, '0', null, 'N');
INSERT INTO home_floor_keyword VALUES ('15', 'en', 'China', '1', 'drill pipe', '0', 'VALID', '2017-12-16 14:12:44', '38699', null, null, '0', null, 'N');
INSERT INTO home_floor_keyword VALUES ('16', 'en', 'China', '1', 'kelly', '0', 'VALID', '2017-12-16 14:12:44', '38699', null, null, '0', null, 'N');
INSERT INTO home_floor_keyword VALUES ('17', 'en', 'China', '1', 'oil tubing', '0', 'VALID', '2017-12-16 14:12:44', '38699', null, null, '0', null, 'N');
INSERT INTO home_floor_keyword VALUES ('18', 'en', 'China', '1', 'petroleum special pipes', '0', 'VALID', '2017-12-16 14:12:44', '38699', null, null, '0', null, 'N');
INSERT INTO home_floor_keyword VALUES ('19', 'en', 'China', '1', 'pipeline', '0', 'VALID', '2017-12-16 14:12:44', '38699', null, null, '0', null, 'N');
INSERT INTO home_floor_keyword VALUES ('20', 'en', 'China', '1', 'screen pipe', '0', 'VALID', '2017-12-16 14:12:44', '38699', null, null, '0', null, 'N');
INSERT INTO home_floor_keyword VALUES ('21', 'en', 'China', '2', 'barbed wire', '0', 'VALID', '2017-12-16 14:13:51', '38699', null, null, '0', null, 'N');
INSERT INTO home_floor_keyword VALUES ('22', 'en', 'China', '2', 'cast iron pipe', '0', 'VALID', '2017-12-16 14:13:51', '38699', null, null, '0', null, 'N');
INSERT INTO home_floor_keyword VALUES ('23', 'en', 'China', '2', 'ferroalloy', '0', 'VALID', '2017-12-16 14:13:51', '38699', null, null, '0', null, 'N');
INSERT INTO home_floor_keyword VALUES ('24', 'en', 'China', '2', 'iron wire', '0', 'VALID', '2017-12-16 14:13:51', '38699', null, null, '0', null, 'N');
INSERT INTO home_floor_keyword VALUES ('25', 'en', 'China', '2', 'metal wire', '0', 'VALID', '2017-12-16 14:13:51', '38699', null, null, '0', null, 'N');
INSERT INTO home_floor_keyword VALUES ('26', 'en', 'China', '2', 'rail', '0', 'VALID', '2017-12-16 14:13:51', '38699', null, null, '0', null, 'N');
INSERT INTO home_floor_keyword VALUES ('27', 'en', 'China', '2', 'shape steel', '0', 'VALID', '2017-12-16 14:13:51', '38699', null, null, '0', null, 'N');
INSERT INTO home_floor_keyword VALUES ('28', 'en', 'China', '2', 'steel', '0', 'VALID', '2017-12-16 14:13:51', '38699', null, null, '0', null, 'N');
INSERT INTO home_floor_keyword VALUES ('29', 'en', 'China', '3', 'chain', '0', 'VALID', '2017-12-16 14:14:15', '38699', null, null, '0', null, 'N');
INSERT INTO home_floor_keyword VALUES ('30', 'en', 'China', '3', 'chain adjuster', '0', 'VALID', '2017-12-16 14:14:15', '38699', null, null, '0', null, 'N');
INSERT INTO home_floor_keyword VALUES ('31', 'en', 'China', '3', 'chain wheel', '0', 'VALID', '2017-12-16 14:14:15', '38699', null, null, '0', null, 'N');
INSERT INTO home_floor_keyword VALUES ('32', 'en', 'China', '3', 'door', '0', 'VALID', '2017-12-16 14:14:15', '38699', null, null, '0', null, 'N');
INSERT INTO home_floor_keyword VALUES ('33', 'en', 'China', '3', 'door, window', '0', 'VALID', '2017-12-16 14:14:15', '38699', null, null, '0', null, 'N');
INSERT INTO home_floor_keyword VALUES ('34', 'en', 'China', '3', 'doorknob', '0', 'VALID', '2017-12-16 14:14:15', '38699', null, null, '0', null, 'N');
INSERT INTO home_floor_keyword VALUES ('35', 'en', 'China', '3', 'faucet', '0', 'VALID', '2017-12-16 14:14:15', '38699', null, null, '0', null, 'N');
INSERT INTO home_floor_keyword VALUES ('36', 'en', 'China', '3', 'flux', '0', 'VALID', '2017-12-16 14:14:15', '38699', null, null, '0', null, 'N');
INSERT INTO home_floor_keyword VALUES ('37', 'en', 'China', '4', 'lubricant', '0', 'VALID', '2017-12-16 14:14:32', '38699', null, null, '0', null, 'N');
INSERT INTO home_floor_keyword VALUES ('38', 'en', 'China', '4', 'petroleum coke', '0', 'VALID', '2017-12-16 14:14:32', '38699', null, null, '0', null, 'N');
INSERT INTO home_floor_keyword VALUES ('39', 'en', 'China', '4', 'petroleum wax', '0', 'VALID', '2017-12-16 14:14:32', '38699', null, null, '0', null, 'N');
INSERT INTO home_floor_keyword VALUES ('40', 'en', 'China', '4', 'raw oil', '0', 'VALID', '2017-12-16 14:14:32', '38699', null, null, '0', null, 'N');
INSERT INTO home_floor_keyword VALUES ('41', 'en', 'China', '4', 'sealing grease', '0', 'VALID', '2017-12-16 14:14:32', '38699', null, null, '0', null, 'N');
INSERT INTO home_floor_keyword VALUES ('42', 'en', 'China', '4', 'solvent oil', '0', 'VALID', '2017-12-16 14:14:32', '38699', null, null, '0', null, 'N');
INSERT INTO home_floor_keyword VALUES ('43', 'en', 'China', '4', 'standard oil', '0', 'VALID', '2017-12-16 14:14:32', '38699', null, null, '0', null, 'N');
INSERT INTO home_floor_keyword VALUES ('44', 'en', 'China', '5', 'asbestos', '0', 'VALID', '2017-12-16 14:14:53', '38699', null, null, '0', null, 'N');
INSERT INTO home_floor_keyword VALUES ('45', 'en', 'China', '5', 'brick', '0', 'VALID', '2017-12-16 14:14:53', '38699', null, null, '0', null, 'N');
INSERT INTO home_floor_keyword VALUES ('46', 'en', 'China', '5', 'felt', '0', 'VALID', '2017-12-16 14:14:53', '38699', null, null, '0', null, 'N');
INSERT INTO home_floor_keyword VALUES ('47', 'en', 'China', '5', 'glazing', '0', 'VALID', '2017-12-16 14:14:53', '38699', null, null, '0', null, 'N');
INSERT INTO home_floor_keyword VALUES ('48', 'en', 'China', '5', 'sand', '0', 'VALID', '2017-12-16 14:14:53', '38699', null, null, '0', null, 'N');
INSERT INTO home_floor_keyword VALUES ('49', 'en', 'China', '5', 'stone', '0', 'VALID', '2017-12-16 14:14:53', '38699', null, null, '0', null, 'N');
INSERT INTO home_floor_keyword VALUES ('50', 'en', 'China', '5', 'tile', '0', 'VALID', '2017-12-16 14:14:53', '38699', null, null, '0', null, 'N');

-- ----------------------------
-- Table structure for `home_floor_product`
-- ----------------------------
DROP TABLE IF EXISTS `home_floor_product`;
CREATE TABLE `home_floor_product` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `lang` char(2) DEFAULT 'en' COMMENT '语言',
  `country_bn` varchar(96) NOT NULL COMMENT '国家简称',
  `spu` varchar(16) DEFAULT NULL COMMENT 'SPU编码',
  `floor_id` bigint(20) DEFAULT '0' COMMENT '楼层ID',
  `sort_order` smallint(6) DEFAULT '0' COMMENT '排序',
  `name` varchar(200) DEFAULT NULL,
  `show_name` varchar(200) DEFAULT '' COMMENT 'SKU名称',
  `status` varchar(20) DEFAULT 'VALID' COMMENT '状态',
  `deleted_by` bigint(20) DEFAULT '0',
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `created_by` bigint(20) NOT NULL DEFAULT '0' COMMENT '创建人',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `updated_by` bigint(20) DEFAULT NULL COMMENT '更新人ID',
  `deleted_flag` char(1) DEFAULT 'N' COMMENT '删除标志',
  PRIMARY KEY (`id`),
  KEY `country_bn` (`country_bn`),
  KEY `lang` (`lang`),
  KEY `floor_id` (`floor_id`)
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8 COMMENT='首页楼层产品';

-- ----------------------------
-- Records of home_floor_product
-- ----------------------------
INSERT INTO home_floor_product VALUES ('1', 'en', 'China', '0102010000010000', '1', '0', 'Drill Pipe', 'Drill Pipe', 'VALID', '0', null, '2017-12-15 19:44:42', '38699', '2017-12-15 19:45:29', '38699', 'N');
INSERT INTO home_floor_product VALUES ('2', 'en', 'China', '0102010000020000', '1', '0', 'Heavy Weight Drill Pipe', 'API 5DP Heavy Weight Drill Pipe', 'VALID', '0', null, '2017-12-15 19:44:42', '38699', '2017-12-15 19:45:29', '38699', 'N');
INSERT INTO home_floor_product VALUES ('3', 'en', 'China', '0102010000030000', '1', '0', 'Heavy Weight Drill Pipe', '3-1/2\" 4-1/2\" 5\" NC31 Heavy Weight Drill Pipe', 'VALID', '0', null, '2017-12-15 19:44:42', '38699', '2017-12-15 19:45:29', '38699', 'N');
INSERT INTO home_floor_product VALUES ('4', 'en', 'China', '0102010000040000', '1', '0', 'Drill Pipe', 'API5D, Drill Pipe', 'VALID', '0', null, '2017-12-15 19:44:42', '38699', '2017-12-15 19:45:29', '38699', 'N');
INSERT INTO home_floor_product VALUES ('5', 'en', 'China', '0103010000030000', '1', '0', 'Integral Rock Drill Rod', 'Integral Rock Drill Rod', 'VALID', '0', null, '2017-12-15 19:44:42', '38699', '2017-12-15 19:45:30', '38699', 'N');
INSERT INTO home_floor_product VALUES ('6', 'en', 'China', '0103010000040000', '1', '0', 'Integral Flat-tip  Drill Rod 1', 'Integral Flat-tip  Drill Rod 1', 'VALID', '0', null, '2017-12-15 19:44:42', '38699', '2017-12-15 19:45:30', '38699', 'N');
INSERT INTO home_floor_product VALUES ('7', 'en', 'China', '0103010000050000', '1', '0', 'Integral Flat-tip  Drill Rod', 'Integral Flat-tip  Drill Rod', 'VALID', '0', null, '2017-12-15 19:44:42', '38699', '2017-12-15 19:45:30', '38699', 'N');
INSERT INTO home_floor_product VALUES ('8', 'en', 'China', '0103010000060000', '1', '0', 'Flat-tip U-shaped  Drill Bit', 'Flat-tip U-shaped  Drill Bit', 'VALID', '0', null, '2017-12-15 19:44:42', '38699', '2017-12-15 19:45:30', '38699', 'N');
INSERT INTO home_floor_product VALUES ('9', 'en', 'China', '0206010000010000', '2', '0', 'Eccentric Reducer', 'Eccentric Reducer', 'VALID', '0', null, '2017-12-15 19:55:45', '38699', null, null, 'N');
INSERT INTO home_floor_product VALUES ('10', 'en', 'China', '0206010000020000', '2', '0', 'Concentric Reducer', 'Concentric Reducer', 'VALID', '0', null, '2017-12-15 19:55:45', '38699', null, null, 'N');
INSERT INTO home_floor_product VALUES ('11', 'en', 'China', '0206010000050000', '2', '0', 'Eccentric Reducer', 'BW eccentric reducer', 'VALID', '0', null, '2017-12-15 19:55:45', '38699', null, null, 'N');
INSERT INTO home_floor_product VALUES ('12', 'en', 'China', '0206010000060000', '2', '0', 'Concentric Reducer', 'BW concentric reducer', 'VALID', '0', null, '2017-12-15 19:55:45', '38699', null, null, 'N');
INSERT INTO home_floor_product VALUES ('13', 'en', 'China', '0206010000090000', '2', '0', 'Reducing Pipe', 'Reducing Pipe', 'VALID', '0', null, '2017-12-15 19:55:45', '38699', null, null, 'N');
INSERT INTO home_floor_product VALUES ('14', 'en', 'China', '0206010000100000', '2', '0', 'Eccentric Reducing Pipe', 'Eccentric Reducing Pipe', 'VALID', '0', null, '2017-12-15 19:55:45', '38699', null, null, 'N');
INSERT INTO home_floor_product VALUES ('15', 'en', 'China', '0206010000110000', '2', '0', 'Concentric Reducing Pipe', 'Concentric Reducing Pipe', 'VALID', '0', null, '2017-12-15 19:55:45', '38699', null, null, 'N');
INSERT INTO home_floor_product VALUES ('16', 'en', 'China', '0210030000050000', '2', '0', '7x[6x37 (a)+IWR] 7x[6x61(a)+IWR] Steel Cables', '7x[6x37 (a)+IWR] 7x[6x61(a)+IWR] Steel Cables', 'VALID', '0', null, '2017-12-15 19:55:45', '38699', null, null, 'N');
INSERT INTO home_floor_product VALUES ('17', 'en', 'China', '0409010000280000', '3', '0', 'TIG welding wire for stainless steel', 'TIG welding wire for stainless steel', 'VALID', '0', null, '2017-12-15 19:56:14', '38699', null, null, 'N');
INSERT INTO home_floor_product VALUES ('18', 'en', 'China', '0409010000100000', '3', '0', 'Cast Iron Electrode', 'Cast Iron Electrode', 'VALID', '0', null, '2017-12-15 19:56:14', '38699', null, null, 'N');
INSERT INTO home_floor_product VALUES ('19', 'en', 'China', '0409010000040000', '3', '0', 'Low Alloy Steel Electrode', 'Low Alloy Steel Electrode', 'VALID', '0', null, '2017-12-15 19:56:14', '38699', null, null, 'N');
INSERT INTO home_floor_product VALUES ('20', 'en', 'China', '0403020000170000', '3', '0', 'ST4.8 Cross Recessed Countersunk Head Drilling Screw', 'ST4.8 Cross Recessed Countersunk Head Drilling Screw', 'VALID', '0', null, '2017-12-15 19:56:14', '38699', null, null, 'N');
INSERT INTO home_floor_product VALUES ('21', 'en', 'China', '0409010000050000', '3', '0', 'Overlaying Electrode', 'Overlaying Electrode', 'VALID', '0', null, '2017-12-15 19:56:14', '38699', null, null, 'N');
INSERT INTO home_floor_product VALUES ('22', 'en', 'China', '0409010000030000', '3', '0', 'Stainless Steel Electrode', 'Stainless Steel Electrode', 'VALID', '0', null, '2017-12-15 19:56:14', '38699', null, null, 'N');
INSERT INTO home_floor_product VALUES ('23', 'en', 'China', '0409010000150000', '3', '0', 'Stainless Steel Electrode', 'Stainless Steel Electrode', 'VALID', '0', null, '2017-12-15 19:56:14', '38699', null, null, 'N');
INSERT INTO home_floor_product VALUES ('24', 'en', 'China', '0409010000330000', '3', '0', 'Aluminum Silicon Alloy Welding Wire', 'Aluminum Silicon Alloy Welding Wire', 'VALID', '0', null, '2017-12-15 19:56:14', '38699', null, null, 'N');
INSERT INTO home_floor_product VALUES ('25', 'en', 'China', '0408020000010000', '3', '0', 'Chain Adjuster', 'Chain Adjuster', 'VALID', '0', null, '2017-12-15 19:56:14', '38699', null, null, 'N');
INSERT INTO home_floor_product VALUES ('26', 'en', 'China', '0409010000230000', '3', '0', 'Antihydrogen Steel Gas Shield Welding Wire', 'Antihydrogen Steel Gas Shield Welding Wire', 'VALID', '0', null, '2017-12-15 19:56:14', '38699', null, null, 'N');
INSERT INTO home_floor_product VALUES ('27', 'en', 'China', '0901010000110000', '4', '0', 'Anionic Polyacrylamide', 'Anionic Polyacrylamide', 'VALID', '0', null, '2017-12-15 19:56:34', '38699', null, null, 'N');
INSERT INTO home_floor_product VALUES ('28', 'en', 'China', '0901010000130000', '4', '0', 'Nonionic Polyacrylamide', 'Nonionic Polyacrylamide', 'VALID', '0', null, '2017-12-15 19:56:34', '38699', null, null, 'N');
INSERT INTO home_floor_product VALUES ('29', 'en', 'China', '0901010000140000', '4', '0', 'Xanthan Gum', 'Xanthan Gum', 'VALID', '0', null, '2017-12-15 19:56:34', '38699', null, null, 'N');
INSERT INTO home_floor_product VALUES ('30', 'en', 'China', '0901050000010000', '4', '0', 'CrosslinkingAgent for Fracturing', 'Crosslinking Agent for Fracturing ', 'VALID', '0', null, '2017-12-15 19:56:34', '38699', null, null, 'N');
INSERT INTO home_floor_product VALUES ('31', 'en', 'China', '0901080000010000', '4', '0', 'Defoamer(CM015A)', 'Defoamer(CM015A)', 'VALID', '0', null, '2017-12-15 19:56:35', '38699', null, null, 'N');
INSERT INTO home_floor_product VALUES ('32', 'en', 'China', '0901080000020000', '4', '0', 'Defoamer(CM053)', 'Defoamer(CM053)', 'VALID', '0', null, '2017-12-15 19:56:35', '38699', null, null, 'N');
INSERT INTO home_floor_product VALUES ('33', 'en', 'China', '0901140000010000', '4', '0', 'High-temperatureFluid Loss Control Agent', 'High-temperatureFluid Loss Control Agent', 'VALID', '0', null, '2017-12-15 19:56:35', '38699', null, null, 'N');
INSERT INTO home_floor_product VALUES ('34', 'en', 'China', '0901140000020000', '4', '0', 'Retarding Fluid Loss Control Agent', 'Retarding Fluid Loss Control Agent', 'VALID', '0', null, '2017-12-15 19:56:35', '38699', null, null, 'N');
INSERT INTO home_floor_product VALUES ('35', 'en', 'China', '1002010000040000', '5', '0', 'COLO H01 Pattern Tire', 'COLO H01 Pattern Tire', 'VALID', '0', null, '2017-12-15 19:56:54', '38699', null, null, 'N');
INSERT INTO home_floor_product VALUES ('36', 'en', 'China', '1002010000050000', '5', '0', 'DIAS ZERO Pattern Tire', 'DIAS ZERO Pattern Tire', 'VALID', '0', null, '2017-12-15 19:56:54', '38699', null, null, 'N');
INSERT INTO home_floor_product VALUES ('37', 'en', 'China', '1002010000060000', '5', '0', 'DRAK M-T Pattern Tire', 'DRAK M-T Pattern Tire', 'VALID', '0', null, '2017-12-15 19:56:54', '38699', null, null, 'N');
INSERT INTO home_floor_product VALUES ('38', 'en', 'China', '1002010000070000', '5', '0', 'ENRI U08Pattern Tire', 'ENRI U08Pattern Tire', 'VALID', '0', null, '2017-12-15 19:56:54', '38699', null, null, 'N');
INSERT INTO home_floor_product VALUES ('39', 'en', 'China', '1002010000080000', '5', '0', 'L-COMFORT68 Pattern Tire', 'L-COMFORT68 Pattern Tire', 'VALID', '0', null, '2017-12-15 19:56:54', '38699', null, null, 'N');
INSERT INTO home_floor_product VALUES ('40', 'en', 'China', '1002010000090000', '5', '0', 'L-FINDER 78 Pattern Tire', 'L-FINDER 78 Pattern Tire', 'VALID', '0', null, '2017-12-15 19:56:54', '38699', null, null, 'N');
INSERT INTO home_floor_product VALUES ('41', 'en', 'China', '1002010000100000', '5', '0', 'L-GRIP 16 Pattern Tire', 'L-GRIP 16 Pattern Tire', 'VALID', '0', null, '2017-12-15 19:56:54', '38699', null, null, 'N');
INSERT INTO home_floor_product VALUES ('42', 'en', 'China', '1002010000110000', '5', '0', 'L-MAX 39 Pattern Tire ', 'L-MAX 39 Pattern Tire ', 'VALID', '0', null, '2017-12-15 19:56:54', '38699', null, null, 'N');
INSERT INTO home_floor_product VALUES ('43', 'en', 'China', '1301010000300000', '6', '0', 'High visibility multi-function waistcoat', 'High visibility multi-function waistcoat', 'VALID', '0', null, '2017-12-15 19:57:18', '38699', null, null, 'N');
INSERT INTO home_floor_product VALUES ('44', 'en', 'China', '1301010000020000', '6', '0', 'Function bomber jacket', 'Function bomber jacket', 'VALID', '0', null, '2017-12-15 19:57:18', '38699', null, null, 'N');
INSERT INTO home_floor_product VALUES ('45', 'en', 'China', '1301010000030000', '6', '0', 'High visibility rain suit', 'High visibility rain suit', 'VALID', '0', null, '2017-12-15 19:57:18', '38699', null, null, 'N');
INSERT INTO home_floor_product VALUES ('46', 'en', 'China', '1301010000040000', '6', '0', 'High visibility outdoor-design jacket', 'High visibility outdoor-design jacket', 'VALID', '0', null, '2017-12-15 19:57:18', '38699', null, null, 'N');
INSERT INTO home_floor_product VALUES ('47', 'en', 'China', '1301010000050000', '6', '0', 'High visibility light weight thermal jacket', 'High visibility light weight thermal jacket', 'VALID', '0', null, '2017-12-15 19:57:18', '38699', null, null, 'N');
INSERT INTO home_floor_product VALUES ('48', 'en', 'China', '1301010000060000', '6', '0', 'Hi-visibility jacket with 3M tape', 'Hi-visibility jacket with 3M tape', 'VALID', '0', null, '2017-12-15 19:57:18', '38699', null, null, 'N');
INSERT INTO home_floor_product VALUES ('49', 'en', 'China', '1301010000070000', '6', '0', 'High visibility pilot jacket ', 'High visibility pilot jacket ', 'VALID', '0', null, '2017-12-15 19:57:18', '38699', null, null, 'N');
INSERT INTO home_floor_product VALUES ('50', 'en', 'China', '1301010000080000', '6', '0', 'Hi-visibility waterproof jacket', 'Hi-visibility waterproof jacket', 'VALID', '0', null, '2017-12-15 19:57:18', '38699', null, null, 'N');
INSERT INTO home_floor_product VALUES ('51', 'en', 'China', '0210030000060000', '4', '0', 'Class 7X[6X37(b)+IWR] Steel Cable', 'Class 7X[6X37(b)+IWR] Steel Cable', 'VALID', '0', null, '2017-12-16 14:19:43', '38699', null, null, 'N');
INSERT INTO home_floor_product VALUES ('52', 'en', 'China', '0210030000070000', '4', '0', '8X[6X37+IWR]+IWRC Steel Cable', '8X[6X37+IWR]+IWRC Steel Cable', 'VALID', '0', null, '2017-12-16 14:19:43', '38699', null, null, 'N');
INSERT INTO home_floor_product VALUES ('53', 'en', 'China', '0210030000080000', '4', '0', '8X[6X61+IWR]+IWRC Steel Cable', '8X[6X61+IWR]+IWRC Steel Cable', 'VALID', '0', null, '2017-12-16 14:19:43', '38699', null, null, 'N');
INSERT INTO home_floor_product VALUES ('54', 'en', 'China', '0210030000090000', '4', '0', 'Class 9X[6X37(a)+IWR] 9X[6X61(a)+IWR] Steel Cable', 'Class 9X[6X37(a)+IWR] 9X[6X61(a)+IWR] Steel Cable', 'VALID', '0', null, '2017-12-16 14:19:43', '38699', null, null, 'N');
INSERT INTO home_floor_product VALUES ('55', 'en', 'China', '0210030000100000', '4', '0', 'BDJWGS GT6 Z & PZ', 'BDJWGS GT6 Z & PZ', 'VALID', '0', null, '2017-12-16 14:19:43', '38699', null, null, 'N');
INSERT INTO home_floor_product VALUES ('56', 'en', 'China', '0210030000110000', '4', '0', 'BDJWGS GT8 Z & PZ', 'BDJWGS GT8 Z & PZ', 'VALID', '0', null, '2017-12-16 14:19:43', '38699', null, null, 'N');
INSERT INTO home_floor_product VALUES ('57', 'en', 'China', '0210030000120000', '4', '0', 'BDJWGS GT8 ZD', 'BDJWGS GT8 ZD', 'VALID', '0', null, '2017-12-16 14:19:43', '38699', null, null, 'N');
INSERT INTO home_floor_product VALUES ('58', 'en', 'China', '1102010000040000', '1', '0', 'PE100 Grade  Water Supply Pipeline', 'PE100 Grade  Water Supply Pipeline', 'VALID', '0', null, '2017-12-16 14:21:03', '38699', null, null, 'N');
INSERT INTO home_floor_product VALUES ('59', 'en', 'China', '1102010000050000', '1', '0', 'UHWMPE socker lined tubing', 'Ultra-high-molecular-weight Polythylene Pipes', 'VALID', '0', null, '2017-12-16 14:21:03', '38699', null, null, 'N');
INSERT INTO home_floor_product VALUES ('60', 'en', 'China', '1102010000060000', '1', '0', 'UHWMPE ground pipeline', 'UHWMPE ground pipeline', 'VALID', '0', null, '2017-12-16 14:21:03', '38699', null, null, 'N');
INSERT INTO home_floor_product VALUES ('61', 'en', 'China', '1102010000080000', '1', '0', 'PE Socket Reducing Tee', 'PE Socket Reducing Tee', 'VALID', '0', null, '2017-12-16 14:21:03', '38699', null, null, 'N');
INSERT INTO home_floor_product VALUES ('62', 'en', 'China', '1102010000090000', '1', '0', 'PE Socket Reducing Coupling', 'PE Socket Reducing Coupling', 'VALID', '0', null, '2017-12-16 14:21:03', '38699', null, null, 'N');
INSERT INTO home_floor_product VALUES ('63', 'en', 'China', '1102010000100000', '1', '0', 'PE Butt Flange', 'PE Butt Flange', 'VALID', '0', null, '2017-12-16 14:21:03', '38699', null, null, 'N');
INSERT INTO home_floor_product VALUES ('64', 'en', 'China', '1102010000110000', '1', '0', 'PE 100 Grade Water Supply Pipe', 'PE 100 Grade Water Supply Pipe', 'VALID', '0', null, '2017-12-16 14:21:03', '38699', null, null, 'N');
INSERT INTO home_floor_product VALUES ('65', 'en', 'China', '1102010000120000', '1', '0', 'PE Socket Welding 45° Elbow', 'PE Socket Welding 45° Elbow', 'VALID', '0', null, '2017-12-16 14:21:03', '38699', null, null, 'N');

-- ----------------------------
-- Table structure for `home_floor_show_cat`
-- ----------------------------
DROP TABLE IF EXISTS `home_floor_show_cat`;
CREATE TABLE `home_floor_show_cat` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `lang` char(2) DEFAULT 'en' COMMENT '语言',
  `country_bn` varchar(96) NOT NULL COMMENT '国家简称',
  `floor_id` bigint(20) DEFAULT '0' COMMENT '楼层ID',
  `cat_name` varchar(200) DEFAULT NULL,
  `cat_no` varchar(10) NOT NULL COMMENT '关键词',
  `sort_order` smallint(6) DEFAULT '0' COMMENT '排序',
  `status` varchar(20) DEFAULT 'VALID' COMMENT '状态',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `created_by` bigint(20) NOT NULL DEFAULT '0' COMMENT '创建人',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `deleted_at` datetime DEFAULT NULL COMMENT '删除时间',
  `deleted_by` bigint(20) DEFAULT '0' COMMENT '删除人ID',
  `updated_by` bigint(20) DEFAULT NULL COMMENT '更新人ID',
  `deleted_flag` char(1) DEFAULT 'N' COMMENT '删除标志',
  PRIMARY KEY (`id`),
  KEY `country_bn` (`country_bn`),
  KEY `lang` (`lang`),
  KEY `floor_id` (`floor_id`),
  KEY `cat_no` (`cat_no`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='首页楼层与分类';

-- ----------------------------
-- Records of home_floor_show_cat
-- ----------------------------
INSERT INTO home_floor_show_cat VALUES ('1', 'en', 'China', '1', 'kelly ', '010100', '0', 'VALID', '2017-12-15 20:03:13', '38699', null, null, '0', null, 'N');
INSERT INTO home_floor_show_cat VALUES ('2', 'en', 'China', '1', 'drill pipe', '010200', '0', 'VALID', '2017-12-15 20:03:13', '38699', null, null, '0', null, 'N');
INSERT INTO home_floor_show_cat VALUES ('3', 'en', 'China', '1', 'drill collar', '010300', '0', 'VALID', '2017-12-15 20:03:14', '38699', null, null, '0', null, 'N');
INSERT INTO home_floor_show_cat VALUES ('4', 'en', 'China', '1', 'casing pipe', '010400', '0', 'VALID', '2017-12-15 20:03:14', '38699', null, null, '0', null, 'N');
INSERT INTO home_floor_show_cat VALUES ('5', 'en', 'China', '1', 'oil tubing', '010500', '0', 'VALID', '2017-12-15 20:03:14', '38699', null, null, '0', null, 'N');

-- ----------------------------
-- Table structure for `product_relation`
-- ----------------------------
DROP TABLE IF EXISTS `product_relation`;
CREATE TABLE `product_relation` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '添加人',
  `lang` varchar(6) NOT NULL COMMENT '语言',
  `spu` varchar(16) NOT NULL COMMENT 'SPU编码',
  `relation_spu` varchar(16) NOT NULL COMMENT '被关联的SPU编码',
  `created_at` datetime NOT NULL COMMENT '添加时间',
  `updated_at` datetime DEFAULT NULL COMMENT '删除时间',
  `deleted_at` datetime DEFAULT NULL COMMENT '删除时间',
  `deleted_by` bigint(20) DEFAULT '0' COMMENT '删除人ID',
  `updated_by` bigint(20) DEFAULT '0' COMMENT '删除人ID',
  `created_by` bigint(20) NOT NULL,
  `deleted_flag` char(1) DEFAULT 'N' COMMENT '删除标志 N表示 未删除',
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_relation_lang_spu_relation_spu` (`lang`,`spu`,`relation_spu`) USING BTREE,
  KEY `product_relation_spu` (`spu`),
  KEY `product_relation_relation_spu` (`relation_spu`),
  KEY `product_relation_deleted_flag` (`deleted_flag`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='SPU关联维护表';

-- ----------------------------
-- Records of product_relation
-- ----------------------------

-- ----------------------------
-- Table structure for `stock`
-- ----------------------------
DROP TABLE IF EXISTS `stock`;
CREATE TABLE `stock` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `lang` char(2) DEFAULT 'en' COMMENT '语言',
  `country_bn` varchar(96) NOT NULL COMMENT '国家简称',
  `spu` varchar(16) DEFAULT NULL COMMENT 'SPU编码',
  `sku` varchar(16) NOT NULL DEFAULT '' COMMENT 'SKU编号',
  `floor_id` bigint(20) DEFAULT '0' COMMENT '楼层ID',
  `sort_order` smallint(6) DEFAULT '0' COMMENT '排序',
  `name` varchar(200) DEFAULT NULL,
  `show_name` varchar(200) DEFAULT '' COMMENT 'SKU名称',
  `stock` int(10) DEFAULT '1' COMMENT '库存',
  `status` varchar(20) DEFAULT 'VALID' COMMENT '状态',
  `deleted_at` datetime DEFAULT NULL,
  `deleted_by` bigint(20) DEFAULT '0',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `created_by` bigint(20) NOT NULL DEFAULT '0' COMMENT '创建人',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `updated_by` bigint(20) DEFAULT NULL COMMENT '更新人ID',
  `deleted_flag` char(1) DEFAULT 'N' COMMENT '删除标志',
  PRIMARY KEY (`id`),
  KEY `country_bn` (`country_bn`),
  KEY `lang` (`lang`),
  KEY `floor_id` (`floor_id`),
  KEY `sku` (`sku`)
) ENGINE=InnoDB AUTO_INCREMENT=131 DEFAULT CHARSET=utf8 COMMENT='现货';

-- ----------------------------
-- Records of stock
-- ----------------------------
INSERT INTO stock VALUES ('3', 'en', 'China', '3101100000020000', '3101100000010001', '1', '0', 'VARCO Top Drive Lower Cock', 'VARCO Top Drive Lower Cock', '5', 'VALID', null, '0', '2017-12-06 17:30:57', '38699', null, null, 'N');
INSERT INTO stock VALUES ('85', 'en', 'China', '1302010000090000', '1302010000090001', '1', '0', 'Helmet', 'V-type helmet', '50', 'VALID', '2017-12-15 17:52:20', '38699', '2017-12-13 11:20:05', '38699', '2017-12-13 11:28:12', '38699', 'Y');
INSERT INTO stock VALUES ('86', 'en', 'China', '1302010000090000', '1302010000090002', '1', '0', 'Helmet', 'V-type helmet', '1', 'VALID', '2017-12-15 17:52:20', '38699', '2017-12-13 11:20:05', '38699', '2017-12-13 11:28:12', '38699', 'Y');
INSERT INTO stock VALUES ('87', 'en', 'China', '1302010000090000', '1302010000090003', '1', '0', 'Helmet', 'V-type helmet', '1', 'VALID', null, '0', '2017-12-13 11:20:05', '38699', '2017-12-13 11:28:12', '38699', 'N');
INSERT INTO stock VALUES ('88', 'en', 'China', '1302010000090000', '1302010000090004', '1', '0', 'Helmet', 'V-type helmet', '1', 'VALID', null, '0', '2017-12-13 11:20:05', '38699', '2017-12-13 11:28:12', '38699', 'N');
INSERT INTO stock VALUES ('89', 'en', 'China', '1302010000020000', '1302010000020001', '1', '0', 'Helmet', 'Breathable helmet', '1', 'VALID', null, '0', '2017-12-13 11:20:05', '38699', '2017-12-13 11:28:12', '38699', 'N');
INSERT INTO stock VALUES ('90', 'en', 'China', '1302010000020000', '1302010000020002', '1', '0', 'Helmet', 'Breathable helmet', '1', 'VALID', null, '0', '2017-12-13 11:20:06', '38699', '2017-12-13 11:28:12', '38699', 'N');
INSERT INTO stock VALUES ('91', 'en', 'China', '1302010000020000', '1302010000020003', '1', '0', 'Helmet', 'Breathable helmet', '1', 'VALID', null, '0', '2017-12-13 11:20:06', '38699', '2017-12-13 11:28:12', '38699', 'N');
INSERT INTO stock VALUES ('92', 'en', 'China', '1302010000020000', '1302010000020004', '2', '0', 'Helmet', 'Breathable helmet', '1', 'VALID', null, '0', '2017-12-13 11:20:06', '38699', '2017-12-13 11:28:12', '38699', 'N');
INSERT INTO stock VALUES ('93', 'en', 'China', '1302010000030000', '1302010000030001', '2', '0', 'Helmet', 'Breathable helmet', '1', 'VALID', null, '0', '2017-12-13 11:20:06', '38699', '2017-12-13 11:28:12', '38699', 'N');
INSERT INTO stock VALUES ('94', 'en', 'China', '1302010000030000', '1302010000030002', '2', '0', 'Helmet', 'Breathable helmet', '1', 'VALID', null, '0', '2017-12-13 11:20:06', '38699', '2017-12-13 11:28:12', '38699', 'N');
INSERT INTO stock VALUES ('95', 'en', 'China', '1302010000030000', '1302010000030003', '2', '0', 'Helmet', 'Breathable helmet', '1', 'VALID', null, '0', '2017-12-13 11:20:06', '38699', '2017-12-13 11:28:12', '38699', 'N');
INSERT INTO stock VALUES ('96', 'en', 'China', '1302010000030000', '1302010000030004', '2', '0', 'Helmet', 'Breathable helmet', '1', 'VALID', null, '0', '2017-12-13 11:20:06', '38699', '2017-12-13 11:28:12', '38699', 'N');
INSERT INTO stock VALUES ('97', 'en', 'China', '1302010000040000', '1302010000040001', '2', '0', 'Helmet', 'V-type helmet', '1', 'VALID', null, '0', '2017-12-13 11:20:07', '38699', '2017-12-13 11:28:12', '38699', 'N');
INSERT INTO stock VALUES ('98', 'en', 'China', '1302010000040000', '1302010000040002', '2', '0', 'Helmet', 'V-type helmet', '1', 'VALID', null, '0', '2017-12-13 11:20:07', '38699', '2017-12-13 11:28:12', '38699', 'N');
INSERT INTO stock VALUES ('99', 'en', 'China', '1302010000040000', '1302010000040003', '2', '0', 'Helmet', 'V-type helmet', '1', 'VALID', null, '0', '2017-12-13 11:20:07', '38699', '2017-12-13 11:28:13', '38699', 'N');
INSERT INTO stock VALUES ('100', 'en', 'China', '1302010000040000', '1302010000040004', '3', '0', 'Helmet', 'V-type helmet', '1', 'VALID', null, '0', '2017-12-13 11:20:07', '38699', '2017-12-13 11:28:13', '38699', 'N');
INSERT INTO stock VALUES ('101', 'en', 'China', '1302010000050000', '1302010000050001', '3', '0', 'Helmet', 'V-type breathable helmet', '1', 'VALID', null, '0', '2017-12-13 11:20:07', '38699', '2017-12-13 11:28:13', '38699', 'N');
INSERT INTO stock VALUES ('102', 'en', 'China', '1302010000050000', '1302010000050002', '3', '0', 'Helmet', 'V-type breathable helmet', '1', 'VALID', null, '0', '2017-12-13 11:20:07', '38699', '2017-12-13 11:28:49', '38699', 'N');
INSERT INTO stock VALUES ('103', 'en', 'China', '1302010000050000', '1302010000050003', '3', '0', 'Helmet', 'V-type breathable helmet', '1', 'VALID', null, '0', '2017-12-13 11:20:08', '38699', '2017-12-13 11:28:49', '38699', 'N');
INSERT INTO stock VALUES ('104', 'en', 'China', '1302010000050000', '1302010000050004', '3', '0', 'Helmet', 'V-type breathable helmet', '1', 'VALID', null, '0', '2017-12-13 11:20:08', '38699', '2017-12-13 11:28:49', '38699', 'N');
INSERT INTO stock VALUES ('105', 'en', 'China', '1302010000060000', '1302010000060001', '3', '0', 'Helmet', 'breathable helmet', '1', 'VALID', null, '0', '2017-12-13 11:20:08', '38699', '2017-12-13 17:19:24', '38699', 'N');
INSERT INTO stock VALUES ('106', 'en', 'China', '1302010000060000', '1302010000060002', '3', '0', 'Helmet', 'breathable helmet', '1', 'VALID', null, '0', '2017-12-13 11:20:08', '38699', '2017-12-13 17:19:24', '38699', 'N');
INSERT INTO stock VALUES ('107', 'en', 'China', '1302010000060000', '1302010000060003', '3', '0', 'Helmet', 'breathable helmet', '1', 'VALID', null, '0', '2017-12-13 11:20:08', '38699', '2017-12-13 17:19:24', '38699', 'N');
INSERT INTO stock VALUES ('108', 'en', 'China', '1302010000060000', '1302010000060004', '4', '0', 'Helmet', 'breathable helmet', '1', 'VALID', null, '0', '2017-12-13 11:20:08', '38699', '2017-12-13 17:19:24', '38699', 'N');
INSERT INTO stock VALUES ('109', 'en', 'China', '1302010000070000', '1302010000070001', '4', '0', 'Helmet', 'Round wide-brimmed helmet', '1', 'VALID', null, '0', '2017-12-13 11:20:09', '38699', '2017-12-13 17:19:24', '38699', 'N');
INSERT INTO stock VALUES ('110', 'en', 'China', '1302010000070000', '1302010000070002', '4', '0', 'Helmet', 'Round wide-brimmed helmet', '1', 'VALID', null, '0', '2017-12-13 11:20:09', '38699', '2017-12-13 17:19:24', '38699', 'N');
INSERT INTO stock VALUES ('111', 'en', 'China', '1302010000070000', '1302010000070003', '4', '0', 'Helmet', 'Round wide-brimmed helmet', '1', 'VALID', null, '0', '2017-12-13 11:20:09', '38699', '2017-12-13 17:19:24', '38699', 'N');
INSERT INTO stock VALUES ('112', 'en', 'China', '1302010000070000', '1302010000070004', '4', '0', 'Helmet', 'Round wide-brimmed helmet', '1', 'VALID', null, '0', '2017-12-13 11:20:09', '38699', '2017-12-13 17:19:23', '38699', 'N');
INSERT INTO stock VALUES ('113', 'en', 'China', '1302010000080000', '1302010000080001', '4', '0', 'Helmet visibility sunshade', 'Helmet visibility sunshade', '1', 'VALID', null, '0', '2017-12-13 11:20:09', '38699', '2017-12-13 17:19:23', '38699', 'N');
INSERT INTO stock VALUES ('114', 'en', 'China', '1302030000240000', '1302030000240001', '4', '0', 'Goodyear welt safety shoes', 'Goodyear welt safety shoes', '1', 'VALID', null, '0', '2017-12-13 11:20:09', '38699', '2017-12-13 17:19:23', '38699', 'N');
INSERT INTO stock VALUES ('115', 'en', 'China', '1302030000240000', '1302030000240002', '4', '0', 'Goodyear welt safety shoes', 'Goodyear welt safety shoes', '1', 'VALID', null, '0', '2017-12-13 11:20:10', '38699', '2017-12-13 17:19:23', '38699', 'N');
INSERT INTO stock VALUES ('116', 'en', 'China', '3308020000050000', '3308020000050002', '5', '0', 'Bottom Packing Tool', 'Bottom Packing Tool ', '1', 'VALID', null, '0', '2017-12-13 17:16:39', '38699', '2017-12-13 17:19:23', '38699', 'N');
INSERT INTO stock VALUES ('117', 'en', 'China', '3308020000050000', '3308020000050003', '5', '0', 'Bottom Packing Tool', 'Bottom Packing Tool ', '1', 'VALID', null, '0', '2017-12-13 17:16:40', '38699', '2017-12-13 17:19:23', '38699', 'N');
INSERT INTO stock VALUES ('118', 'en', 'China', '0107010000010000', '0107010000010001', '5', '0', '0319 Series JIC Tube Sleeve', '0319 Series JIC Tube Sleeve', '1', 'VALID', null, '0', '2017-12-13 19:15:00', '38699', '2017-12-13 19:18:04', '38699', 'N');
INSERT INTO stock VALUES ('119', 'en', 'China', '0407010000010000', '0407010000010001', '5', '0', 'Spring', 'Spring', '1', 'VALID', null, '0', '2017-12-13 19:15:00', '38699', '2017-12-13 19:18:04', '38699', 'N');
INSERT INTO stock VALUES ('120', 'en', 'China', '0407010000010000', '0407010000010002', '5', '0', 'Spring', 'Spring', '1', 'VALID', null, '0', '2017-12-13 19:15:00', '38699', '2017-12-13 19:18:04', '38699', 'N');
INSERT INTO stock VALUES ('121', 'en', 'China', '0407010000010000', '0407010000010003', '5', '0', 'Spring', 'Spring', '1', 'VALID', null, '0', '2017-12-13 19:15:00', '38699', '2017-12-13 19:18:04', '38699', 'N');
INSERT INTO stock VALUES ('122', 'en', 'China', '0407010000010000', '0407010000010004', '5', '0', 'Spring', 'Spring', '1', 'VALID', null, '0', '2017-12-13 19:17:59', '38699', '2017-12-13 19:18:05', '38699', 'N');
INSERT INTO stock VALUES ('123', 'en', 'China', '0407010000020000', '0407010000020001', '5', '0', 'Wafer Spring', 'Wafer Spring', '1', 'VALID', null, '0', '2017-12-13 19:17:59', '38699', '2017-12-13 19:18:05', '38699', 'N');
INSERT INTO stock VALUES ('124', 'en', 'China', '0407010000020000', '0407010000020002', '5', '0', 'Wafer Spring', 'Wafer Spring', '1', 'VALID', null, '0', '2017-12-13 19:17:59', '38699', '2017-12-13 19:18:05', '38699', 'N');
INSERT INTO stock VALUES ('125', 'en', 'China', '0407010000020000', '0407010000020003', '5', '0', 'Wafer Spring', 'Wafer Spring', '1', 'VALID', null, '0', '2017-12-13 19:18:00', '38699', '2017-12-13 19:18:05', '38699', 'N');
INSERT INTO stock VALUES ('126', 'en', 'China', '0505010000010000', '0505010000010001', '5', '0', 'Bitumen', 'Bitumen', '1', 'VALID', null, '0', '2017-12-13 19:18:00', '38699', '2017-12-13 19:18:05', '38699', 'N');
INSERT INTO stock VALUES ('127', 'en', 'China', '0505010000010000', '0505010000010002', '5', '0', 'Bitumen', 'Bitumen', '1', 'VALID', null, '0', '2017-12-13 19:18:00', '38699', '2017-12-13 19:18:05', '38699', 'N');
INSERT INTO stock VALUES ('128', 'en', 'China', '1002010000040000', '1002010000040001', '5', '0', 'COLO H01 Pattern Tire', 'COLO H01 Pattern Tire', '1', 'VALID', null, '0', '2017-12-13 19:18:00', '38699', '2017-12-13 19:18:05', '38699', 'N');
INSERT INTO stock VALUES ('129', 'en', 'China', '1002010000040000', '1002010000040002', '5', '0', 'COLO H01 Pattern Tire', 'COLO H01 Pattern Tire', '1', 'VALID', null, '0', '2017-12-13 19:18:00', '38699', '2017-12-13 19:18:05', '38699', 'N');
INSERT INTO stock VALUES ('130', 'en', 'China', '1002010000040000', '1002010000040003', '5', '0', 'COLO H01 Pattern Tire', 'COLO H01 Pattern Tire', '1', 'VALID', null, '0', '2017-12-13 19:18:00', '38699', '2017-12-13 19:18:05', '38699', 'N');

-- ----------------------------
-- Table structure for `stock_cost_price`
-- ----------------------------
DROP TABLE IF EXISTS `stock_cost_price`;
CREATE TABLE `stock_cost_price` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `country_bn` varchar(100) NOT NULL COMMENT '国家简称',
  `spu` varchar(32) DEFAULT NULL,
  `sku` varchar(32) NOT NULL COMMENT 'SKU',
  `supplier_id` bigint(20) NOT NULL COMMENT '供应商ID',
  `min_price` decimal(16,4) DEFAULT NULL COMMENT '最小展示单价',
  `max_price` decimal(16,4) DEFAULT NULL COMMENT '最大展示单价',
  `max_promotion_price` decimal(16,4) DEFAULT NULL COMMENT '最大促销单价',
  `min_promotion_price` decimal(16,4) DEFAULT NULL COMMENT '最小促销单价',
  `price_unit` varchar(20) DEFAULT NULL COMMENT '商品单位',
  `price_cur_bn` varchar(6) DEFAULT NULL COMMENT '货币单位',
  `price_symbol` varchar(3) DEFAULT NULL COMMENT '货币符号',
  `min_purchase_qty` int(11) DEFAULT '1' COMMENT '最小购买量',
  `max_purchase_qty` int(11) DEFAULT NULL COMMENT '最大购买量',
  `trade_terms_bn` varchar(6) DEFAULT NULL COMMENT '贸易术语简称',
  `price_validity_start` date DEFAULT NULL COMMENT '有效开始时间',
  `price_validity_end` date DEFAULT NULL COMMENT '价格有效期',
  `status` varchar(20) DEFAULT 'VALID' COMMENT '状态',
  `created_by` bigint(20) DEFAULT NULL COMMENT '创建人',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_by` bigint(20) DEFAULT NULL COMMENT '修改人',
  `updated_at` datetime DEFAULT NULL COMMENT '修改时间',
  `deleted_at` datetime DEFAULT NULL COMMENT '删除时间',
  `deleted_by` bigint(20) DEFAULT '0' COMMENT '删除人ID',
  `deleted_flag` char(1) DEFAULT 'N',
  PRIMARY KEY (`id`),
  KEY `sku` (`sku`),
  KEY `supplier_id` (`supplier_id`),
  KEY `country_bn` (`country_bn`)
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8 COMMENT='现货展示价格';

-- ----------------------------
-- Records of stock_cost_price
-- ----------------------------
INSERT INTO stock_cost_price VALUES ('1', 'China', '3101100000020000', '3101100000020001', '381', '48000.0000', '58000.0000', '47000.0000', '57000.0000', '支', 'CNY', '￥', '1', '100000', 'DAT', '2017-12-01', '2018-12-01', 'VALID', '38699', '2017-12-06 17:30:57', '38699', '2017-12-06 17:49:45', null, '0', 'N');
INSERT INTO stock_cost_price VALUES ('2', 'China', '3101100000020000', '3101100000010001', '381', '150.0000', '500.0000', null, null, '支', 'CNY', '￥', '1', '15', null, '2017-12-01', '2018-12-01', 'VALID', null, '2017-12-09 20:00:06', null, null, null, '0', 'N');
INSERT INTO stock_cost_price VALUES ('3', 'China', '1302010000090000', '1302010000090001', '21', '200.0000', null, null, null, '袋', 'CNY', '￥', '1', '10', null, '2017-12-18', '2018-12-01', 'VALID', '38699', '2017-12-13 11:20:05', null, null, null, '0', 'N');
INSERT INTO stock_cost_price VALUES ('4', 'China', '1302010000090000', '1302010000090002', '21', '16.0000', null, null, null, '袋', 'CNY', '￥', '1', null, null, null, '2018-12-01', 'VALID', '38699', '2017-12-13 11:20:05', null, null, null, '0', 'N');
INSERT INTO stock_cost_price VALUES ('5', 'China', '1302010000090000', '1302010000090003', '21', '16.0000', null, null, null, '袋', 'CNY', '￥', '1', null, null, null, '2018-12-01', 'VALID', '38699', '2017-12-13 11:20:05', null, null, null, '0', 'N');
INSERT INTO stock_cost_price VALUES ('6', 'China', '1302010000090000', '1302010000090004', '21', '16.0000', null, null, null, '袋', 'CNY', '￥', '1', null, null, null, '2018-12-01', 'VALID', '38699', '2017-12-13 11:20:05', null, null, null, '0', 'N');
INSERT INTO stock_cost_price VALUES ('7', 'China', '1302010000020000', '1302010000020001', '21', '24.0000', null, null, null, '袋', 'CNY', '￥', '1', null, null, null, '2018-12-01', 'VALID', '38699', '2017-12-13 11:20:06', null, null, null, '0', 'N');
INSERT INTO stock_cost_price VALUES ('8', 'China', '1302010000020000', '1302010000020002', '21', '24.0000', null, null, null, '袋', 'CNY', '￥', '1', null, null, null, '2018-12-01', 'VALID', '38699', '2017-12-13 11:20:06', null, null, null, '0', 'N');
INSERT INTO stock_cost_price VALUES ('9', 'China', '1302010000020000', '1302010000020003', '21', '24.0000', null, null, null, '袋', 'CNY', '￥', '1', null, null, null, '2018-12-01', 'VALID', '38699', '2017-12-13 11:20:06', null, null, null, '0', 'N');
INSERT INTO stock_cost_price VALUES ('10', 'China', '1302010000020000', '1302010000020004', '21', '24.0000', null, null, null, '袋', 'CNY', '￥', '1', null, null, null, '2018-12-01', 'VALID', '38699', '2017-12-13 11:20:06', null, null, null, '0', 'N');
INSERT INTO stock_cost_price VALUES ('11', 'China', '1302010000030000', '1302010000030001', '21', '16.0000', null, null, null, '袋', 'CNY', '￥', '1', null, null, null, '2018-12-01', 'VALID', '38699', '2017-12-13 11:20:06', null, null, null, '0', 'N');
INSERT INTO stock_cost_price VALUES ('12', 'China', '1302010000030000', '1302010000030002', '21', '16.0000', null, null, null, '袋', 'CNY', '￥', '1', null, null, null, '2018-12-01', 'VALID', '38699', '2017-12-13 11:20:06', null, null, null, '0', 'N');
INSERT INTO stock_cost_price VALUES ('13', 'China', '1302010000030000', '1302010000030003', '21', '16.0000', null, null, null, '袋', 'CNY', '￥', '1', null, null, null, '2018-12-01', 'VALID', '38699', '2017-12-13 11:20:06', null, null, null, '0', 'N');
INSERT INTO stock_cost_price VALUES ('14', 'China', '1302010000030000', '1302010000030004', '21', '16.0000', null, null, null, '袋', 'CNY', '￥', '1', null, null, null, '2018-12-01', 'VALID', '38699', '2017-12-13 11:20:07', null, null, null, '0', 'N');
INSERT INTO stock_cost_price VALUES ('15', 'China', '1302010000040000', '1302010000040001', '21', '18.0000', null, null, null, '袋', 'CNY', '￥', '1', null, null, null, '2018-12-01', 'VALID', '38699', '2017-12-13 11:20:07', null, null, null, '0', 'N');
INSERT INTO stock_cost_price VALUES ('16', 'China', '1302010000040000', '1302010000040002', '21', '18.0000', null, null, null, '袋', 'CNY', '￥', '1', null, null, null, '2018-12-01', 'VALID', '38699', '2017-12-13 11:20:07', null, null, null, '0', 'N');
INSERT INTO stock_cost_price VALUES ('17', 'China', '1302010000040000', '1302010000040003', '21', '18.0000', null, null, null, '袋', 'CNY', '￥', '1', null, null, null, '2018-12-01', 'VALID', '38699', '2017-12-13 11:20:07', null, null, null, '0', 'N');
INSERT INTO stock_cost_price VALUES ('18', 'China', '1302010000040000', '1302010000040004', '21', '18.0000', null, null, null, '袋', 'CNY', '￥', '1', null, null, null, '2018-12-01', 'VALID', '38699', '2017-12-13 11:20:07', null, null, null, '0', 'N');
INSERT INTO stock_cost_price VALUES ('19', 'China', '1302010000050000', '1302010000050001', '21', '25.0000', null, null, null, '袋', 'CNY', '￥', '1', null, null, null, '2018-12-01', 'VALID', '38699', '2017-12-13 11:20:07', null, null, null, '0', 'N');
INSERT INTO stock_cost_price VALUES ('20', 'China', '1302010000050000', '1302010000050002', '21', '25.0000', null, null, null, '袋', 'CNY', '￥', '1', null, null, null, '2018-12-01', 'VALID', '38699', '2017-12-13 11:20:07', null, null, null, '0', 'N');
INSERT INTO stock_cost_price VALUES ('21', 'China', '1302010000050000', '1302010000050003', '21', '25.0000', null, null, null, '袋', 'CNY', '￥', '1', null, null, null, '2018-12-01', 'VALID', '38699', '2017-12-13 11:20:08', null, null, null, '0', 'N');
INSERT INTO stock_cost_price VALUES ('22', 'China', '1302010000050000', '1302010000050004', '21', '25.0000', null, null, null, '袋', 'CNY', '￥', '1', null, null, null, '2018-12-01', 'VALID', '38699', '2017-12-13 11:20:08', null, null, null, '0', 'N');
INSERT INTO stock_cost_price VALUES ('23', 'China', '1302010000060000', '1302010000060001', '21', '6.5000', null, null, null, '袋', 'CNY', '￥', '1', null, null, null, '2018-12-01', 'VALID', '38699', '2017-12-13 11:20:08', null, null, null, '0', 'N');
INSERT INTO stock_cost_price VALUES ('24', 'China', '1302010000060000', '1302010000060002', '21', '6.5000', null, null, null, '袋', 'CNY', '￥', '1', null, null, null, '2018-12-01', 'VALID', '38699', '2017-12-13 11:20:08', null, null, null, '0', 'N');
INSERT INTO stock_cost_price VALUES ('25', 'China', '1302010000060000', '1302010000060003', '21', '6.5000', null, null, null, '袋', 'CNY', '￥', '1', null, null, null, '2018-12-01', 'VALID', '38699', '2017-12-13 11:20:08', null, null, null, '0', 'N');
INSERT INTO stock_cost_price VALUES ('26', 'China', '1302010000060000', '1302010000060004', '21', '6.5000', null, null, null, '袋', 'CNY', '￥', '1', null, null, null, '2018-12-01', 'VALID', '38699', '2017-12-13 11:20:09', null, null, null, '0', 'N');
INSERT INTO stock_cost_price VALUES ('27', 'China', '1302010000070000', '1302010000070001', '21', '12.0000', null, null, null, '袋', 'CNY', '￥', '1', null, null, null, '2018-12-01', 'VALID', '38699', '2017-12-13 11:20:09', null, null, null, '0', 'N');
INSERT INTO stock_cost_price VALUES ('28', 'China', '1302010000070000', '1302010000070002', '21', '12.0000', null, null, null, '袋', 'CNY', '￥', '1', null, null, null, '2018-12-01', 'VALID', '38699', '2017-12-13 11:20:09', null, null, null, '0', 'N');
INSERT INTO stock_cost_price VALUES ('29', 'China', '1302010000070000', '1302010000070003', '21', '12.0000', null, null, null, '袋', 'CNY', '￥', '1', null, null, null, '2018-12-01', 'VALID', '38699', '2017-12-13 11:20:09', null, null, null, '0', 'N');
INSERT INTO stock_cost_price VALUES ('30', 'China', '1302010000070000', '1302010000070004', '21', '12.0000', null, null, null, '袋', 'CNY', '￥', '1', null, null, null, '2018-12-01', 'VALID', '38699', '2017-12-13 11:20:09', null, null, null, '0', 'N');
INSERT INTO stock_cost_price VALUES ('31', 'China', '1302010000080000', '1302010000080001', '21', '5.3000', null, null, null, '袋', 'CNY', '￥', '1', null, null, null, '2018-12-01', 'VALID', '38699', '2017-12-13 11:20:09', null, null, null, '0', 'N');
INSERT INTO stock_cost_price VALUES ('32', 'China', '1302030000240000', '1302030000240001', '21', '245.0000', null, null, null, '箱', 'CNY', '￥', '1', null, null, null, '2018-12-01', 'VALID', '38699', '2017-12-13 11:20:09', null, null, null, '0', 'N');
INSERT INTO stock_cost_price VALUES ('33', 'China', '1302030000240000', '1302030000240002', '21', '245.0000', null, null, null, '箱', 'CNY', '￥', '1', null, null, null, '2018-12-01', 'VALID', '38699', '2017-12-13 11:20:10', null, null, null, '0', 'N');
INSERT INTO stock_cost_price VALUES ('34', 'China', '3308020000050000', '3308020000050002', '372', '93174.0000', null, null, null, '箱', 'CNY', '￥', '1', null, null, null, '2018-12-01', 'VALID', '38699', '2017-12-13 17:16:39', null, null, null, '0', 'N');
INSERT INTO stock_cost_price VALUES ('35', 'China', '3308020000050000', '3308020000050003', '372', '104821.0000', null, null, null, '箱', 'CNY', '￥', '1', null, null, null, '2018-12-01', 'VALID', '38699', '2017-12-13 17:16:40', null, null, null, '0', 'N');
INSERT INTO stock_cost_price VALUES ('36', 'China', '0107010000010000', '0107010000010001', '212', '5.7000', null, null, null, '箱', 'USD', '$', '1', null, null, null, '2018-12-01', 'VALID', '38699', '2017-12-13 19:15:00', null, null, null, '0', 'N');
INSERT INTO stock_cost_price VALUES ('37', 'China', '0407010000010000', '0407010000010001', '49', '1.5700', null, null, null, '箱', 'USD', '$', '1', null, null, null, '2018-12-01', 'VALID', '38699', '2017-12-13 19:15:00', null, null, null, '0', 'N');
INSERT INTO stock_cost_price VALUES ('38', 'China', '0407010000010000', '0407010000010002', '49', '2.2300', null, null, null, '箱', 'USD', '$', '1', null, null, null, '2018-12-01', 'VALID', '38699', '2017-12-13 19:15:00', null, null, null, '0', 'N');
INSERT INTO stock_cost_price VALUES ('39', 'China', '0407010000010000', '0407010000010003', '49', '1.5700', null, null, null, '箱', 'USD', '$', '1', null, null, null, '2018-12-01', 'VALID', '38699', '2017-12-13 19:15:00', null, null, null, '0', 'N');
INSERT INTO stock_cost_price VALUES ('40', 'China', '0407010000010000', '0407010000010004', '49', '26.3800', null, null, null, '箱', 'USD', '$', '1', null, null, null, '2018-12-01', 'VALID', '38699', '2017-12-13 19:17:59', null, null, null, '0', 'N');
INSERT INTO stock_cost_price VALUES ('41', 'China', '0407010000020000', '0407010000020001', '49', '43.2800', null, null, null, '箱', 'USD', '$', '1', null, null, null, '2018-12-01', 'VALID', '38699', '2017-12-13 19:17:59', null, null, null, '0', 'N');
INSERT INTO stock_cost_price VALUES ('42', 'China', '0407010000020000', '0407010000020002', '49', '31.3400', null, null, null, '箱', 'USD', '$', '1', null, null, null, '2018-12-01', 'VALID', '38699', '2017-12-13 19:17:59', null, null, null, '0', 'N');
INSERT INTO stock_cost_price VALUES ('43', 'China', '0407010000020000', '0407010000020003', '49', '42.5900', null, null, null, '箱', 'USD', '$', '1', null, null, null, '2018-12-01', 'VALID', '38699', '2017-12-13 19:18:00', null, null, null, '0', 'N');
INSERT INTO stock_cost_price VALUES ('44', 'China', '0505010000010000', '0505010000010001', '374', '0.0000', null, null, null, '桶', 'USD', '$', '1', null, null, null, '2018-12-01', 'VALID', '38699', '2017-12-13 19:18:00', null, null, null, '0', 'N');
INSERT INTO stock_cost_price VALUES ('45', 'China', '0505010000010000', '0505010000010002', '374', '0.0000', null, null, null, '桶', 'USD', '$', '1', null, null, null, '2018-12-01', 'VALID', '38699', '2017-12-13 19:18:00', null, null, null, '0', 'N');
INSERT INTO stock_cost_price VALUES ('46', 'China', '1002010000040000', '1002010000040001', '12', '16.6800', null, null, null, '箱', 'USD', '$', '1', null, null, null, '2018-12-01', 'VALID', '38699', '2017-12-13 19:18:00', null, null, null, '0', 'N');
INSERT INTO stock_cost_price VALUES ('47', 'China', '1002010000040000', '1002010000040002', '12', '18.6200', null, null, null, '箱', 'USD', '$', '1', null, null, null, '2018-12-01', 'VALID', '38699', '2017-12-13 19:18:00', null, null, null, '0', 'N');
INSERT INTO stock_cost_price VALUES ('48', 'China', '1002010000040000', '1002010000040003', '12', '19.3000', null, null, null, '箱', 'USD', '$', '1', null, null, null, '2018-12-01', 'VALID', '38699', '2017-12-13 19:18:00', null, null, null, '0', 'N');
INSERT INTO stock_cost_price VALUES ('49', 'China', '1302010000090000', '1302010000090001', '21', '150.0000', null, null, null, '袋', 'CNY', '￥', '11', null, null, '2017-12-19', null, 'VALID', null, '2017-12-19 21:34:01', null, null, null, '0', 'N');

-- ----------------------------
-- Table structure for `stock_country`
-- ----------------------------
DROP TABLE IF EXISTS `stock_country`;
CREATE TABLE `stock_country` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `country_bn` varchar(96) NOT NULL COMMENT '国家简码',
  `show_flag` char(1) NOT NULL DEFAULT 'Y' COMMENT '显示标志 Y表示显示',
  `display_position` varchar(100) DEFAULT NULL COMMENT '显示位置',
  `created_at` datetime NOT NULL COMMENT '添加时间',
  `created_by` bigint(20) NOT NULL COMMENT '添加人',
  `updated_at` datetime DEFAULT NULL COMMENT '维护时间',
  `deleted_at` datetime DEFAULT NULL COMMENT '删除时间',
  `deleted_by` bigint(20) DEFAULT '0' COMMENT '删除人ID',
  `updated_by` bigint(20) DEFAULT '0' COMMENT '维护人',
  PRIMARY KEY (`id`),
  KEY `country_bn` (`country_bn`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='现货与国家关系';

-- ----------------------------
-- Records of stock_country
-- ----------------------------
INSERT INTO stock_country VALUES ('1', 'China', 'Y', 'LEFT', '2017-12-06 14:24:59', '38699', '2017-12-06 14:36:01', null, '0', '38699');

-- ----------------------------
-- Table structure for `stock_country_ads`
-- ----------------------------
DROP TABLE IF EXISTS `stock_country_ads`;
CREATE TABLE `stock_country_ads` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `lang` char(2) DEFAULT 'en' COMMENT '语言',
  `country_bn` varchar(96) NOT NULL COMMENT '国家简称',
  `sort_order` smallint(6) DEFAULT '0' COMMENT '排序',
  `img_url` varchar(200) DEFAULT NULL COMMENT '广告地址',
  `img_name` varchar(200) DEFAULT '' COMMENT '广告名称',
  `link` varchar(200) DEFAULT NULL COMMENT '广告链接',
  `group` varchar(100) DEFAULT 'BANNER' COMMENT '广告分组',
  `status` varchar(20) DEFAULT 'VALID' COMMENT '状态',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `created_by` bigint(20) NOT NULL DEFAULT '0' COMMENT '创建人',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `updated_by` bigint(20) DEFAULT NULL COMMENT '更新人ID',
  `deleted_flag` char(1) DEFAULT 'N' COMMENT '删除标志',
  `deleted_at` datetime DEFAULT NULL COMMENT '创建时间',
  `deleted_by` bigint(20) DEFAULT '0' COMMENT '创建人',
  PRIMARY KEY (`id`),
  KEY `country_bn` (`country_bn`),
  KEY `lang` (`lang`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='现货广告';

-- ----------------------------
-- Records of stock_country_ads
-- ----------------------------
INSERT INTO stock_country_ads VALUES ('1', 'en', 'China', '0', 'group1/M00/00/7D/rBISxFozpm6AM-2RAAMMDzn30rA052.jpg', 'index_banner', null, 'BANNER', 'VALID', '2017-12-15 18:48:53', '38699', null, null, 'Y', '0000-00-00 00:00:00', '0');
INSERT INTO stock_country_ads VALUES ('2', 'en', 'China', '0', 'group1/M00/00/7D/rBISxFozpm6AM-2RAAMMDzn30rA052.jpg', 'index_banner', null, 'BANNER', 'VALID', '2017-12-15 18:48:58', '38699', null, null, 'Y', '2017-12-15 19:04:04', '38699');
INSERT INTO stock_country_ads VALUES ('3', 'en', 'China', '0', 'group1/M00/00/81/rBISxFo0qhCAXbW8AAHxhkP0ydI885.jpg', 'nowgoods_banner_01.jpg', null, 'BANNER', 'VALID', '2017-12-16 13:10:07', '38699', null, null, 'N', null, '0');
INSERT INTO stock_country_ads VALUES ('4', 'en', 'China', '0', 'group1/M00/00/81/rBISxFo0rNKABAf1AAH741tkdvQ945.png', 'index_advert_01.png', null, 'HOT', 'VALID', '2017-12-16 13:20:00', '38699', null, null, 'N', null, '0');
INSERT INTO stock_country_ads VALUES ('6', 'en', 'China', '0', 'group1/M00/00/81/rBISxFo0rRGAIVf-AAC1stziL9k738.png', 'index_advert_02', '/en/rfq/find.html', 'HOT', 'VALID', '2017-12-16 13:28:14', '38699', null, null, 'N', null, '0');

-- ----------------------------
-- Table structure for `stock_floor`
-- ----------------------------
DROP TABLE IF EXISTS `stock_floor`;
CREATE TABLE `stock_floor` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `lang` char(6) NOT NULL DEFAULT 'en' COMMENT '语言',
  `floor_name` varchar(100) NOT NULL COMMENT '楼层名称',
  `country_bn` varchar(100) NOT NULL COMMENT '国家简码',
  `onshelf_flag` char(1) NOT NULL DEFAULT 'N' COMMENT '上下架标志',
  `description` tinytext COMMENT '描述',
  `sort_order` smallint(6) NOT NULL DEFAULT '0' COMMENT '排序',
  `sku_count` smallint(6) NOT NULL DEFAULT '0' COMMENT '产品数量',
  `created_at` datetime NOT NULL COMMENT '添加时间',
  `created_by` bigint(20) NOT NULL COMMENT '添加人',
  `updated_at` datetime DEFAULT NULL COMMENT '维护时间',
  `deleted_at` datetime DEFAULT NULL COMMENT '删除时间',
  `deleted_by` bigint(20) DEFAULT '0' COMMENT '删除人ID',
  `updated_by` bigint(20) DEFAULT '0' COMMENT '维护人',
  `deleted_flag` char(1) DEFAULT 'N' COMMENT '删除标志',
  PRIMARY KEY (`id`),
  KEY `country_bn` (`country_bn`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='楼层';

-- ----------------------------
-- Records of stock_floor
-- ----------------------------
INSERT INTO stock_floor VALUES ('1', 'en', 'petroleum special pipes', 'China', 'Y', 'petroleum special pipes', '99', '3', '2017-12-13 15:26:29', '38699', '2017-12-15 17:52:20', null, '0', '38699', 'N');
INSERT INTO stock_floor VALUES ('2', 'en', 'petroleum and its products', 'China', 'Y', 'petroleum and its products', '99', '5', '2017-12-13 15:28:44', '38699', null, null, '0', '0', 'N');
INSERT INTO stock_floor VALUES ('3', 'en', 'petroleum special equipment', 'China', 'Y', 'petroleum special equipment', '99', '5', '2017-12-13 15:30:39', '38699', null, null, '0', '0', 'N');
INSERT INTO stock_floor VALUES ('4', 'en', 'petroleum special tools', 'China', 'Y', 'petroleum special tools', '99', '5', '2017-12-13 15:32:56', '38699', null, null, '0', '0', 'N');
INSERT INTO stock_floor VALUES ('5', 'en', 'labour protection', 'China', 'Y', 'labour protection', '99', '5', '2017-12-13 15:35:20', '38699', '2017-12-13 19:18:05', null, '0', '38699', 'N');

-- ----------------------------
-- Table structure for `stock_floor_ads`
-- ----------------------------
DROP TABLE IF EXISTS `stock_floor_ads`;
CREATE TABLE `stock_floor_ads` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `lang` char(2) DEFAULT 'en' COMMENT '语言',
  `country_bn` varchar(96) NOT NULL COMMENT '国家简称',
  `floor_id` bigint(20) DEFAULT '0' COMMENT '楼层ID',
  `sort_order` smallint(6) DEFAULT '0' COMMENT '排序',
  `img_url` varchar(200) DEFAULT NULL COMMENT '图片地址',
  `img_name` varchar(200) DEFAULT '' COMMENT '图片名称',
  `link` varchar(200) DEFAULT NULL COMMENT '广告链接',
  `group` varchar(100) DEFAULT 'BACKGROUP' COMMENT '图片分组 BACKGROUP 背景图片',
  `status` varchar(20) DEFAULT 'VALID' COMMENT '状态',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `created_by` bigint(20) NOT NULL DEFAULT '0' COMMENT '创建人',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `deleted_at` datetime DEFAULT NULL COMMENT '删除时间',
  `deleted_by` bigint(20) DEFAULT '0' COMMENT '删除人ID',
  `updated_by` bigint(20) DEFAULT NULL COMMENT '更新人ID',
  `deleted_flag` char(1) DEFAULT 'N' COMMENT '删除标志',
  PRIMARY KEY (`id`),
  KEY `country_bn` (`country_bn`),
  KEY `lang` (`lang`),
  KEY `floor_id` (`floor_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='现货楼层广告';

-- ----------------------------
-- Records of stock_floor_ads
-- ----------------------------
INSERT INTO stock_floor_ads VALUES ('1', 'en', 'China', '1', '0', 'group1/M00/00/7D/rBISxFozpm6AM-2RAAMMDzn30rA052.jpg', 'index_banner', null, 'BANNER', 'VALID', '2017-12-15 19:23:18', '38699', null, null, '0', null, 'N');

-- ----------------------------
-- Table structure for `stock_floor_keyword`
-- ----------------------------
DROP TABLE IF EXISTS `stock_floor_keyword`;
CREATE TABLE `stock_floor_keyword` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `lang` char(2) DEFAULT 'en' COMMENT '语言',
  `country_bn` varchar(96) NOT NULL COMMENT '国家简称',
  `floor_id` bigint(20) DEFAULT '0' COMMENT '楼层ID',
  `keyword` varchar(200) NOT NULL COMMENT '关键词',
  `sort_order` smallint(6) DEFAULT '0' COMMENT '排序',
  `status` varchar(20) DEFAULT 'VALID' COMMENT '状态',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `created_by` bigint(20) NOT NULL DEFAULT '0' COMMENT '创建人',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `deleted_at` datetime DEFAULT NULL COMMENT '删除时间',
  `deleted_by` bigint(20) DEFAULT '0' COMMENT '删除人ID',
  `updated_by` bigint(20) DEFAULT NULL COMMENT '更新人ID',
  `deleted_flag` char(1) DEFAULT 'N' COMMENT '删除标志',
  PRIMARY KEY (`id`),
  KEY `country_bn` (`country_bn`),
  KEY `lang` (`lang`),
  KEY `floor_id` (`floor_id`)
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8 COMMENT='楼层关键词';

-- ----------------------------
-- Records of stock_floor_keyword
-- ----------------------------
INSERT INTO stock_floor_keyword VALUES ('1', 'en', 'China', '1', 'kelly', '0', 'VALID', '2017-12-13 15:28:12', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_keyword VALUES ('2', 'en', 'China', '1', 'drill pipe', '0', 'VALID', '2017-12-13 15:28:12', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_keyword VALUES ('3', 'en', 'China', '1', 'drill collar', '0', 'VALID', '2017-12-13 15:28:12', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_keyword VALUES ('4', 'en', 'China', '1', 'casing pipe', '0', 'VALID', '2017-12-13 15:28:12', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_keyword VALUES ('5', 'en', 'China', '1', 'oil tubing', '0', 'VALID', '2017-12-13 15:28:12', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_keyword VALUES ('6', 'en', 'China', '2', 'solvent oil', '0', 'VALID', '2017-12-13 15:29:47', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_keyword VALUES ('7', 'en', 'China', '2', 'lubricant', '0', 'VALID', '2017-12-13 15:29:47', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_keyword VALUES ('8', 'en', 'China', '2', 'standard oil', '0', 'VALID', '2017-12-13 15:29:47', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_keyword VALUES ('9', 'en', 'China', '2', 'petroleum wax', '0', 'VALID', '2017-12-13 15:29:47', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_keyword VALUES ('10', 'en', 'China', '2', 'petroleum asphalt', '0', 'VALID', '2017-12-13 15:29:47', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_keyword VALUES ('11', 'en', 'China', '3', 'petroleum special equipment', '0', 'VALID', '2017-12-13 15:32:33', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_keyword VALUES ('12', 'en', 'China', '3', 'drilling&workover rig', '0', 'VALID', '2017-12-13 15:32:33', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_keyword VALUES ('13', 'en', 'China', '3', 'drilling rig', '0', 'VALID', '2017-12-13 15:32:33', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_keyword VALUES ('14', 'en', 'China', '3', 'workover rig', '0', 'VALID', '2017-12-13 15:32:33', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_keyword VALUES ('15', 'en', 'China', '3', 'auxiliary equipment', '0', 'VALID', '2017-12-13 15:32:33', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_keyword VALUES ('16', 'en', 'China', '3', 'mast&substructure', '0', 'VALID', '2017-12-13 15:32:33', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_keyword VALUES ('17', 'en', 'China', '3', 'mud equipment', '0', 'VALID', '2017-12-13 15:32:33', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_keyword VALUES ('18', 'en', 'China', '3', 'manifold', '0', 'VALID', '2017-12-13 15:32:33', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_keyword VALUES ('19', 'en', 'China', '4', 'drilling bit', '0', 'VALID', '2017-12-13 15:34:49', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_keyword VALUES ('20', 'en', 'China', '4', 'coring bit', '0', 'VALID', '2017-12-13 15:34:49', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_keyword VALUES ('21', 'en', 'China', '4', 'bit nozzle', '0', 'VALID', '2017-12-13 15:34:49', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_keyword VALUES ('22', 'en', 'China', '4', 'bit joint', '0', 'VALID', '2017-12-13 15:34:49', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_keyword VALUES ('23', 'en', 'China', '4', 'crown blank of PDC bit with steel body', '0', 'VALID', '2017-12-13 15:34:49', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_keyword VALUES ('24', 'en', 'China', '4', 'drill pipe joint', '0', 'VALID', '2017-12-13 15:34:49', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_keyword VALUES ('25', 'en', 'China', '4', 'workblank of drill pipe joint', '0', 'VALID', '2017-12-13 15:34:49', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_keyword VALUES ('26', 'en', 'China', '4', 'slip', '0', 'VALID', '2017-12-13 15:34:49', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_keyword VALUES ('35', 'en', 'China', '5', 'work clothes', '0', 'VALID', '2017-12-13 15:36:38', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_keyword VALUES ('36', 'en', 'China', '5', 'fire suits', '0', 'VALID', '2017-12-13 15:36:39', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_keyword VALUES ('37', 'en', 'China', '5', 'hats', '0', 'VALID', '2017-12-13 15:36:39', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_keyword VALUES ('38', 'en', 'China', '5', 'gloves', '0', 'VALID', '2017-12-13 15:36:39', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_keyword VALUES ('39', 'en', 'China', '5', 'footgear', '0', 'VALID', '2017-12-13 15:36:39', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_keyword VALUES ('40', 'en', 'China', '5', 'cover products', '0', 'VALID', '2017-12-13 15:36:39', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_keyword VALUES ('41', 'en', 'China', '5', 'mat products', '0', 'VALID', '2017-12-13 15:36:39', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_keyword VALUES ('42', 'en', 'China', '5', 'eye protection products', '0', 'VALID', '2017-12-13 15:36:39', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_keyword VALUES ('43', 'en', 'China', '1', '010100', '0', 'VALID', '2017-12-15 21:02:58', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_keyword VALUES ('44', 'en', 'China', '1', '010200', '0', 'VALID', '2017-12-15 21:02:58', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_keyword VALUES ('45', 'en', 'China', '1', '010300', '0', 'VALID', '2017-12-15 21:02:58', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_keyword VALUES ('46', 'en', 'China', '1', '010400', '0', 'VALID', '2017-12-15 21:02:58', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_keyword VALUES ('47', 'en', 'China', '1', '010500', '0', 'VALID', '2017-12-15 21:02:58', '38699', null, null, '0', null, 'N');

-- ----------------------------
-- Table structure for `stock_floor_show_cat`
-- ----------------------------
DROP TABLE IF EXISTS `stock_floor_show_cat`;
CREATE TABLE `stock_floor_show_cat` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `lang` char(2) DEFAULT 'en' COMMENT '语言',
  `country_bn` varchar(96) NOT NULL COMMENT '国家简称',
  `floor_id` bigint(20) DEFAULT '0' COMMENT '楼层ID',
  `cat_name` varchar(200) DEFAULT NULL,
  `cat_no` varchar(10) NOT NULL COMMENT '关键词',
  `sort_order` smallint(6) DEFAULT '0' COMMENT '排序',
  `status` varchar(20) DEFAULT 'VALID' COMMENT '状态',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `created_by` bigint(20) NOT NULL DEFAULT '0' COMMENT '创建人',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `deleted_at` datetime DEFAULT NULL COMMENT '删除时间',
  `deleted_by` bigint(20) DEFAULT '0' COMMENT '删除人ID',
  `updated_by` bigint(20) DEFAULT NULL COMMENT '更新人ID',
  `deleted_flag` char(1) DEFAULT 'N' COMMENT '删除标志',
  PRIMARY KEY (`id`),
  KEY `country_bn` (`country_bn`),
  KEY `lang` (`lang`),
  KEY `floor_id` (`floor_id`),
  KEY `cat_no` (`cat_no`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8 COMMENT='楼层分类';

-- ----------------------------
-- Records of stock_floor_show_cat
-- ----------------------------
INSERT INTO stock_floor_show_cat VALUES ('1', 'en', 'China', '1', 'kelly ', '010100', '0', 'VALID', '2017-12-13 15:28:18', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_show_cat VALUES ('2', 'en', 'China', '1', 'drill pipe', '010200', '0', 'VALID', '2017-12-13 15:28:18', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_show_cat VALUES ('3', 'en', 'China', '1', 'drill collar', '010300', '0', 'VALID', '2017-12-13 15:28:18', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_show_cat VALUES ('4', 'en', 'China', '1', 'casing pipe', '010400', '0', 'VALID', '2017-12-13 15:28:18', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_show_cat VALUES ('5', 'en', 'China', '1', 'oil tubing', '010500', '0', 'VALID', '2017-12-13 15:28:19', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_show_cat VALUES ('6', 'en', 'China', '2', 'rail', '020100', '0', 'VALID', '2017-12-13 15:30:00', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_show_cat VALUES ('7', 'en', 'China', '2', 'commercial steel section', '020200', '0', 'VALID', '2017-12-13 15:30:00', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_show_cat VALUES ('8', 'en', 'China', '2', 'wire rod', '020300', '0', 'VALID', '2017-12-13 15:30:00', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_show_cat VALUES ('9', 'en', 'China', '2', 'shape steel ', '020400', '0', 'VALID', '2017-12-13 15:30:00', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_show_cat VALUES ('10', 'en', 'China', '2', 'steel sheet and strip', '020500', '0', 'VALID', '2017-12-13 15:30:00', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_show_cat VALUES ('11', 'en', 'China', '3', 'drilling&workover rig', '140100', '0', 'VALID', '2017-12-13 15:31:07', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_show_cat VALUES ('12', 'en', 'China', '3', 'auxiliary equipment', '140200', '0', 'VALID', '2017-12-13 15:31:07', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_show_cat VALUES ('13', 'en', 'China', '3', 'special vehicle', '140300', '0', 'VALID', '2017-12-13 15:31:07', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_show_cat VALUES ('14', 'en', 'China', '3', 'well testing equipment', '140400', '0', 'VALID', '2017-12-13 15:31:07', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_show_cat VALUES ('15', 'en', 'China', '3', 'oil & gas extraction equipment', '140500', '0', 'VALID', '2017-12-13 15:31:07', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_show_cat VALUES ('16', 'en', 'China', '4', 'bit', '330100', '0', 'VALID', '2017-12-13 15:33:46', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_show_cat VALUES ('17', 'en', 'China', '4', 'drill pipe joint and workblank', '330200', '0', 'VALID', '2017-12-13 15:33:46', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_show_cat VALUES ('18', 'en', 'China', '4', 'well drilling/workover tools', '330300', '0', 'VALID', '2017-12-13 15:33:46', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_show_cat VALUES ('19', 'en', 'China', '4', 'drilling fishing tools', '330400', '0', 'VALID', '2017-12-13 15:33:46', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_show_cat VALUES ('20', 'en', 'China', '4', 'coring tools', '330500', '0', 'VALID', '2017-12-13 15:33:46', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_show_cat VALUES ('21', 'en', 'China', '4', 'cementing tools', '330600', '0', 'VALID', '2017-12-13 15:33:46', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_show_cat VALUES ('22', 'en', 'China', '4', 'directional well tools', '330700', '0', 'VALID', '2017-12-13 15:33:46', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_show_cat VALUES ('23', 'en', 'China', '4', 'pilot production tools', '330800', '0', 'VALID', '2017-12-13 15:33:46', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_show_cat VALUES ('31', 'en', 'China', '5', 'protective clothing', '130100', '0', 'VALID', '2017-12-13 15:36:36', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_show_cat VALUES ('32', 'en', 'China', '5', 'hats/gloves/shoes', '130200', '0', 'VALID', '2017-12-13 15:36:36', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_show_cat VALUES ('33', 'en', 'China', '5', 'protective equipment', '130300', '0', 'VALID', '2017-12-13 15:36:36', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_show_cat VALUES ('34', 'en', 'China', '5', 'medical/health protective equipment', '130400', '0', 'VALID', '2017-12-13 15:36:36', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_show_cat VALUES ('35', 'en', 'China', '5', 'industrial clothing', '130500', '0', 'VALID', '2017-12-13 15:36:36', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_show_cat VALUES ('36', 'en', 'China', '5', 'cleaning product', '130600', '0', 'VALID', '2017-12-13 15:36:36', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_show_cat VALUES ('37', 'en', 'China', '5', 'bedclothes', '130700', '0', 'VALID', '2017-12-13 15:36:36', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_show_cat VALUES ('38', 'en', 'China', '1', 'kelly ', '010100', '0', 'VALID', '2017-12-15 21:02:32', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_show_cat VALUES ('39', 'en', 'China', '1', 'drill pipe', '010200', '0', 'VALID', '2017-12-15 21:02:32', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_show_cat VALUES ('40', 'en', 'China', '1', 'drill collar', '010300', '0', 'VALID', '2017-12-15 21:02:32', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_show_cat VALUES ('41', 'en', 'China', '1', 'casing pipe', '010400', '0', 'VALID', '2017-12-15 21:02:32', '38699', null, null, '0', null, 'N');
INSERT INTO stock_floor_show_cat VALUES ('42', 'en', 'China', '1', 'oil tubing', '010500', '0', 'VALID', '2017-12-15 21:02:32', '38699', null, null, '0', null, 'N');
