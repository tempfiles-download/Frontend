CREATE DATABASE IF NOT EXISTS `tempfiles` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `tempfiles`;
CREATE TABLE `files`(
  `id` VARCHAR(14) NOT NULL,
  `iv` VARCHAR(96) NOT NULL,
  `metadata` TEXT NOT NULL,
  `content` MEDIUMBLOB NOT NULL,
  `maxviews` text,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(`id`),
  UNIQUE KEY `id`(`id`)
) ENGINE = InnoDB DEFAULT CHARSET = latin1;
