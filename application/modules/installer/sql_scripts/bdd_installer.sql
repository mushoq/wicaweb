SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

DEFAULT CHARACTER SET utf8
DEFAULT COLLATE utf8_general_ci;
-- -----------------------------------------------------
-- Table `wc_website_template`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `wc_website_template` ;

CREATE  TABLE IF NOT EXISTS `wc_website_template` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(100) NOT NULL ,
  `file_name` VARCHAR(100) NOT NULL ,
  `image` VARCHAR(200) NULL ,
  `css_files` TEXT NULL ,
  `js_files` TEXT NULL ,
  `images_files` TEXT NULL ,
  `media_css` TEXT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `wc_website_language`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `wc_website_language` ;

CREATE  TABLE IF NOT EXISTS `wc_website_language` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(150) NOT NULL ,
  `abbreviation` VARCHAR(15) NOT NULL ,
  `description` TEXT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `wc_website`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `wc_website` ;

CREATE  TABLE IF NOT EXISTS `wc_website` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `language_id` INT NOT NULL ,
  `template_id` INT NOT NULL COMMENT 'website template' ,
  `name` VARCHAR(200) NULL COMMENT '\n' ,
  `description` TEXT NULL ,
  `keywords` TEXT NULL ,
  `website_url` VARCHAR(45) NULL ,
  `default_page` ENUM('yes', 'no') NOT NULL COMMENT 'default page\nwhen entering to \nthe site' ,
  `logo` VARCHAR(200) NULL COMMENT 'general logo' ,
  `icon` VARCHAR(200) NULL COMMENT 'general icon' ,
  `info_email` VARCHAR(45) NULL ,
  `time_zone` VARCHAR(45) NULL ,
  `date_format` VARCHAR(45) NULL ,
  `hour_format` VARCHAR(45) NULL ,
  `number_format` VARCHAR(45) NULL ,
  `copyright` TEXT NULL ,
  `publication_approve` ENUM('yes', 'no') NOT NULL COMMENT 'contents should be\naproved before \nbeen published?\nyes/no\n' ,
  `prints` ENUM('yes', 'no') NOT NULL COMMENT 'hits counting?\nyes/no' ,
  `friendly_url` ENUM('yes', 'no') NOT NULL COMMENT 'implement friendly \nurls?\nyes/no' ,
  `tiny_url` ENUM('yes', 'no') NOT NULL COMMENT 'tiny url?\nyes/no' ,
  `log` ENUM('yes', 'no') NOT NULL COMMENT 'include a system log?\nyes/no' ,
  `sitemap_level` TINYINT NULL COMMENT 'sitemap \nlevel\nnumber of nodes' ,
  `dictionary` ENUM('yes', 'no') NOT NULL COMMENT 'use a dictionary\nto check forms\ncontent? \nyes/no' ,
  `private_section` ENUM('yes', 'no') NOT NULL COMMENT 'pubic and private\nsections' ,
  `section_expiration` ENUM('yes', 'no') NOT NULL COMMENT 'article use.. \nexpiration date' ,
  `section_author` ENUM('yes', 'no') NOT NULL COMMENT 'article use..\narticle author?\nyes/no' ,
  `section_feature` ENUM('yes', 'no') NOT NULL COMMENT 'article_use\noutstanding' ,
  `section_highlight` ENUM('yes', 'no') NOT NULL ,
  `section_comments_type` ENUM('internal', 'external','none') NOT NULL ,
  `section_comments` ENUM('none', 'all', 'section', 'article') NOT NULL ,
  `section_images_number` VARCHAR(45) NULL COMMENT 'number of images \nthat contain \nsection' ,
  `section_storage` ENUM('yes', 'no') NULL COMMENT 'store articles ' ,
  `section_expiration_time` INT NULL ,
  `section_rss` ENUM('yes', 'no') NOT NULL ,
  `watermark` VARCHAR(200) NULL ,
  `watermark_pos` VARCHAR(5) NULL ,
  `smtp_hostname` VARCHAR(200) NULL ,
  `smtp_port` INT NULL ,
  `smtp_username` VARCHAR(100) NULL ,
  `smtp_password` VARCHAR(100) NULL ,
  `analytics` VARCHAR(200) NULL ,
  `max_width` INT NULL ,
  `max_height` INT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_website_template1` (`template_id` ASC) ,
  INDEX `fk_website_language1` (`language_id` ASC) ,
  CONSTRAINT `fk_website_template1`
    FOREIGN KEY (`template_id` )
    REFERENCES `wc_website_template` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_website_language1`
    FOREIGN KEY (`language_id` )
    REFERENCES `wc_website_language` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `wc_area`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `wc_area` ;

CREATE  TABLE IF NOT EXISTS `wc_area` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `template_id` INT NOT NULL ,
  `name` VARCHAR(200) NOT NULL ,
  `type` ENUM('fixed', 'variable') NOT NULL ,
  `area_number` INT NOT NULL ,
  `width` VARCHAR(45) NOT NULL ,
  PRIMARY KEY (`id`, `template_id`) ,
  INDEX `fk_area_template1` (`template_id` ASC) ,
  CONSTRAINT `fk_area_template1`
    FOREIGN KEY (`template_id` )
    REFERENCES `wc_website_template` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `wc_section_template`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `wc_section_template` ;

CREATE  TABLE IF NOT EXISTS `wc_section_template` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(200) NOT NULL ,
  `file_name` VARCHAR(200) NOT NULL ,
  `column_number` INT NOT NULL ,
  `type` ENUM('section', 'article', 'both') NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `wc_section`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `wc_section` ;

