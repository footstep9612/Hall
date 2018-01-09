/*
Navicat MySQL Data Transfer

Source Server         : 172.18.18.193_3306
Source Server Version : 50505
Source Host           : 172.18.18.193:3306
Source Database       : erui_goods

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2017-11-05 09:47:59
*/
use erui_goods;
SET FOREIGN_KEY_CHECKS=0;
ALTER TABLE `product` add COLUMN relation_flag varchar(32) NOT NULL default 'N' COMMENT '关联标志' after recommend_flag;
ALTER TABLE `show_cat` add COLUMN spu_count bigint(20) NOT NULL default 0 COMMENT 'SPU数量' after sort_order;
UPDATE product set relation_flag='Y' where spu in(SELECT spu from  erui_stock.product_relation where lang='en') and deleted_flag='N' and lang='en';


