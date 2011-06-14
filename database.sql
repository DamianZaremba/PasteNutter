-- phpMyAdmin SQL Dump
-- version 3.3.5
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jun 14, 2011 at 10:23 AM
-- Server version: 5.5.12
-- PHP Version: 5.3.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `PasteNutter`
--

-- --------------------------------------------------------

--
-- Table structure for table `irc_users`
--

CREATE TABLE IF NOT EXISTS `irc_users` (
  `nick` varchar(50) NOT NULL,
  `host` varchar(2000) NOT NULL,
  `ping` bigint(60) NOT NULL DEFAULT '0',
  UNIQUE KEY `nick` (`nick`),
  KEY `host` (`host`(767))
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `pastes`
--

CREATE TABLE IF NOT EXISTS `pastes` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `user` varchar(50) DEFAULT NULL,
  `syntax` varchar(200) DEFAULT NULL,
  `paste` text NOT NULL,
  `views` bigint(200) NOT NULL,
  `downloads` bigint(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=31 ;

