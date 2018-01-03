/*
Navicat MySQL Data Transfer

Source Server         : 172.18.18.193_3306
Source Server Version : 50505
Source Host           : 172.18.18.193:3306
Source Database       : erui_stock

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2017-12-28 16:04:39
*/

SET FOREIGN_KEY_CHECKS=0;
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
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8 COMMENT='首页广告图片';

-- ----------------------------
-- Records of home_country_ads
-- ----------------------------
INSERT INTO home_country_ads VALUES ('7', 'en', 'China', '0', 'BANNER', 'group1/M00/00/7D/rBISxFozpm6AM-2RAAMMDzn30rA052.jpg', 'index_banner', null, 'VALID', '2017-12-15 18:42:48', '38699', null, null, 'N', '0', null);
INSERT INTO home_country_ads VALUES ('8', 'en', 'China', '0', 'BANNER', 'group1/M00/00/7D/rBISxFozpm6AM-2RAAMMDzn30rA052.jpg', 'index_banner', null, 'VALID', '2017-12-15 18:43:03', '38699', null, null, 'Y', '38699', '2017-12-15 18:52:28');
INSERT INTO home_country_ads VALUES ('9', 'en', 'China', '0', 'BANNER', 'group1/M00/00/81/rBISxFo0qhCAXbW8AAHxhkP0ydI885.jpg', 'nowgoods_banner_01.jpg', null, 'VALID', '2017-12-16 13:15:22', '38699', null, null, 'N', '0', null);
INSERT INTO home_country_ads VALUES ('10', 'en', 'China', '0', 'HOT', 'group1/M00/00/81/rBISxFo0rNKABAf1AAH741tkdvQ945.png', 'index_advert_01.png', null, 'VALID', '2017-12-16 13:19:42', '38699', null, null, 'N', '0', null);
INSERT INTO home_country_ads VALUES ('11', 'en', 'China', '0', 'HOT', 'group1/M00/00/81/rBISxFo0rRGAIVf-AAC1stziL9k738.png', 'index_advert_02', '/en/rfq/find.html', 'VALID', '2017-12-16 13:28:04', '38699', null, null, 'N', '0', null);
INSERT INTO home_country_ads VALUES ('12', 'en', 'Argentina', '0', 'BANNER', 'group1/M00/00/7D/rBISxFozpm6AM-2RAAMMDzn30rA052.jpg', 'index_banner', null, 'VALID', '2017-12-15 18:42:48', '38699', null, null, 'N', '0', null);
INSERT INTO home_country_ads VALUES ('13', 'en', 'Argentina', '0', 'BANNER', 'group1/M00/00/7D/rBISxFozpm6AM-2RAAMMDzn30rA052.jpg', 'index_banner', null, 'VALID', '2017-12-15 18:43:03', '38699', null, null, 'Y', '38699', '2017-12-15 18:52:28');
INSERT INTO home_country_ads VALUES ('14', 'en', 'Argentina', '0', 'BANNER', 'group1/M00/00/81/rBISxFo0qhCAXbW8AAHxhkP0ydI885.jpg', 'nowgoods_banner_01.jpg', null, 'VALID', '2017-12-16 13:15:22', '38699', null, null, 'N', '0', null);
INSERT INTO home_country_ads VALUES ('15', 'en', 'Argentina', '0', 'HOT', 'group1/M00/00/81/rBISxFo0rNKABAf1AAH741tkdvQ945.png', 'index_advert_01.png', null, 'VALID', '2017-12-16 13:19:42', '38699', null, null, 'N', '0', null);
INSERT INTO home_country_ads VALUES ('16', 'en', 'Argentina', '0', 'HOT', 'group1/M00/00/81/rBISxFo0rRGAIVf-AAC1stziL9k738.png', 'index_advert_02', '/en/rfq/find.html', 'VALID', '2017-12-16 13:28:04', '38699', null, null, 'N', '0', null);
INSERT INTO home_country_ads VALUES ('17', 'en', 'China', '0', 'ICO', 'group1/M00/01/8A/rBFgyFpEo7iAH34tAAAlSE5AF7M959.jpg', 'screen pipe', '/en/product/index/show_cat_no/010601.html', 'VALID', '2017-12-28 15:57:40', '38699', null, null, 'N', '0', null);
INSERT INTO home_country_ads VALUES ('18', 'en', 'China', '0', 'ICO', 'group1/M00/01/8A/rBFgyFpEpE-AD6DWAAAjQFFERKo539.jpg', 'steel tube', '/en/product/index/show_cat_no/020601.html', 'VALID', '2017-12-28 15:58:20', '38699', null, null, 'N', '0', null);
INSERT INTO home_country_ads VALUES ('19', 'en', 'China', '0', 'ICO', 'group1/M00/01/8A/rBFgyFpEpG2AOfq2AAAntrZIoqE931.jpg', 'tire', '/en/product/index/show_cat_no/100201.html', 'VALID', '2017-12-28 15:58:53', '38699', null, null, 'N', '0', null);
INSERT INTO home_country_ads VALUES ('20', 'en', 'China', '0', 'ICO', 'group1/M00/01/8A/rBFgyFpEpI-AQ0fcAAAmSzpGB-k330.jpg', 'gloves', '/en/product/index/show_cat_no/130202.html', 'VALID', '2017-12-28 15:59:25', '38699', null, null, 'N', '0', null);
INSERT INTO home_country_ads VALUES ('21', 'en', 'China', '0', 'ICO', 'group1/M00/01/8A/rBFgyFpEpLqAP_36AAAiDaQOKC4005.jpg', 'anti-gas protection equipment', '/en/product/index/show_cat_no/130313.html', 'VALID', '2017-12-28 16:00:14', '38699', null, null, 'N', '0', null);
INSERT INTO home_country_ads VALUES ('22', 'en', 'China', '0', 'ICO', 'group1/M00/01/8A/rBFgyFpEpOCAUIfmAAAmVWb3Kfs895.jpg', 'shale shaker', '/en/product/index/show_cat_no/140242.html', 'VALID', '2017-12-28 16:00:50', '38699', null, null, 'N', '0', null);
INSERT INTO home_country_ads VALUES ('23', 'en', 'China', '0', 'ICO', 'group1/M00/01/8A/rBFgyFpEpQSAZ-67AAAirsVMfZ4905.jpg', 'disc brake', '/en/product/index/show_cat_no/140295.html', 'VALID', '2017-12-28 16:01:25', '38699', null, null, 'N', '0', null);
INSERT INTO home_country_ads VALUES ('24', 'en', 'China', '0', 'ICO', 'group1/M00/01/8A/rBFgyFpEpSiAWdArAAAiSoCTNlE658.jpg', 'downhole logging tools', '/en/product/index/show_cat_no/260202.html', 'VALID', '2017-12-28 16:02:03', '38699', null, null, 'N', '0', null);
INSERT INTO home_country_ads VALUES ('25', 'en', 'China', '0', 'ICO', 'group1/M00/01/8B/rBFgyFpEpUyAI8gYAAAlZ85VIMQ367.jpg', 'drill bit', '/en/product/index/show_cat_no/280300.html', 'VALID', '2017-12-28 16:02:40', '38699', null, null, 'N', '0', null);
INSERT INTO home_country_ads VALUES ('26', 'en', 'China', '0', 'ICO', 'group1/M00/01/8B/rBFgyFpEpXCAcB9_AAAmvw3DWS0892.jpg', 'bearing', '/en/product/index/show_cat_no/300000.html', 'VALID', '2017-12-28 16:03:14', '38699', null, null, 'N', '0', null);
INSERT INTO home_country_ads VALUES ('27', 'en', 'China', '0', 'ICO', 'group1/M00/01/8B/rBFgyFpEpZOATGpiAAAlpnlGSkk275.jpg', 'cementing tool', '/en/product/index/show_cat_no/330601.html', 'VALID', '2017-12-28 16:03:48', '38699', null, null, 'N', '0', null);
INSERT INTO home_country_ads VALUES ('28', 'en', 'China', '0', 'ICO', 'group1/M00/01/8B/rBFgyFpEpbCAKQcvAAAkErLN6EE082.jpg', 'sealing elements', '/en/product/index/show_cat_no/350000.html', 'VALID', '2017-12-28 16:04:23', '38699', null, null, 'N', '0', null);
