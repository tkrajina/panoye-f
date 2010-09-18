<?php

$sqlStr = 'CREATE TABLE IF NOT EXISTS `panoye_app_test` (
  `id` bigint(20) NOT NULL auto_increment,
  `str` varchar(100) NOT NULL,
  `jkl` varchar(100) NOT NULL,
  `sef_url` varchar(100) NOT NULL,
  `title` varchar(100) NOT NULL,
  `created` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `updated` timestamp NOT NULL default \'0000-00-00 00:00:00\',
  PRIMARY KEY  (`id`),
  KEY `str` (`str`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=20';
$sql = new Sql( $sqlStr );
$sql->execute();