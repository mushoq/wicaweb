-- phpMyAdmin SQL Dump
-- version 3.3.2deb1ubuntu1
-- http://www.phpmyadmin.net
--
-- Servidor: localhost
-- Tiempo de generación: 19-12-2012 
-- Versión del servidor: 5.1.61
-- Versión de PHP: 5.3.9-ZS5.6.0

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

SET FOREIGN_KEY_CHECKS = 0;


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de datos: `wicaweb`
--

--
-- Volcar la base de datos para la tabla `wc_section_template`
--

INSERT INTO `wc_section_template` (`id`, `name`, `file_name`, `column_number`, `type`) VALUES
(1, '1 Column', 'sectemplate.phtml', 1, 'both'),
(2, '2 Columns', 'sectemplate.phtml', 2, 'both'),
(3, '3 Columns', 'sectemplate.phtml', 3, 'both'),
(4, '4 Columns', 'sectemplate.phtml', 4, 'both'),
(5, 'Carrousel', 'seccarousel1.phtml', 1, 'section'),
(6, 'login template', 'logintemplate.phtml', 1, 'both');

--
-- Volcar la base de datos para la tabla `wc_website_language`
--

INSERT INTO `wc_website_language` (`id`, `name`, `abbreviation`, `description`) VALUES
(1, 'Espa&ntilde;ol', 'es', NULL),
(2, 'English', 'en', NULL);

--
-- Volcar la base de datos para la tabla `wc_website_template`
--

INSERT INTO `wc_website_template` (`id`, `name`, `file_name`, `image`, `css_files`, `js_files`, `images_files`, `media_css`) VALUES
(1, 'template web 1', 'webtemplate1.phtml', 'template1.jpg', 'NULL', 'NULL', 'NULL', 'NULL'),
(2, 'template web 2', 'webtemplate2.phtml', 'template2.jpg', 'NULL', 'NULL', 'NULL', 'NULL'),
(3, 'template web 3', 'webtemplate3.phtml', 'template3.jpg', 'NULL', 'NULL', 'NULL', 'NULL'),
(4, 'template web 4', 'webtemplate4.phtml', 'template4.jpg', 'NULL', 'NULL', 'NULL', 'NULL');


--
-- Volcar la base de datos para la tabla `wc_area`
--

INSERT INTO `wc_area` (`id`, `template_id`, `name`, `type`, `area_number`, `width`) VALUES
(1, 1, 'wica_area_content', 'variable', 1, 'col-md-9'),
(2, 1, 'wica_area_2', 'fixed', 2, 'col-md-3'),
(3, 1, 'wica_area_3', 'fixed', 3, 'col-md-3'),
(4, 2, 'wica_area_content', 'variable', 1, 'col-md-9'),
(5, 2, 'wica_area_2', 'fixed', 2, 'col-md-3'),
(6, 2, 'wica_area_3', 'fixed', 3, 'col-md-3'),
(7, 3, 'wica_area_content', 'variable', 1, 'col-md-8'),
(8, 3, 'wica_area_2', 'fixed', 2, 'col-md-2'),
(9, 3, 'wica_area_3', 'fixed', 3, 'col-md-2'),
(10, 4, 'wica_area_content', 'variable', 1, 'col-md-12');

--
-- Volcar la base de datos para la tabla `wc_module`
--

INSERT INTO `wc_module` (`id`, `name`, `description`, `status`, `action`, `image`, `partial`) VALUES
(1, 'Websites', 'Website creator', 'active', 'core/website_website','website.png','NULL'),
(2, 'CMS', 'CMS module, sections and contents', 'active', 'core/section_section','cms.png','NULL'),
(3, 'Users', 'User creator', 'active', 'core/user_user','users.png','NULL'),
(4, 'Profiles', 'Profile creator', 'active', 'core/profile_profile','profile.png','NULL'),
(5, 'External Files', 'External files uploader', 'active', 'core/externalfiles_externalfiles','files.png','NULL'),
(6, 'Templates', 'Templates administration', 'active', 'core/template_template', 'templates.png','NULL'),
(7, 'Dictionary', 'Dictionary administration', 'active', 'core/dictionary_dictionary', 'dictionary.png','NULL'),
(8, 'External Modules', 'External modules installer', 'active', 'core/externalmodules_externalmodules', 'external_modules.png','NULL'),
(9, 'Banners', 'Banners external module', 'active', 'banners', 'banners.png','banner.phtml'),
(10, 'Products', 'Products external module', 'active', 'products', 'products.png','product.phtml');
--
-- Volcar la base de datos para la tabla `wc_module_action`
--

