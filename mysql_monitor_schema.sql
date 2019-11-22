/*
SQLyog Professional v10.42 
MySQL - 8.0.18 : Database - sql_db
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`sql_db` /*!40100 DEFAULT CHARACTER SET utf8 */ /*!80016 DEFAULT ENCRYPTION='N' */;

USE `sql_db`;

/*Table structure for table `mysql_repl_status` */

DROP TABLE IF EXISTS `mysql_repl_status`;

CREATE TABLE `mysql_repl_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `server_id` int(11) DEFAULT NULL,
  `host` varchar(30) DEFAULT NULL,
  `dbname` varchar(100) DEFAULT NULL,
  `port` int(11) DEFAULT NULL,
  `role` tinyint(2) DEFAULT NULL,
  `is_live` tinyint(4) DEFAULT NULL,
  `read_only` varchar(10) DEFAULT NULL,
  `gtid_mode` varchar(10) DEFAULT NULL,
  `Master_Host` varchar(30) DEFAULT NULL,
  `Master_Port` varchar(100) DEFAULT NULL,
  `Slave_IO_Running` varchar(20) DEFAULT NULL,
  `Slave_SQL_Running` varchar(20) DEFAULT NULL,
  `Seconds_Behind_Master` varchar(20) DEFAULT NULL,
  `Master_Log_File` varchar(30) DEFAULT NULL,
  `Relay_Master_Log_File` varchar(30) DEFAULT NULL,
  `Read_Master_Log_Pos` varchar(30) DEFAULT NULL,
  `Exec_Master_Log_Pos` varchar(30) DEFAULT NULL,
  `Last_IO_Error` varchar(500) DEFAULT NULL,
  `Last_SQL_Error` varchar(500) DEFAULT NULL,
  `create_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `IX_h_d_p_c` (`host`,`dbname`,`port`,`create_time`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

/*Table structure for table `mysql_status` */

DROP TABLE IF EXISTS `mysql_status`;

CREATE TABLE `mysql_status` (
  `id` bigint(10) unsigned NOT NULL AUTO_INCREMENT,
  `host` varchar(30) DEFAULT NULL,
  `dbname` varchar(100) DEFAULT NULL,
  `port` int(11) DEFAULT NULL,
  `role` tinyint(4) DEFAULT NULL,
  `is_live` tinyint(4) DEFAULT NULL,
  `max_connections` int(11) DEFAULT NULL,
  `threads_connected` int(11) DEFAULT NULL,
  `qps_select` int(11) DEFAULT NULL,
  `qps_insert` int(11) DEFAULT NULL,
  `qps_update` int(11) DEFAULT NULL,
  `qps_delete` int(11) DEFAULT NULL,
  `runtime` int(11) DEFAULT NULL,
  `db_version` varchar(100) DEFAULT NULL,
  `create_time` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IX_h_d_p_c` (`host`,`dbname`,`port`,`create_time`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

/*Table structure for table `mysql_status_history` */

DROP TABLE IF EXISTS `mysql_status_history`;

CREATE TABLE `mysql_status_history` (
  `id` bigint(10) unsigned NOT NULL AUTO_INCREMENT,
  `host` varchar(30) DEFAULT NULL,
  `dbname` varchar(100) DEFAULT NULL,
  `port` int(11) DEFAULT NULL,
  `role` tinyint(4) DEFAULT NULL,
  `is_live` tinyint(4) DEFAULT NULL,
  `max_connections` int(11) DEFAULT NULL,
  `threads_connected` int(11) DEFAULT NULL,
  `qps_select` int(11) DEFAULT NULL,
  `qps_insert` int(11) DEFAULT NULL,
  `qps_update` int(11) DEFAULT NULL,
  `qps_delete` int(11) DEFAULT NULL,
  `runtime` int(11) DEFAULT NULL,
  `db_version` varchar(100) DEFAULT NULL,
  `create_time` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `create_time` (`create_time`),
  KEY `IX_h_d_p_c` (`host`,`dbname`,`port`,`create_time`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

/*Table structure for table `mysql_status_info` */

DROP TABLE IF EXISTS `mysql_status_info`;

CREATE TABLE `mysql_status_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '输入被监控MySQL的IP地址',
  `dbname` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '输入被监控MySQL的数据库名',
  `user` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '输入被监控MySQL的用户名',
  `pwd` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '输入被监控MySQL的密码',
  `port` int(11) DEFAULT NULL COMMENT '输入被监控MySQL的端口号',
  `monitor` tinyint(4) DEFAULT '1' COMMENT '0为关闭监控;1为开启监控',
  `send_mail` tinyint(4) DEFAULT '1' COMMENT '0为关闭邮件报警;1为开启邮件报警',
  `send_mail_to_list` varchar(255) DEFAULT NULL COMMENT '邮件人列表',
  `send_weixin` tinyint(4) DEFAULT '1' COMMENT '0为关闭微信报警;1为开启微信报警',
  `send_weixin_to_list` varchar(100) DEFAULT NULL COMMENT '微信公众号',
  `alarm_threads_running` tinyint(4) DEFAULT NULL COMMENT '记录活动连接数告警信息，1为已记录',
  `threshold_alarm_threads_running` tinyint(4) DEFAULT NULL COMMENT '设置连接数阀值',
  `alarm_repl_status` tinyint(4) DEFAULT NULL COMMENT '记录主从复制告警信息，1为记录主从状态，3为记录主从延迟状态',
  `threshold_warning_repl_delay` tinyint(4) DEFAULT NULL COMMENT '设置主从复制延迟阀值',
  PRIMARY KEY (`id`),
  KEY `IX_i_d_p` (`ip`,`dbname`,`port`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='监控信息表';

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
