-- phpMyAdmin SQL Dump
-- version 4.0.10.7
-- http://www.phpmyadmin.net
--
-- Servidor: localhost
-- Tiempo de generación: 10-03-2015 a las 22:05:39
-- Versión del servidor: 5.5.40-cll
-- Versión de PHP: 5.4.23

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Base de datos: `candidaturas`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cnd_candidatos`
--

CREATE TABLE IF NOT EXISTS `cnd_candidatos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text NOT NULL,
  `url_foto` varchar(255) NOT NULL,
  `url_detalle` varchar(255) NOT NULL,
  `encabeza` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cnd_config`
--

CREATE TABLE IF NOT EXISTS `cnd_config` (
  `name` varchar(128) NOT NULL,
  `value` text,
  UNIQUE KEY `nombre` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cnd_log`
--

CREATE TABLE IF NOT EXISTS `cnd_log` (
  `level` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `activity` text NOT NULL,
  `timestmap` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip` varchar(16) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cnd_logged_users`
--

CREATE TABLE IF NOT EXISTS `cnd_logged_users` (
  `user_id` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip` varchar(16) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cnd_login_attempts`
--

CREATE TABLE IF NOT EXISTS `cnd_login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int(11) NOT NULL,
  `ip` varchar(16) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cnd_newpass`
--

CREATE TABLE IF NOT EXISTS `cnd_newpass` (
  `user_id` int(11) NOT NULL,
  `hash` varchar(255) NOT NULL,
  `creation` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cnd_users`
--

CREATE TABLE IF NOT EXISTS `cnd_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `apellidos` varchar(255) NOT NULL,
  `nif` varchar(20) NOT NULL,
  `direccion` varchar(255) NOT NULL,
  `cp` varchar(10) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `nacimiento` varchar(16) NOT NULL,
  `voted` tinyint(1) NOT NULL DEFAULT '0',
  `presencial` tinyint(1) NOT NULL DEFAULT '0',
  `provincia` varchar(255) DEFAULT NULL,
  `municipio` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nif` (`nif`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cnd_votes`
--

CREATE TABLE IF NOT EXISTS `cnd_votes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `first` int(11) DEFAULT NULL,
  `v1` int(11) DEFAULT NULL,
  `v2` int(11) DEFAULT NULL,
  `v3` int(11) DEFAULT NULL,
  `v4` int(11) DEFAULT NULL,
  `v5` int(11) DEFAULT NULL,
  `v6` int(11) DEFAULT NULL,
  `v7` int(11) DEFAULT NULL,
  `v8` int(11) DEFAULT NULL,
  `data` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
