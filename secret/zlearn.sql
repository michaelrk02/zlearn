-- Adminer 4.7.8 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `zl_config`;
CREATE TABLE `zl_config` (
  `key` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

TRUNCATE `zl_config`;
INSERT INTO `zl_config` (`key`, `value`) VALUES
('enable_app_registration',	'1'),
('enable_sso_registration',	'1');

DROP TABLE IF EXISTS `zl_courses`;
CREATE TABLE `zl_courses` (
  `course_id` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `password` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `title` varchar(100) NOT NULL,
  `metadata` varchar(250) NOT NULL,
  `description` text NOT NULL,
  `allow_leave` tinyint(1) NOT NULL,
  PRIMARY KEY (`course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


DROP TABLE IF EXISTS `zl_course_members`;
CREATE TABLE `zl_course_members` (
  `course_id` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `user_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `instructor` tinyint(1) NOT NULL,
  KEY `user_id` (`user_id`),
  KEY `course_id` (`course_id`),
  CONSTRAINT `zl_course_members_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `zl_users` (`user_id`),
  CONSTRAINT `zl_course_members_ibfk_3` FOREIGN KEY (`course_id`) REFERENCES `zl_courses` (`course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


DROP TABLE IF EXISTS `zl_materials`;
CREATE TABLE `zl_materials` (
  `material_id` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `course_id` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `title` varchar(100) NOT NULL,
  `subtitle` varchar(250) NOT NULL,
  `contents` text NOT NULL,
  `timestamp` bigint(20) NOT NULL,
  PRIMARY KEY (`material_id`),
  KEY `course_id` (`course_id`),
  CONSTRAINT `zl_materials_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `zl_courses` (`course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


DROP TABLE IF EXISTS `zl_quizzes`;
CREATE TABLE `zl_quizzes` (
  `quiz_id` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `course_id` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` varchar(1000) NOT NULL,
  `duration` int(11) NOT NULL,
  `num_questions` int(11) NOT NULL,
  `questions_hash` varchar(32) NOT NULL,
  `essay` tinyint(1) NOT NULL,
  `mc_num_choices` int(2) NOT NULL,
  `mc_score_correct` int(11) NOT NULL,
  `mc_score_incorrect` int(11) NOT NULL,
  `mc_score_empty` int(11) NOT NULL,
  `mc_answers` text NOT NULL,
  `show_grades` tinyint(1) NOT NULL,
  `show_leaderboard` tinyint(1) NOT NULL,
  `locked` tinyint(1) NOT NULL,
  `hash` varchar(32) NOT NULL,
  PRIMARY KEY (`quiz_id`),
  KEY `course_id` (`course_id`),
  CONSTRAINT `zl_quizzes_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `zl_courses` (`course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


DROP TABLE IF EXISTS `zl_quiz_responses`;
CREATE TABLE `zl_quiz_responses` (
  `quiz_id` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `user_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `timestamp` bigint(20) NOT NULL,
  `data` longtext NOT NULL,
  `score` int(11) NOT NULL,
  KEY `user_id` (`user_id`),
  KEY `quiz_id` (`quiz_id`),
  CONSTRAINT `zl_quiz_responses_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `zl_users` (`user_id`),
  CONSTRAINT `zl_quiz_responses_ibfk_4` FOREIGN KEY (`quiz_id`) REFERENCES `zl_quizzes` (`quiz_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


DROP TABLE IF EXISTS `zl_users`;
CREATE TABLE `zl_users` (
  `user_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `password` varchar(60) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(254) NOT NULL,
  `hash` varchar(32) NOT NULL,
  `allow_course_management` tinyint(1) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- 2021-07-03 13:11:32