CREATE  TABLE IF NOT EXISTS `wc_section` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `section_parent_id` BIGINT NULL ,
  `website_id` INT NOT NULL ,
  `section_template_id` INT NOT NULL ,
  `internal_name` VARCHAR(200) NULL ,
  `title` VARCHAR(200) NOT NULL ,
  `subtitle` VARCHAR(45) NULL ,
  `title_browser` VARCHAR(200) NULL ,
  `synopsis` TEXT NULL ,
  `keywords` TEXT NULL ,
  `type` ENUM('public', 'private') NOT NULL ,
  `created_by_id` INT NOT NULL ,
  `updated_by_id` INT NULL ,
  `creation_date` TIMESTAMP NULL ,
  `last_update_date` TIMESTAMP NULL ,
  `approved` ENUM('yes', 'no') NOT NULL ,
  `author` VARCHAR(120) NULL ,
  `publication_status` ENUM('published', 'nonpublished') NOT NULL ,
  `order_number` INT NULL ,
  `feature` ENUM('yes', 'no') NOT NULL ,
  `highlight` ENUM('yes', 'no') NOT NULL ,
  `publish_date` DATE NULL ,
  `expire_date` DATE NULL ,
  `show_publish_date` ENUM('yes', 'no') NOT NULL ,
  `rss_available` ENUM('yes', 'no') NOT NULL ,
  `external_link` VARCHAR(100) NULL ,
  `target` ENUM('self', 'blank') NOT NULL ,
  `comments` ENUM('yes', 'no') NOT NULL ,
  `external_comment_script` TEXT NULL ,
  `display_menu` ENUM('yes', 'no') NOT NULL ,
  `display_menu2` enum('yes','no') DEFAULT 'no',
  `homepage` ENUM('yes', 'no') NOT NULL ,
  `article` ENUM('yes', 'no') NOT NULL ,
  PRIMARY KEY (`id`, `website_id`) ,
  INDEX `fk_section_website` (`website_id` ASC) ,
  INDEX `fk_section_section1` (`section_parent_id` ASC) ,
  INDEX `fk_section_section_template1` (`section_template_id` ASC) ,
  CONSTRAINT `fk_section_website`
    FOREIGN KEY (`website_id` )
    REFERENCES `wc_website` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_section_section1`
    FOREIGN KEY (`section_parent_id` )
    REFERENCES `wc_section` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_section_section_template1`
    FOREIGN KEY (`section_template_id` )
    REFERENCES `wc_section_template` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `wc_content_type`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `wc_content_type` ;

CREATE  TABLE IF NOT EXISTS `wc_content_type` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(100) NOT NULL ,
  `description` VARCHAR(250) NULL ,
  `status` ENUM('active', 'inactive') NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `wc_content`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `wc_content` ;

CREATE  TABLE IF NOT EXISTS `wc_content` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `content_type_id` INT NOT NULL ,
  `website_id` INT NOT NULL ,
  `internal_name` VARCHAR(200) NOT NULL ,
  `title` VARCHAR(200) NULL ,
  `created_by` INT NOT NULL ,
  `updated_by` INT NULL ,
  `creation_date` TIMESTAMP NOT NULL ,
  `last_update_date` TIMESTAMP NULL ,
  `approved` ENUM('yes', 'no') NOT NULL ,
  `status` ENUM('active', 'inactive') NOT NULL ,
  PRIMARY KEY (`id`, `content_type_id`) ,
  INDEX `fk_content_content_type1` (`content_type_id` ASC) ,
  CONSTRAINT `fk_content_content_type1`
    FOREIGN KEY (`content_type_id` )
    REFERENCES `wc_content_type` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `wc_field`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `wc_field` ;

CREATE  TABLE IF NOT EXISTS `wc_field` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `content_type_id` INT NOT NULL ,
  `name` VARCHAR(100) NOT NULL ,
  `type` ENUM('date', 'textfield','textarea','image', 'button', 'select', 'radio', 'checkbox', 'file', 'flash','select_images') NOT NULL ,
  `required` ENUM('yes', 'no') NOT NULL ,
  `order_item` smallint(6) DEFAULT '1',
  PRIMARY KEY (`id`, `content_type_id`) ,
  INDEX `fk_field_content_type1` (`content_type_id` ASC) ,
  CONSTRAINT `fk_field_content_type1`
    FOREIGN KEY (`content_type_id` )
    REFERENCES `wc_content_type` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `wc_content_field`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `wc_content_field` ;

