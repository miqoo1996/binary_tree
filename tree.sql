--
-- table tree
--

CREATE TABLE IF NOT EXISTS `tree` (
  `id` int(12) unsigned NOT NULL auto_increment,
  `name` varchar(50) NOT NULL,
  `lft` int(12) unsigned NOT NULL,
  `rgt` int(12) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `lft` (`lft`),
  KEY `rgt` (`rgt`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;
