/*
Navicat MySQL Data Transfer

Source Server         : 172.18.18.193_3306
Source Server Version : 50505
Source Host           : 172.18.18.193:3306
Source Database       : erui_stock

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2017-12-28 11:12:58
*/

SET FOREIGN_KEY_CHECKS=0;
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
INSERT INTO home_floor VALUES ('1', 'en', 'Petroleum special tools', 'China', 'Y', '石油专用工具', '0', '0', '2017-12-15 19:49:32', '38699', null, null, '0', '0', 'N');
INSERT INTO home_floor VALUES ('2', 'en', 'Labor insurance, security', 'China', 'Y', '劳保、安防', '0', '8', '2017-12-15 19:49:51', '38699', '2017-12-15 19:52:34', null, '0', '38699', 'N');
INSERT INTO home_floor VALUES ('3', 'en', 'Petroleum equipments and accessories', 'China', 'Y', '石油设备及配件', '0', '0', '2017-12-15 19:50:05', '38699', null, null, '0', '0', 'N');
INSERT INTO home_floor VALUES ('4', 'en', 'Instrument and meter', 'China', 'Y', '仪器仪表', '0', '0', '2017-12-15 19:50:14', '38699', null, null, '0', '0', 'N');
INSERT INTO home_floor VALUES ('5', 'en', 'Standard parts and hardware sundries', 'China', 'Y', '标准件及五金杂项', '0', '0', '2017-12-15 19:50:25', '38699', null, null, '0', '0', 'N');