CREATE  TABLE IF NOT EXISTS `wc_content_field` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `field_id` INT NOT NULL ,
  `content_id` INT NOT NULL ,
  `value` TEXT NULL ,
  PRIMARY KEY (`id`, `field_id`, `content_id`) ,
  INDEX `fk_content_field_field1` (`field_id` ASC) ,
  INDEX `fk_content_field_content1` (`content_id` ASC) ,
  CONSTRAINT `fk_content_field_field1`
    FOREIGN KEY (`field_id` )
    REFERENCES `wc_field` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_content_field_content1`
    FOREIGN KEY (`content_id` )
    REFERENCES `wc_content` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `forum`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `forum` ;

CREATE  TABLE IF NOT EXISTS `forum` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(200) NOT NULL ,
  `topic` TEXT NOT NULL ,
  `proposed_by_id` INT NULL ,
  `user_type` ENUM('public', 'private') NOT NULL ,
  `status` ENUM('active', 'inactive') NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `forum_comment`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `forum_comment` ;

CREATE  TABLE IF NOT EXISTS `forum_comment` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `forum_id` INT NOT NULL ,
  `author_id` INT NOT NULL ,
  `content` TEXT NOT NULL ,
  `date` DATETIME NOT NULL ,
  `approvals` SMALLINT NULL ,
  `disapprovals` SMALLINT NULL ,
  `abuse` ENUM('yes', 'no') NOT NULL ,
  PRIMARY KEY (`id`, `forum_id`) ,
  INDEX `fk_comment_forum1` (`forum_id` ASC) ,
  CONSTRAINT `fk_comment_forum1`
    FOREIGN KEY (`forum_id` )
    REFERENCES `forum` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `poll`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `poll` ;

CREATE  TABLE IF NOT EXISTS `poll` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `content_id` TEXT NOT NULL ,
  `start_date` DATE NOT NULL ,
  `end_date` DATE NOT NULL ,
  `status` ENUM('active', 'inactive') NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `question`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `question` ;

CREATE  TABLE IF NOT EXISTS `question` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `poll_id` INT NOT NULL ,
  `content` TEXT NOT NULL ,
  PRIMARY KEY (`id`, `poll_id`) ,
  INDEX `fk_question_poll1` (`poll_id` ASC) ,
  CONSTRAINT `fk_question_poll1`
    FOREIGN KEY (`poll_id` )
    REFERENCES `poll` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `answer`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `answer` ;

CREATE  TABLE IF NOT EXISTS `answer` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `question_id` INT NOT NULL ,
  `content` TEXT NULL ,
  `image` VARCHAR(45) NULL ,
  `value` ENUM('right', 'wrong') NOT NULL ,
  PRIMARY KEY (`id`, `question_id`) ,
  INDEX `fk_answer_question1` (`question_id` ASC) ,
  CONSTRAINT `fk_answer_question1`
    FOREIGN KEY (`question_id` )
    REFERENCES `question` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `banner`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `banner` ;

CREATE  TABLE IF NOT EXISTS `banner` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(45) NOT NULL ,
  `description` TEXT NULL ,
  `content` TEXT NOT NULL ,
  `banner_type` ENUM('image', 'flash', 'html') NOT NULL ,
  `link` VARCHAR(500) NULL ,
  `type` ENUM('calendar', 'hits') NOT NULL ,
  `publish_date` DATE NULL ,
  `expire_date` DATE NULL ,
  `hits` BIGINT NULL ,
  `order_number` INT NULL ,
  `status` ENUM('active', 'inactive') NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `banner_counts`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `banner_counts` ;

CREATE  TABLE IF NOT EXISTS `banner_counts` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `banner_id` INT NOT NULL ,
  `count_hits` BIGINT NOT NULL ,
  PRIMARY KEY (`id`, `banner_id`) ,
  INDEX `fk_banner_hits_banner1` (`banner_id` ASC) ,
  CONSTRAINT `fk_banner_hits_banner1`
    FOREIGN KEY (`banner_id` )
    REFERENCES `banner` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `wc_module`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `wc_module` ;

CREATE  TABLE IF NOT EXISTS `wc_module` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(45) NOT NULL ,
  `description` TEXT NULL ,
  `status` ENUM('active', 'inactive') NOT NULL ,
  `action` VARCHAR(250) NOT NULL ,
  `image` VARCHAR(200) NULL ,
  `partial` VARCHAR(250) NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `wc_module_description`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `wc_module_description` ;

CREATE  TABLE IF NOT EXISTS `wc_module_description` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `module_id` INT NOT NULL ,
  `row_id` INT NOT NULL ,
  PRIMARY KEY (`id`, `module_id`) ,
  INDEX `fk_module_description_module1` (`module_id` ASC) ,
  CONSTRAINT `fk_module_description_module1`
    FOREIGN KEY (`module_id` )
    REFERENCES `wc_module` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `wc_section_module_area`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `wc_section_module_area` ;

CREATE  TABLE IF NOT EXISTS `wc_section_module_area` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `section_id` BIGINT NOT NULL ,
  `area_id` INT NOT NULL ,
  `module_description_id` INT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_section_module_area_area1` (`area_id` ASC) ,
  INDEX `fk_section_module_area_module_description1` (`module_description_id` ASC) ,
  INDEX `fk_section_module_area_section1` (`section_id` ASC) ,
  CONSTRAINT `fk_section_module_area_area1`
    FOREIGN KEY (`area_id` )
    REFERENCES `wc_area` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_section_module_area_module_description1`
    FOREIGN KEY (`module_description_id` )
    REFERENCES `wc_module_description` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_section_module_area_section1`
    FOREIGN KEY (`section_id` )
    REFERENCES `wc_section` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `wc_module_action`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `wc_module_action` ;

CREATE  TABLE IF NOT EXISTS `wc_module_action` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `module_id` INT NOT NULL ,
  `title` VARCHAR(100) NOT NULL ,
  `action` VARCHAR(100) NOT NULL ,
  PRIMARY KEY (`id`, `module_id`) ,
  INDEX `fk_module_action_module1` (`module_id` ASC) ,
  CONSTRAINT `fk_module_action_module1`
    FOREIGN KEY (`module_id` )
    REFERENCES `wc_module` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `wc_section_prints`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `wc_section_prints` ;

CREATE  TABLE IF NOT EXISTS `wc_section_prints` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `section_id` BIGINT NOT NULL ,
  `count` BIGINT NULL ,
  PRIMARY KEY (`id`, `section_id`) ,
  INDEX `fk_section_prints_section1` (`section_id` ASC) ,
  CONSTRAINT `fk_section_prints_section1`
    FOREIGN KEY (`section_id` )
    REFERENCES `wc_section` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `wc_public_user`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `wc_public_user` ;

