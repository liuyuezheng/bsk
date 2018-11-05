/*
Navicat MySQL Data Transfer

Source Server         : lyz
Source Server Version : 50553
Source Host           : localhost:3306
Source Database       : websocket

Target Server Type    : MYSQL
Target Server Version : 50553
File Encoding         : 65001

Date: 2018-09-14 15:09:30
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for answer
-- ----------------------------
DROP TABLE IF EXISTS `answer`;
CREATE TABLE `answer` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '答案id',
  `connect` text COLLATE utf8_bin COMMENT '选择内容',
  `topic_id` int(10) unsigned DEFAULT NULL COMMENT '题目id',
  `type` int(2) unsigned DEFAULT NULL COMMENT '答案确认：1正确 0错误',
  `creattime` int(15) unsigned DEFAULT NULL COMMENT '时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='选择项';

-- ----------------------------
-- Table structure for dan
-- ----------------------------
DROP TABLE IF EXISTS `dan`;
CREATE TABLE `dan` (
  `dan_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '段位id',
  `dan_name` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT '段位名',
  `dan_count` int(15) unsigned DEFAULT NULL COMMENT 'pizza量限制值',
  PRIMARY KEY (`dan_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='段位表';

-- ----------------------------
-- Table structure for relation
-- ----------------------------
DROP TABLE IF EXISTS `relation`;
CREATE TABLE `relation` (
  `relation_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) DEFAULT NULL COMMENT '用户id',
  `topic_id` int(10) DEFAULT NULL COMMENT '题目id',
  `answer_id` int(10) DEFAULT NULL COMMENT '选择id',
  `points` int(15) unsigned DEFAULT '0' COMMENT '得分',
  `users_id` int(10) unsigned DEFAULT '0' COMMENT '合作id',
  `status` int(2) unsigned DEFAULT NULL COMMENT '游戏模式：1: 1-1 2: 2-2',
  `createtime` int(15) unsigned DEFAULT NULL COMMENT '时间',
  `room_id` int(10) unsigned DEFAULT NULL COMMENT '房间id',
  PRIMARY KEY (`relation_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='关系表';

-- ----------------------------
-- Table structure for room
-- ----------------------------
DROP TABLE IF EXISTS `room`;
CREATE TABLE `room` (
  `room_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '房间id',
  `time` int(15) unsigned DEFAULT NULL COMMENT '创建时间',
  `count` int(10) unsigned DEFAULT NULL COMMENT '人数',
  `type` int(2) unsigned DEFAULT '1' COMMENT 'pk模式：1：1vs1  2：2vs2',
  PRIMARY KEY (`room_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- ----------------------------
-- Table structure for topic
-- ----------------------------
DROP TABLE IF EXISTS `topic`;
CREATE TABLE `topic` (
  `topic_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '题目id',
  `title` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT '题目',
  `answer_id` int(10) unsigned DEFAULT NULL COMMENT '答案id',
  `createtime` int(10) unsigned DEFAULT NULL COMMENT '时间',
  PRIMARY KEY (`topic_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='题目表';

-- ----------------------------
-- Table structure for user
-- ----------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '账号id',
  `username` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT '用户名',
  `createtime` int(15) unsigned DEFAULT NULL COMMENT '创建时间',
  `dan_id` int(10) unsigned DEFAULT '1' COMMENT '段位id',
  `count` int(15) unsigned DEFAULT '0' COMMENT 'pizza量',
  `ranking` int(10) unsigned DEFAULT NULL COMMENT '排名',
  `points` int(15) unsigned DEFAULT '0' COMMENT '积分',
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='用户表';
