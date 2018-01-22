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
use erui_stock;

ALTER TABLE `stock` add COLUMN model varchar(255) DEFAULT NULL COMMENT '型号' after show_name;