CREATE  TABLE IF NOT EXISTS `wc_public_user` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(100) NULL ,
  `lastname` VARCHAR(100) NULL ,
  `identification` VARCHAR(20) NULL ,
  `email` VARCHAR(45) NULL ,
  `phone` VARCHAR(20) NULL ,
  `username` VARCHAR(45) NOT NULL ,
  `password` VARCHAR(45) NOT NULL ,
  `old_password` VARCHAR(45) NOT NULL ,
  `status` ENUM('active', 'inactive') NOT NULL ,
  `activation_key` VARCHAR(100) NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `wc_dictionary`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `wc_dictionary` ;

CREATE  TABLE IF NOT EXISTS `wc_dictionary` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `website_id` INT NULL ,
  `name` VARCHAR(45) NULL ,
  `status` ENUM('active', 'inactive') NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_dictionary_website1` (`website_id` ASC) ,
  CONSTRAINT `fk_dictionary_website1`
    FOREIGN KEY (`website_id` )
    REFERENCES `wc_website` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `wc_word`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `wc_word` ;

CREATE  TABLE IF NOT EXISTS `wc_word` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `dictionary_id` INT NOT NULL ,
  `expression` VARCHAR(45) NULL ,
  PRIMARY KEY (`id`, `dictionary_id`) ,
  INDEX `fk_word_dictionary1` (`dictionary_id` ASC) ,
  CONSTRAINT `fk_word_dictionary1`
    FOREIGN KEY (`dictionary_id` )
    REFERENCES `wc_dictionary` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `wc_profile`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `wc_profile` ;

CREATE  TABLE IF NOT EXISTS `wc_profile` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(100) NOT NULL ,
  `status` ENUM('active', 'inactive') NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `wc_user`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `wc_user` ;

CREATE  TABLE IF NOT EXISTS `wc_user` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `profile_id` INT NOT NULL ,
  `name` VARCHAR(100) NULL ,
  `lastname` VARCHAR(100) NULL ,
  `identification` VARCHAR(20) NULL ,
  `email` VARCHAR(45) NULL ,
  `phone` VARCHAR(20) NULL ,
  `username` VARCHAR(45) NOT NULL ,
  `password` VARCHAR(45) NOT NULL ,
  `creation_date` TIMESTAMP NOT NULL ,
  `last_update_date` TIMESTAMP NULL ,
  `status` ENUM('active', 'inactive') NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_wc_user_wc_profile1` (`profile_id` ASC) ,
  CONSTRAINT `fk_wc_user_wc_profile1`
    FOREIGN KEY (`profile_id` )
    REFERENCES `wc_profile` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'website_user';


-- -----------------------------------------------------
-- Table `wc_log`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `wc_log` ;

CREATE  TABLE IF NOT EXISTS `wc_log` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(100) NOT NULL ,
  `action` VARCHAR(45) NOT NULL ,
  `user` INT NOT NULL ,
  `date` TIMESTAMP NOT NULL ,
  `wc_user_id` INT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_wc_log_wc_user1` (`wc_user_id` ASC) ,
  CONSTRAINT `fk_wc_log_wc_user1`
    FOREIGN KEY (`wc_user_id` )
    REFERENCES `wc_user` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `wc_module_action_profile`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `wc_module_action_profile` ;

CREATE  TABLE IF NOT EXISTS `wc_module_action_profile` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `profile_id` INT NOT NULL ,
  `module_action_id` INT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_module_action_profile_module_action1` (`module_action_id` ASC) ,
  INDEX `fk_module_action_profile_profile1` (`profile_id` ASC) ,
  CONSTRAINT `fk_module_action_profile_module_action1`
    FOREIGN KEY (`module_action_id` )
    REFERENCES `wc_module_action` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_module_action_profile_profile1`
    FOREIGN KEY (`profile_id` )
    REFERENCES `wc_profile` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `wc_section_profile`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `wc_section_profile` ;

CREATE  TABLE IF NOT EXISTS `wc_section_profile` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `profile_id` INT NOT NULL ,
  `section_id` BIGINT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_wc_section_profile_wc_section1` (`section_id` ASC) ,
  INDEX `fk_wc_section_profile_wc_profile1` (`profile_id` ASC) ,
  CONSTRAINT `fk_wc_section_profile_wc_section1`
    FOREIGN KEY (`section_id` )
    REFERENCES `wc_section` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_wc_section_profile_wc_profile1`
    FOREIGN KEY (`profile_id` )
    REFERENCES `wc_profile` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `wc_website_profile`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `wc_website_profile` ;

CREATE  TABLE IF NOT EXISTS `wc_website_profile` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `profile_id` INT NOT NULL ,
  `website_id` INT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_website_action_profile_profile1` (`profile_id` ASC) ,
  INDEX `fk_wc_website_profile_wc_website1` (`website_id` ASC) ,
  CONSTRAINT `fk_website_action_profile_profile1`
    FOREIGN KEY (`profile_id` )
    REFERENCES `wc_profile` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_wc_website_profile_wc_website1`
    FOREIGN KEY (`website_id` )
    REFERENCES `wc_website` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `wc_content_temp`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `wc_content_temp` ;

CREATE  TABLE IF NOT EXISTS `wc_content_temp` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `content_type_id` INT NOT NULL ,
  `content_id` INT NULL ,
  `website_id` INT NOT NULL ,
  `internal_name` VARCHAR(200) NOT NULL ,
  `title` VARCHAR(200) NULL ,
  `created_by` INT NOT NULL ,
  `updated_by` INT NULL ,
  `creation_date` TIMESTAMP NOT NULL ,
  `last_update_date` TIMESTAMP NULL ,
  `approved` ENUM('yes', 'no') NOT NULL ,
  `status` ENUM('active', 'inactive') NOT NULL ,
  PRIMARY KEY (`id`, `content_type_id`) ,
  INDEX `fk_wc_content_temp_wc_content_type1` (`content_type_id` ASC) ,
  CONSTRAINT `fk_wc_content_temp_wc_content_type1`
    FOREIGN KEY (`content_type_id` )
    REFERENCES `wc_content_type` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `wc_content_field_temp`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `wc_content_field_temp` ;

CREATE  TABLE IF NOT EXISTS `wc_content_field_temp` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `field_id` INT NOT NULL ,
  `content_temp_id` INT NOT NULL ,
  `content_id` INT NULL ,
  `value` TEXT NULL ,
  PRIMARY KEY (`id`, `field_id`, `content_temp_id`) ,
  INDEX `fk_wc_content_field_temp_wc_field1` (`field_id` ASC) ,
  INDEX `fk_wc_content_field_temp_wc_content_temp1` (`content_temp_id` ASC) ,
  CONSTRAINT `fk_wc_content_field_temp_wc_field1`
    FOREIGN KEY (`field_id` )
    REFERENCES `wc_field` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_wc_content_field_temp_wc_content_temp1`
    FOREIGN KEY (`content_temp_id` )
    REFERENCES `wc_content_temp` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `wc_website_state`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `wc_website_state` ;

