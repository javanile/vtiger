CREATE TABLE `sessiondata` (
  `id` varchar(32) NOT NULL default '',
  `expiry` int(10) unsigned NOT NULL default '0',
  `data` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

