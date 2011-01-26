CREATE TABLE  `log` (
	`id` BIGINT NOT NULL AUTO_INCREMENT ,
	`level` TINYINT NOT NULL ,
	`log` VARCHAR( 1000 ) NOT NULL ,
	`created` TIMESTAMP NULL ,
	`updated` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL ,
	PRIMARY KEY (  `id` ) ,
	INDEX (  `id` )
) ENGINE = MYISAM;

ALTER TABLE  `log` CHANGE  `log`  `log` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;

ALTER TABLE  `log` ADD  `session_id` VARCHAR( 50 ) NOT NULL AFTER  `log` ,
ADD  `time` INT NOT NULL AFTER  `session_id`;

ALTER TABLE  `log` CHANGE  `time`  `time` DECIMAL( 5, 3 ) NOT NULL;