CREATE  TABLE IF NOT EXISTS `wc_website_state` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `website_id` INT NOT NULL ,
  `type` ENUM('online', 'offline', 'comingsoon') NOT NULL ,
  `display_text` VARCHAR(250) NULL COMMENT 'text to display\n when a state is \nselected' ,
  `image` VARCHAR(45) NULL ,
  `status` ENUM('active', 'inactive') NOT NULL ,
  PRIMARY KEY (`id`, `website_id`) ,
  INDEX `fk_state_website1` (`website_id` ASC) ,
  CONSTRAINT `fk_state_website1`
    FOREIGN KEY (`website_id` )
    REFERENCES `wc_website` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `product`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `product` ;

CREATE  TABLE IF NOT EXISTS `product` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(250) NOT NULL ,
  `description` TEXT NULL ,
  `image` VARCHAR(250) NOT NULL ,
  `available` ENUM('yes', 'no') NOT NULL ,
  `status` ENUM('active', 'inactive') NOT NULL ,
  `feature` ENUM('yes', 'no') NOT NULL ,
  `order_number` INT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `wc_section_image`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `wc_section_image` ;

CREATE  TABLE IF NOT EXISTS `wc_section_image` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `section_id` BIGINT NOT NULL ,
  `name` VARCHAR(100) NULL ,
  `file_name` VARCHAR(100) NOT NULL ,
  PRIMARY KEY (`id`, `section_id`) ,
  INDEX `fk_section_image_section1` (`section_id` ASC) ,
  CONSTRAINT `fk_section_image_section1`
    FOREIGN KEY (`section_id` )
    REFERENCES `wc_section` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `wc_section_comment`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `wc_section_comment` ;

CREATE  TABLE IF NOT EXISTS `wc_section_comment` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `section_id` BIGINT NOT NULL ,
  `content` TEXT NOT NULL ,
  `author_id` VARCHAR(45) NOT NULL ,
  `date` DATETIME NOT NULL ,
  `approvals` SMALLINT NULL ,
  `disapprovals` SMALLINT NULL ,
  `abuse` ENUM('yes', 'no') NOT NULL ,
  PRIMARY KEY (`id`, `section_id`) ,
  INDEX `fk_section_comment_section1` (`section_id` ASC) ,
  CONSTRAINT `fk_section_comment_section1`
    FOREIGN KEY (`section_id` )
    REFERENCES `wc_section` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `wc_section_temp`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `wc_section_temp` ;

