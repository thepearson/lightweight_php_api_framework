-- test file for mysql

DROP DATABASE IF EXISTS `lpaf`;
CREATE DATABASE `lpaf`;

DROP TABLE IF EXISTS `lpaf`.`test`;
CREATE TABLE `lpaf`.`test` (
 `id` INT(11) PRIMARY KEY NOT NULL,
 `test` VARCHAR(64) DEFAULT NULL
) ENGINE InnoDB;
