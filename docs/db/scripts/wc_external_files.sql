-- phpMyAdmin SQL Dump
-- version 3.3.10deb1
-- http://www.phpmyadmin.net
--
-- Servidor: localhost
-- Tiempo de generación: 20-09-2012 a las 11:42:41
-- Versión del servidor: 5.1.54
-- Versión de PHP: 5.3.9-ZS5.6.0

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de datos: `wicaweb`
--

--
-- Volcar la base de datos para la tabla `wc_external_files`
--

INSERT INTO `wc_external_files` (`id`, `website_id`, `name`, `path`, `type`, `order_number`) VALUES
(1, 1, 'jcarousel js', 'jquery.jcarousel.min_1347989829.js', 'js', 1),
(2, 1, 'jcarousel css', 'skin_1347989853.css', 'css', 1),
(3, 1, 'productos', 'products_1347990812.js', 'js', 2),
(4, 1, 'jquery', 'jquery-ui-1.8.23.custom_1348005941.css', 'css', 2);