CREATE  TABLE IF NOT EXISTS `wc_section_temp` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `section_id` BIGINT NULL ,
  `section_parent_id` BIGINT NULL ,
  `website_id` INT NOT NULL ,
  `section_template_id` INT NOT NULL ,
  `internal_name` VARCHAR(200) NULL ,
  `title` VARCHAR(200) NULL ,
  `subtitle` VARCHAR(45) NULL ,
  `title_browser` VARCHAR(200) NULL ,
  `synopsis` TEXT NULL ,
  `keywords` TEXT NULL ,
  `type` ENUM('public', 'private') NOT NULL ,
  `created_by_id` INT NOT NULL ,
  `updated_by_id` INT NULL ,
  `creation_date` TIMESTAMP NULL ,
  `last_update_date` TIMESTAMP NULL ,
  `approved` ENUM('yes', 'no') NOT NULL ,
  `author` VARCHAR(120) NULL ,
  `publication_status` ENUM('published', 'nonpublished') NOT NULL ,
  `order_number` INT NULL ,
  `feature` ENUM('yes', 'no') NOT NULL ,
  `highlight` ENUM('yes', 'no') NOT NULL ,
  `publish_date` DATETIME NULL ,
  `expire_date` DATETIME NULL ,
  `show_publish_date` ENUM('yes', 'no') NOT NULL ,
  `rss_available` ENUM('yes', 'no') NOT NULL ,
  `external_link` VARCHAR(100) NULL ,
  `target` ENUM('self', 'blank') NOT NULL ,
  `comments` ENUM('yes', 'no') NOT NULL ,
  `external_comment_script` TEXT NULL ,
  `display_menu` ENUM('yes', 'no') NOT NULL ,
  `homepage` ENUM('yes', 'no') NOT NULL ,
  `article` ENUM('yes', 'no') NOT NULL ,
  PRIMARY KEY (`id`, `website_id`) ,
  INDEX `fk_section_website` (`website_id` ASC) ,
  INDEX `fk_section_section_template1` (`section_template_id` ASC) ,
  CONSTRAINT `fk_section_website0`
    FOREIGN KEY (`website_id` )
    REFERENCES `wc_website` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_section_section_template10`
    FOREIGN KEY (`section_template_id` )
    REFERENCES `wc_section_template` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `wc_section_storage`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `wc_section_storage` ;

CREATE  TABLE IF NOT EXISTS `wc_section_storage` (
  `id` BIGINT NOT NULL ,
  `section_parent_id` BIGINT NULL ,
  `website_id` INT NOT NULL ,
  `section_template_id` INT NOT NULL ,
  `internal_name` VARCHAR(200) NULL ,
  `title` VARCHAR(200) NOT NULL ,
  `subtitle` VARCHAR(45) NULL ,
  `title_browser` VARCHAR(200) NULL ,
  `synopsis` TEXT NULL ,
  `keywords` TEXT NULL ,
  `type` ENUM('public', 'private') NOT NULL ,
  `created_by_id` INT NOT NULL ,
  `updated_by_id` INT NULL ,
  `creation_date` TIMESTAMP NULL ,
  `last_update_date` TIMESTAMP NULL ,
  `approved` ENUM('yes', 'no') NOT NULL ,
  `author` VARCHAR(120) NULL ,
  `publication_status` ENUM('published', 'nonpublished') NOT NULL ,
  `order_number` INT NULL ,
  `feature` ENUM('yes', 'no') NOT NULL ,
  `highlight` ENUM('yes', 'no') NOT NULL ,
  `publish_date` DATETIME NULL ,
  `expire_date` DATETIME NULL ,
  `show_publish_date` ENUM('yes', 'no') NOT NULL ,
  `rss_available` ENUM('yes', 'no') NOT NULL ,
  `external_link` VARCHAR(100) NULL ,
  `target` ENUM('self', 'blank') NOT NULL ,
  `comments` ENUM('yes', 'no') NOT NULL ,
  `external_comment_script` TEXT NULL ,
  `display_menu` ENUM('yes', 'no') NOT NULL ,
  `homepage` ENUM('yes', 'no') NOT NULL ,
  `article` ENUM('yes', 'no') NOT NULL ,
  PRIMARY KEY (`id`, `website_id`) ,
  INDEX `fk_wc_section_storage_wc_section_template1` (`section_template_id` ASC) ,
  INDEX `fk_wc_section_storage_wc_section_storage1` (`section_parent_id` ASC) ,
  INDEX `fk_wc_section_storage_wc_website1` (`website_id` ASC) ,
  CONSTRAINT `fk_wc_section_storage_wc_section_template1`
    FOREIGN KEY (`section_template_id` )
    REFERENCES `wc_section_template` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_wc_section_storage_wc_section_storage1`
    FOREIGN KEY (`section_parent_id` )
    REFERENCES `wc_section_storage` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_wc_section_storage_wc_website1`
    FOREIGN KEY (`website_id` )
    REFERENCES `wc_website` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `wc_section_module_area_storage`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `wc_section_module_area_storage` ;

CREATE  TABLE IF NOT EXISTS `wc_section_module_area_storage` (
  `id` INT NOT NULL ,
  `section_id` BIGINT NOT NULL ,
  `area_id` INT NOT NULL ,
  `module_description_id` INT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_section_module_area_store_section_store1` (`section_id` ASC) ,
  INDEX `fk_wc_section_module_area_wc_module_description1` (`module_description_id` ASC) ,
  INDEX `fk_wc_section_module_area_storage_wc_area1` (`area_id` ASC) ,
  CONSTRAINT `fk_section_module_area_store_section_store1`
    FOREIGN KEY (`section_id` )
    REFERENCES `wc_section_storage` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_wc_section_module_area_wc_module_description1`
    FOREIGN KEY (`module_description_id` )
    REFERENCES `wc_module_description` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_wc_section_module_area_storage_wc_area1`
    FOREIGN KEY (`area_id` )
    REFERENCES `wc_area` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `wc_content_storage`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `wc_content_storage` ;

