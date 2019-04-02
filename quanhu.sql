/*
Navicat MySQL Data Transfer

Source Server         : 10.20.1.70
Source Server Version : 50556
Source Host           : 10.20.1.70:3306
Source Database       : quanhu

Target Server Type    : MYSQL
Target Server Version : 50556
File Encoding         : 65001

Date: 2019-03-25 09:21:54
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for days
-- ----------------------------
DROP TABLE IF EXISTS `days`;
CREATE TABLE `days` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pv` int(11) DEFAULT '0',
  `uv` int(11) DEFAULT '0',
  `days` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `days_index` (`days`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for history
-- ----------------------------
DROP TABLE IF EXISTS `history`;
CREATE TABLE `history` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pv` int(11) DEFAULT '0',
  `all` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for hours
-- ----------------------------
DROP TABLE IF EXISTS `hours`;
CREATE TABLE `hours` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pv` int(11) DEFAULT '0',
  `uv` int(11) DEFAULT '0',
  `hours` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hours_index` (`hours`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1306211 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for months
-- ----------------------------
DROP TABLE IF EXISTS `months`;
CREATE TABLE `months` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uv` int(11) DEFAULT '0',
  `pv` int(11) DEFAULT '0',
  `months` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `months_index` (`months`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for qhap
-- ----------------------------
DROP TABLE IF EXISTS `qhap`;
CREATE TABLE `qhap` (
  `id` int(11) DEFAULT NULL,
  `name` varchar(10) DEFAULT NULL,
  `lon` varchar(30) DEFAULT NULL,
  `lat` varchar(30) DEFAULT NULL,
  `mac` varchar(30) DEFAULT NULL,
  `point` varchar(10) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for today
-- ----------------------------
DROP TABLE IF EXISTS `today`;
CREATE TABLE `today` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `apMac` varchar(255) DEFAULT NULL,
  `vendorId` varchar(255) DEFAULT NULL,
  `bssid` varchar(255) DEFAULT NULL,
  `radioType` varchar(255) DEFAULT NULL,
  `channel` varchar(255) DEFAULT NULL,
  `isAssociated` varchar(255) DEFAULT NULL,
  `timestamp` datetime DEFAULT NULL,
  `muType` varchar(255) DEFAULT NULL,
  `rssi` varchar(255) DEFAULT NULL,
  `noiseFloor` varchar(255) DEFAULT NULL,
  `dataRate` varchar(255) DEFAULT NULL,
  `MPDUFlags` varchar(255) DEFAULT NULL,
  `muMac` varchar(255) DEFAULT NULL,
  `frameControl` varchar(255) DEFAULT NULL,
  `sequenceControl` varchar(255) DEFAULT NULL,
  `gettime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `apMac_index` (`apMac`(191)) USING BTREE,
  KEY `gettime_index` (`gettime`) USING BTREE,
  KEY `mumac_index` (`muMac`(191)) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=116286528 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for today_copy
-- ----------------------------
DROP TABLE IF EXISTS `today_copy`;
CREATE TABLE `today_copy` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `apMac` varchar(255) DEFAULT NULL,
  `vendorId` varchar(255) DEFAULT NULL,
  `bssid` varchar(255) DEFAULT NULL,
  `radioType` varchar(255) DEFAULT NULL,
  `channel` varchar(255) DEFAULT NULL,
  `isAssociated` varchar(255) DEFAULT NULL,
  `timestamp` datetime DEFAULT NULL,
  `muType` varchar(255) DEFAULT NULL,
  `rssi` varchar(255) DEFAULT NULL,
  `noiseFloor` varchar(255) DEFAULT NULL,
  `dataRate` varchar(255) DEFAULT NULL,
  `MPDUFlags` varchar(255) DEFAULT NULL,
  `muMac` varchar(255) DEFAULT NULL,
  `frameControl` varchar(255) DEFAULT NULL,
  `sequenceControl` varchar(255) DEFAULT NULL,
  `gettime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `apMac_index` (`apMac`(191)) USING BTREE,
  KEY `gettime_index` (`gettime`) USING BTREE,
  KEY `mumac_index` (`muMac`(191)) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for user_basic
-- ----------------------------
DROP TABLE IF EXISTS `user_basic`;
CREATE TABLE `user_basic` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mu_mac` varchar(255) DEFAULT NULL,
  `logout_time` datetime DEFAULT NULL,
  `login_time` datetime DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `long_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mumac_index1` (`mu_mac`(191)) USING BTREE,
  KEY `logout_time_index` (`logout_time`) USING BTREE,
  KEY `login_time_index` (`logout_time`) USING BTREE,
  KEY `status_index` (`status`(191)) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4965066 DEFAULT CHARSET=utf8mb4;
