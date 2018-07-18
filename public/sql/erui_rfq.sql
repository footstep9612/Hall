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
use erui_rfq;
ALTER TABLE `inquiry` add COLUMN logi_quote_flag char(1) DEFAULT NULL COMMENT 'N 表示不需要 Y  表示需要 null 表示未确定 需物流报价标志' after logi_check_id;
ALTER TABLE `inquiry` add COLUMN loss_rfq_flag char(1)  DEFAULT NULL COMMENT '失单标志 N 未失单 Y  表示失单 null 表示未确定' after logi_quote_flag;
ALTER TABLE `inquiry` add COLUMN loss_rfq_reason text DEFAULT NULL  COMMENT '失单原因' after logi_quote_flag;
ALTER TABLE `inquiry_item` add COLUMN material_cat_no varchar(32) DEFAULT NULL  COMMENT '物料分类编码' after category;
ALTER TABLE `quote_item` add COLUMN org_id bigint(20) DEFAULT NULL  COMMENT '事业部ID' after inquiry_item_id;