INSERT INTO `wc_module_action` (`id`, `module_id`, `title`, `action`) VALUES
(1, 1, 'Create', 'new'),
(2, 1, 'Update', 'edit'),
(3, 2, 'Create', 'new'),
(4, 2, 'Update', 'edit'),
(5, 2, 'LinkUp', 'linksection'),
(6, 2, 'Publish', 'publish'),
(7, 2, 'Delete', 'delete'),
(8, 3, 'Create', 'new'),
(9, 3, 'Update', 'edit'),
(10, 4, 'Create', 'new'),
(11, 4, 'Update', 'edit'),
(12, 5, 'Create', 'new'),
(13, 5, 'Delete', 'delete'),
(14, 6, 'Create', 'new'),
(15, 6, 'Update', 'edit'),
(16, 6, 'Delete', 'delete'),
(17, 7, 'Create', 'new'),
(18, 7, 'Update', 'edit'),
(19, 8, 'Install', 'install'),
(20, 9, 'Create', 'new'),
(21, 9, 'Update', 'edit'),
(22, 9, 'Delete', 'delete'),
(23, 9, 'LinkUp', 'linkbanner'),
(24, 10, 'Create', 'new'),
(25, 10, 'Update', 'edit'),
(26, 10, 'Delete', 'delete'),
(27, 10, 'LinkUp', 'linkproduct');

--
-- Volcar la base de datos para la tabla `wc_profile`
--

INSERT INTO `wc_profile` (`id`, `name`, `status`) VALUES
(1, 'Total Control', 'active');

--
-- Volcar la base de datos para la tabla `wc_module_action_profile`
--

INSERT INTO `wc_module_action_profile` (`id`, `profile_id`, `module_action_id`) VALUES
(1, 1, 1),
(2, 1, 2),
(3, 1, 3),
(4, 1, 4),
(5, 1, 5),
(6, 1, 6),
(7, 1, 7),
(8, 1, 8),
(9, 1, 9),
(10, 1, 10),
(11, 1, 11),
(12, 1, 12),
(13, 1, 13),
(14, 1, 14),
(15, 1, 15),
(16, 1, 16),
(17, 1, 17),
(18, 1, 18),
(19, 1, 19),
(20, 1, 20),
(21, 1, 21),
(22, 1, 22),
(23, 1, 23),
(24, 1, 24),
(25, 1, 25),
(26, 1, 26),
(27, 1, 27);

SET FOREIGN_KEY_CHECKS = 1;


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

INSERT INTO `wc_field` (`id`, `content_type_id`, `name`, `type`, `required`, `order_item`) VALUES 
  (1,1,'Content','textarea','yes',1),
  (2,2,'Picture foot','textfield','no',1),
  (3,2,'Description','textarea','no',2),
  (4,2,'Target','radio','no',3),
  (5,2,'Link','textfield','yes',4),
  (6,2,'Image','image','yes',5),
  (7,2,'Format','select','yes',6),
  (8,2,'Save image','button','no',7),
  (9,2,'Resizeimg','radio','no',8),
  (10,1,'Save','button','no',1),
  (11,3,'Type','radio','yes',1),
  (12,3,'Internal section','select','yes',1),
  (13,3,'Link','textfield','yes',1),
  (14,3,'Email','textfield','yes',1),
  (15,3,'File','file','yes',1),
  (16,3,'File type','select','yes',1),
  (17,3,'Text','textarea','no',1),
  (18,3,'Save','button','no',1),
  (19,4,'Description','textarea','yes',1),
  (20,4,'Email','textfield','no',1),
  (21,4,'Captcha','radio','yes',1),
  (22,4,'Element type','select','yes',1),
  (23,4,'Number','textfield','no',1),
  (24,4,'Add','button','no',1),
  (25,4,'Save form','button','no',1),
  (26,5,'Description','textarea','no',1),
  (27,5,'Background','radio','yes',1),
  (28,5,'Flash file','file','yes',1),
  (29,5,'Alternative Image','file','no',1),
  (30,5,'Save','button','no',1),
  (31,6,'URL','textfield','no',1),
  (32,6,'HTML code','textarea','no',1),
  (33,6,'Save','button','no',1),
  (34,7,'Select Images','select_images','no',1),
  (35,7,'Save image','button','no',1),
  (36,2,'Watermarkimg','radio','no',10),
  (37,2,'Watermark position','radio','no',11),
  (38,2,'Zoom','radio','no',9);


--
-- Volcar la base de datos para la tabla `wc_external_files`
--

INSERT INTO `wc_external_files` (`id`, `website_id`, `name`, `path`, `type`, `order_number`) VALUES
(1, NULL, 'jcarousel js', 'jquery.jcarousel.min_1347989829.js', 'js', 1),
(2, NULL, 'jcarousel css', 'skin_1347989853.css', 'css', 1),
(3, NULL, 'productos', 'products_1347990812.js', 'js', 2),
(4, NULL, 'jquery', 'jquery-ui-1.8.23.custom_1348005941.css', 'css', 2);