CREATE  TABLE IF NOT EXISTS `wc_content_storage` (
  `id` INT NOT NULL ,
  `content_type_id` INT NOT NULL ,
  `website_id` INT NOT NULL ,
  `internal_name` VARCHAR(200) NOT NULL ,
  `title` VARCHAR(200) NULL ,
  `created_by` INT NOT NULL ,
  `updated_by` INT NULL ,
  `creation_date` TIMESTAMP NOT NULL ,
  `last_update_date` TIMESTAMP NULL ,
  `approved` ENUM('yes', 'no') NOT NULL ,
  `status` ENUM('active', 'inactive') NOT NULL ,
  PRIMARY KEY (`id`, `content_type_id`) ,
  INDEX `fk_wc_content_storage_wc_content_type1` (`content_type_id` ASC) ,
  CONSTRAINT `fk_wc_content_storage_wc_content_type1`
    FOREIGN KEY (`content_type_id` )
    REFERENCES `wc_content_type` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `wc_content_field_storage`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `wc_content_field_storage` ;

CREATE  TABLE IF NOT EXISTS `wc_content_field_storage` (
  `id` INT NOT NULL ,
  `field_id` INT NOT NULL ,
  `content_id` INT NOT NULL ,
  `value` TEXT NULL ,
  PRIMARY KEY (`id`, `field_id`, `content_id`) ,
  INDEX `fk_content_field_store_content_store1` (`content_id` ASC) ,
  INDEX `fk_wc_content_field_storage_wc_field1` (`field_id` ASC) ,
  CONSTRAINT `fk_content_field_store_content_store1`
    FOREIGN KEY (`content_id` )
    REFERENCES `wc_content_storage` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_wc_content_field_storage_wc_field1`
    FOREIGN KEY (`field_id` )
    REFERENCES `wc_field` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `wc_section_image_storage`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `wc_section_image_storage` ;

CREATE  TABLE IF NOT EXISTS `wc_section_image_storage` (
  `id` INT NOT NULL ,
  `section_id` BIGINT NOT NULL ,
  `name` VARCHAR(100) NULL ,
  `file_name` VARCHAR(100) NOT NULL ,
  PRIMARY KEY (`id`, `section_id`) ,
  INDEX `fk_section_image_store_section_store1` (`section_id` ASC) ,
  CONSTRAINT `fk_section_image_store_section_store1`
    FOREIGN KEY (`section_id` )
    REFERENCES `wc_section_storage` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `wc_section_comment_storage`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `wc_section_comment_storage` ;

CREATE  TABLE IF NOT EXISTS `wc_section_comment_storage` (
  `id` INT NOT NULL ,
  `section_id` BIGINT NOT NULL ,
  `content` TEXT NOT NULL ,
  `author_id` VARCHAR(45) NOT NULL ,
  `date` DATETIME NOT NULL ,
  `approvals` SMALLINT NULL ,
  `disapprovals` SMALLINT NULL ,
  `abuse` ENUM('yes', 'no') NOT NULL ,
  PRIMARY KEY (`id`, `section_id`) ,
  INDEX `fk_section_comment_store_section_store1` (`section_id` ASC) ,
  CONSTRAINT `fk_section_comment_store_section_store1`
    FOREIGN KEY (`section_id` )
    REFERENCES `wc_section_storage` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `wc_section_prints_storage`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `wc_section_prints_storage` ;

CREATE  TABLE IF NOT EXISTS `wc_section_prints_storage` (
  `id` INT NOT NULL ,
  `section_id` BIGINT NOT NULL ,
  `count` BIGINT NULL ,
  PRIMARY KEY (`id`, `section_id`) ,
  INDEX `fk_section_prints_copy1_section_store1` (`section_id` ASC) ,
  CONSTRAINT `fk_section_prints_copy1_section_store1`
    FOREIGN KEY (`section_id` )
    REFERENCES `wc_section_storage` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `wc_menu`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `wc_menu` ;

CREATE  TABLE IF NOT EXISTS `wc_menu` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(100) NOT NULL ,
  `description` VARCHAR(45) NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `wc_menu_item`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `wc_menu_item` ;

CREATE  TABLE IF NOT EXISTS `wc_menu_item` (
  `id` INT NOT NULL ,
  `menu_id` INT NOT NULL ,
  `name` VARCHAR(100) NOT NULL ,
  `description` VARCHAR(150) NULL ,
  `action` VARCHAR(150) NOT NULL ,
  `order` INT NOT NULL ,
  PRIMARY KEY (`id`, `menu_id`) ,
  INDEX `fk_menu_item_menu1` (`menu_id` ASC) ,
  CONSTRAINT `fk_menu_item_menu1`
    FOREIGN KEY (`menu_id` )
    REFERENCES `wc_menu` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `wc_form_field`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `wc_form_field` ;

CREATE  TABLE IF NOT EXISTS `wc_form_field` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `content_id` INT NOT NULL ,
  `name` VARCHAR(100) NOT NULL ,
  `description` VARCHAR(250) NULL ,
  `type` ENUM('textfield', 'textarea', 'radiobutton', 'dropdown','checkbox','comment','file') NOT NULL ,
  `options` VARCHAR(300) NULL ,
  `required` ENUM('yes', 'no') NOT NULL ,
  `weight` INT NOT NULL ,
  PRIMARY KEY (`id`, `content_id`) ,
  INDEX `fk_form_field_content1` (`content_id` ASC) ,
  CONSTRAINT `fk_form_field_content1`
    FOREIGN KEY (`content_id` )
    REFERENCES `wc_content` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `wc_form_field_temp`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `wc_form_field_temp` ;

CREATE  TABLE IF NOT EXISTS `wc_form_field_temp` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `content_temp_id` INT NOT NULL ,
  `name` VARCHAR(100) NOT NULL ,
  `description` VARCHAR(250) NULL ,
  `type` ENUM('textfield', 'textarea', 'radiobutton', 'dropdown','checkbox','comment','file') NOT NULL ,
  `options` VARCHAR(300) NULL ,
  `required` ENUM('yes', 'no') NOT NULL ,
  `weight` INT NOT NULL ,
  PRIMARY KEY (`id`, `content_temp_id`) ,
  INDEX `fk_form_field_temp_content_temp1` (`content_temp_id` ASC) ,
  CONSTRAINT `fk_form_field_temp_content_temp1`
    FOREIGN KEY (`content_temp_id` )
    REFERENCES `wc_content_temp` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `wc_form_field_storage`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `wc_form_field_storage` ;

CREATE  TABLE IF NOT EXISTS `wc_form_field_storage` (
  `id` INT NOT NULL ,
  `content_id` INT NOT NULL ,
  `name` VARCHAR(100) NOT NULL ,
  `description` VARCHAR(250) NULL ,
  `type` ENUM('textfield', 'textarea', 'radiobutton', 'dropdown','checkbox','comment','file') NOT NULL ,
  `options` VARCHAR(300) NULL ,
  `required` ENUM('yes', 'no') NOT NULL ,
  `weight` INT NOT NULL ,
  PRIMARY KEY (`id`, `content_id`) ,
  INDEX `fk_form_field_storage_content_storage1` (`content_id` ASC) ,
  CONSTRAINT `fk_form_field_storage_content_storage1`
    FOREIGN KEY (`content_id` )
    REFERENCES `wc_content_storage` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `wc_section_image_temp`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `wc_section_image_temp` ;

CREATE  TABLE IF NOT EXISTS `wc_section_image_temp` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `section_image_id` BIGINT NULL ,
  `section_temp_id` BIGINT NOT NULL ,
  `name` VARCHAR(100) NULL ,
  `file_name` VARCHAR(100) NOT NULL ,
  PRIMARY KEY (`id`, `section_temp_id`) ,
  INDEX `fk_wc_section_image_temp_wc_section_temp1` (`section_temp_id` ASC) ,
  CONSTRAINT `fk_wc_section_image_temp_wc_section_temp1`
    FOREIGN KEY (`section_temp_id` )
    REFERENCES `wc_section_temp` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `wc_content_by_section`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `wc_content_by_section` ;

CREATE  TABLE IF NOT EXISTS `wc_content_by_section` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `section_id` BIGINT NOT NULL ,
  `content_id` INT NOT NULL ,
  `weight` INT NULL ,
  `column_number` INT NULL ,
  `align` ENUM('left', 'center', 'right') NOT NULL ,
  INDEX `fk_wc_content_by_section_wc_section1` (`section_id` ASC) ,
  INDEX `fk_wc_content_by_section_wc_content1` (`content_id` ASC) ,
  PRIMARY KEY (`id`) ,
  CONSTRAINT `fk_wc_content_by_section_wc_section1`
    FOREIGN KEY (`section_id` )
    REFERENCES `wc_section` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_wc_content_by_section_wc_content1`
    FOREIGN KEY (`content_id` )
    REFERENCES `wc_content` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `wc_content_by_section_temp`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `wc_content_by_section_temp` ;

CREATE  TABLE IF NOT EXISTS `wc_content_by_section_temp` (
  `id` BIGINT NOT NULL AUTO_INCREMENT ,
  `content_by_section_id` INT NULL ,
  `section_temp_id` BIGINT NOT NULL ,
  `content_temp_id` INT NOT NULL ,
  `weight` INT NULL ,
  `column_number` INT NULL ,
  `align` ENUM('left', 'center', 'right') NOT NULL ,
  INDEX `fk_wc_content_by_section_temp_wc_section_temp1` (`section_temp_id` ASC) ,
  INDEX `fk_wc_content_by_section_temp_wc_content_temp1` (`content_temp_id` ASC) ,
  PRIMARY KEY (`id`) ,
  CONSTRAINT `fk_wc_content_by_section_temp_wc_section_temp1`
    FOREIGN KEY (`section_temp_id` )
    REFERENCES `wc_section_temp` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_wc_content_by_section_temp_wc_content_temp1`
    FOREIGN KEY (`content_temp_id` )
    REFERENCES `wc_content_temp` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `wc_content_by_section_storage`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `wc_content_by_section_storage` ;

CREATE  TABLE IF NOT EXISTS `wc_content_by_section_storage` (
  `id` BIGINT NOT NULL ,
  `section_id` BIGINT NOT NULL ,
  `content_id` INT NOT NULL ,
  `weight` INT NULL ,
  `column_number` INT NULL ,
  `align` ENUM('left', 'center', 'right') NOT NULL ,
  INDEX `fk_wc_content_by_section_storage_wc_section_storage1` (`section_id` ASC) ,
  INDEX `fk_wc_content_by_section_storage_wc_content_storage1` (`content_id` ASC) ,
  PRIMARY KEY (`id`) ,
  CONSTRAINT `fk_wc_content_by_section_storage_wc_section_storage1`
    FOREIGN KEY (`section_id` )
    REFERENCES `wc_section_storage` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_wc_content_by_section_storage_wc_content_storage1`
    FOREIGN KEY (`content_id` )
    REFERENCES `wc_content_storage` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `wc_external_files`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `wc_external_files` ;

CREATE  TABLE IF NOT EXISTS `wc_external_files` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `website_id` INT NULL ,
  `name` VARCHAR(200) NULL ,
  `path` VARCHAR(200) NULL ,
  `type` ENUM('js', 'css') NOT NULL ,
  `order_number` INT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_wc_external_files_wc_website1` (`website_id` ASC) ,
  CONSTRAINT `fk_wc_external_files_wc_website1`
    FOREIGN KEY (`website_id` )
    REFERENCES `wc_website` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `wc_section_module_area_temp`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `wc_section_module_area_temp` ;

CREATE  TABLE IF NOT EXISTS `wc_section_module_area_temp` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `section_module_area_id` INT NULL ,
  `section_temp_id` BIGINT NOT NULL ,
  `area_id` INT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_section_module_area_temp_wc_area1` (`area_id` ASC) ,
  INDEX `fk_section_module_area_temp_wc_section_temp1` (`section_temp_id` ASC) ,
  CONSTRAINT `fk_section_module_area_temp_wc_area1`
    FOREIGN KEY (`area_id` )
    REFERENCES `wc_area` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_section_module_area_temp_wc_section_temp1`
    FOREIGN KEY (`section_temp_id` )
    REFERENCES `wc_section_temp` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `product_catalog`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `product_catalog` ;

CREATE  TABLE IF NOT EXISTS `product_catalog` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `product_id` INT NOT NULL ,
  `code` VARCHAR(250) NOT NULL ,
  `description` VARCHAR(250) NULL ,
  `price` DECIMAL(11,2) NOT NULL ,
  `price_sale` DECIMAL(11,2) NULL ,
  `weight` DECIMAL(5,2) NULL ,
  `image` VARCHAR(250) NULL ,
  PRIMARY KEY (`id`, `product_id`) ,
  INDEX `fk_product_catalog_product1` (`product_id` ASC) ,
  CONSTRAINT `fk_product_catalog_product1`
    FOREIGN KEY (`product_id` )
    REFERENCES `product` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;



SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

