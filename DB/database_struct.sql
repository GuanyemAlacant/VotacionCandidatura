-- phpMyAdmin SQL Dump
-- version 3.3.9.2
-- http://www.phpmyadmin.net
--
-- Servidor: localhost
-- Tiempo de generación: 07-02-2015 a las 10:28:25
-- Versión del servidor: 5.5.9
-- Versión de PHP: 5.3.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Base de datos: `candidaturas`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cnd_candidatos`
--

CREATE TABLE `cnd_candidatos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text NOT NULL,
  `url_foto` varchar(255) NOT NULL,
  `url_detalle` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cnd_newpass`
--

CREATE TABLE `cnd_newpass` (
  `user_id` int(11) NOT NULL,
  `hash` varchar(255) NOT NULL,
  `creation` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cnd_users`
--

CREATE TABLE `cnd_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `apellidos` varchar(255) NOT NULL,
  `nif` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `cp` varchar(10) NOT NULL,
  `direccion` varchar(255) NOT NULL,
  `provincia` varchar(255) NOT NULL,
  `municipio` varchar(255) NOT NULL,
  `nacimiento` date NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nif` (`nif`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
