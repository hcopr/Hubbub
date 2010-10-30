-- phpMyAdmin SQL Dump
-- version 2.11.10.1
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.3
-- Generation Time: Oct 30, 2010 at 03:17 PM
-- Server version: 5.1.45
-- PHP Version: 4.4.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `db276568_4`
--

-- --------------------------------------------------------

--
-- Table structure for table `h2_auditlog`
--

CREATE TABLE `h2_auditlog` (
  `l_key` int(11) NOT NULL AUTO_INCREMENT,
  `l_op` varchar(32) NOT NULL,
  `l_user` varchar(32) NOT NULL,
  `l_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `l_data` longtext NOT NULL,
  `l_returncode` varchar(6) NOT NULL,
  `l_result` varchar(4) NOT NULL,
  `l_reason` varchar(128) NOT NULL,
  PRIMARY KEY (`l_key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `h2_connections`
--

CREATE TABLE `h2_connections` (
  `c_key` int(11) NOT NULL AUTO_INCREMENT,
  `c_from` int(11) NOT NULL,
  `c_to` int(11) NOT NULL,
  `c_status` varchar(10) NOT NULL,
  PRIMARY KEY (`c_key`),
  KEY `c_from` (`c_from`),
  KEY `c_to` (`c_to`),
  KEY `c_status` (`c_status`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `h2_entities`
--

CREATE TABLE `h2_entities` (
  `_key` int(11) NOT NULL AUTO_INCREMENT,
  `user` varchar(128) NOT NULL,
  `url` varchar(128) NOT NULL,
  `server` varchar(128) NOT NULL,
  `type` varchar(12) NOT NULL DEFAULT 'person',
  `pic` varchar(128) NOT NULL,
  `name` varchar(128) NOT NULL,
  `_local` varchar(1) NOT NULL,
  PRIMARY KEY (`_key`),
  KEY `_username` (`user`),
  KEY `_local` (`_local`),
  KEY `url` (`url`),
  KEY `server` (`server`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `h2_idaccounts`
--

CREATE TABLE `h2_idaccounts` (
  `ia_key` int(11) NOT NULL AUTO_INCREMENT,
  `ia_type` varchar(16) NOT NULL,
  `ia_url` varchar(250) NOT NULL,
  `ia_user` int(11) NOT NULL,
  `ia_comments` varchar(200) NOT NULL,
  PRIMARY KEY (`ia_key`),
  KEY `ia_user` (`ia_user`),
  KEY `ia_url` (`ia_url`),
  KEY `ia_type` (`ia_type`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `h2_index`
--

CREATE TABLE `h2_index` (
  `i_userkey` int(11) NOT NULL,
  `i_msgkey` int(11) NOT NULL,
  PRIMARY KEY (`i_userkey`,`i_msgkey`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `h2_messages`
--

CREATE TABLE `h2_messages` (
  `m_key` int(11) NOT NULL AUTO_INCREMENT,
  `m_id` varchar(64) NOT NULL,
  `m_parent` int(11) NOT NULL,
  `m_owner` int(11) NOT NULL,
  `m_author` int(11) NOT NULL,
  `m_created` datetime NOT NULL,
  `m_changed` datetime NOT NULL,
  `m_type` varchar(16) NOT NULL,
  `m_data` longblob NOT NULL,
  `m_compression` smallint(6) NOT NULL,
  `m_deleted` varchar(1) NOT NULL DEFAULT 'N',
  `m_votehash` varchar(32) NOT NULL,
  PRIMARY KEY (`m_key`),
  KEY `m_id` (`m_id`),
  KEY `m_owner` (`m_owner`),
  KEY `m_author` (`m_author`),
  KEY `m_created` (`m_created`),
  KEY `m_changed` (`m_changed`),
  KEY `m_deleted` (`m_deleted`),
  KEY `m_votehash` (`m_votehash`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `h2_nvstore`
--

CREATE TABLE `h2_nvstore` (
  `nv_name` varchar(32) NOT NULL,
  `nv_value` longtext NOT NULL,
  PRIMARY KEY (`nv_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `h2_servers`
--

CREATE TABLE `h2_servers` (
  `s_key` int(11) NOT NULL AUTO_INCREMENT,
  `s_url` varchar(128) NOT NULL,
  `s_name` varchar(128) NOT NULL,
  `s_key_out` varchar(32) NOT NULL,
  `s_key_in` varchar(32) NOT NULL,
  `s_newkey_out` varchar(32) NOT NULL,
  `s_status` varchar(32) NOT NULL,
  PRIMARY KEY (`s_key`),
  KEY `s_url` (`s_url`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `h2_users`
--

CREATE TABLE `h2_users` (
  `u_key` int(11) NOT NULL AUTO_INCREMENT,
  `u_name` varchar(128) NOT NULL,
  `u_email` varchar(128) NOT NULL,
  `u_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `u_l10n` varchar(5) NOT NULL,
  `u_settings` longtext NOT NULL,
  `u_sessionkey` varchar(32) NOT NULL,
  `u_entity` int(11) NOT NULL,
  PRIMARY KEY (`u_key`),
  KEY `u_entity` (`u_entity`),
  KEY `u_sessionkey` (`u_sessionkey`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `h2_votes`
--

CREATE TABLE `h2_votes` (
  `v_msg` int(11) NOT NULL,
  `v_choice` varchar(32) NOT NULL,
  `v_text` varchar(128) NOT NULL,
  `v_voters` varchar(250) NOT NULL,
  `v_count` int(11) NOT NULL,
  PRIMARY KEY (`v_msg`,`v_choice`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

