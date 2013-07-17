-- phpMyAdmin SQL Dump
-- version 3.3.10deb1
-- http://www.phpmyadmin.net
--
-- Servidor: localhost
-- Tiempo de generación: 18-05-2012 a las 11:34:01
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
-- Volcar la base de datos para la tabla `wc_content_type`
--

INSERT INTO `wc_content_type` (`id`, `name`, `description`, `status`) VALUES
(1, 'Text', NULL, 'active'),
(2, 'Image', NULL, 'active'),
(3, 'Link', NULL, 'active'),
(4, 'Form', NULL, 'active'),
(5, 'Flash', NULL, 'active'),
(6, 'Flash Video', NULL, 'active'),
(7, 'Carousel', NULL, 'active');

--
-- Volcar la base de datos para la tabla `wc_field`
--

INSERT INTO `wc_field` (`id`, `content_type_id`, `name`, `type`, `required`) VALUES
(1, 1, 'Content', 'textarea', 'yes'),
(2, 2, 'Picture foot', 'textfield', 'yes'),
(3, 2, 'Description', 'textarea', 'yes'),
(4, 2, 'Target', 'radio', 'no'),
(5, 2, 'Link', 'textfield', 'yes'),
(6, 2, 'Image', 'image', 'yes'),
(7, 2, 'Format', 'select', 'yes'),
(8, 2, 'Save image', 'button', 'no'),
(9, 1, 'Save', 'button', 'no'),
(10, 3, 'Type', 'radio', 'yes'),
(11, 3, 'Internal section', 'select', 'yes'),
(12, 3, 'Link', 'textfield', 'yes'),
(13, 3, 'Email', 'textfield', 'yes'),
(14, 3, 'File', 'file', 'yes'),
(15, 3, 'File type', 'select', 'yes'),
(16, 3, 'Text', 'textarea', 'no'),
(17, 3, 'Save', 'button', 'no'),
(18, 4, 'Description', 'textarea', 'yes'),
(19, 4, 'Email', 'textfield', 'yes'),
(20, 4, 'Captcha', 'radio', 'yes'),
(21, 4, 'Element type', 'select', 'yes'),
(22, 4, 'Number', 'textfield', 'no'),
(23, 4, 'Add', 'button', 'no'),
(24, 4, 'Save form', 'button', 'no'),
(25, 5, 'Description', 'textarea', 'no'),
(26, 5, 'Background', 'radio', 'yes'),
(28, 5, 'Flash file', 'file', 'yes'),
(29, 5, 'Alternative Image', 'file', 'no'),
(30, 5, 'Save', 'button', 'no'),
(31, 6, 'URL', 'textfield', 'no'),
(32, 6, 'HTML code', 'textarea', 'no'),
(33, 6, 'Save', 'button', 'no'),
(34, 7, 'Select Images', 'select_images', 'no'),
(35, 7, 'Save image', 'button', 'no');
