/*
Navicat MySQL Data Transfer

Source Server         : 172.18.18.193_3306
Source Server Version : 50505
Source Host           : 172.18.18.193:3306
Source Database       : erui_stock

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2017-12-28 14:07:11
*/

SET FOREIGN_KEY_CHECKS=0;
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
INSERT INTO home_country_nav VALUES ('1', 'en', 'China', '0', 'PPE', '/en/product/index/keyword/PPE.html', 'VALID', '2017-12-15 21:34:40', '38699', '2017-12-16 10:04:25', '2017-12-15 21:48:20', '38699', '38699', 'N');
INSERT INTO home_country_nav VALUES ('2', 'en', 'China', '0', 'Mud pump', '/en/product/index/keyword/Mud+pump.html', 'VALID', '2017-12-15 21:35:19', '38699', '2017-12-16 10:03:22', null, '0', '38699', 'N');
INSERT INTO home_country_nav VALUES ('3', 'en', 'China', '0', 'Cylinder liner', '/en/product/index/keyword/Cylinder+liner.html', 'VALID', '2017-12-15 21:35:55', '38699', '2017-12-16 10:03:47', null, '0', '38699', 'N');
