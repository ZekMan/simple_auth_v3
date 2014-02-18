-- phpMyAdmin SQL Dump
-- version 3.5.0-rc2
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Фев 19 2014 г., 04:52
-- Версия сервера: 5.5.33-0+wheezy1
-- Версия PHP: 5.4.4-14+deb7u5

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- База данных: `test`
--

-- --------------------------------------------------------

--
-- Структура таблицы `session`
--

CREATE TABLE IF NOT EXISTS `session` (
  `id_sess` int(5) NOT NULL AUTO_INCREMENT,
  `id_user` int(5) NOT NULL,
  `code_sess` varchar(15) NOT NULL,
  `user_agent_sess` varchar(255) NOT NULL,
  `date_sess` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `used_sess` int(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_sess`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id_user` int(5) NOT NULL AUTO_INCREMENT,
  `login_user` varchar(60) NOT NULL,
  `passwd_user` varchar(255) NOT NULL,
  `mail_user` varchar(255) NOT NULL,
  `sex_user` int(1) NOT NULL,
  `key_user` varchar(10) NOT NULL,
  PRIMARY KEY (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
