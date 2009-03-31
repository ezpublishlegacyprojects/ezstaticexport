-- 
-- Structure de la table `ezstaticexport_offset`
-- 

CREATE TABLE `ezstaticexport_offset` (
  `id` bigint(20) NOT NULL auto_increment,
  `export_id` bigint(20) NOT NULL,
  `offset` bigint(20) NOT NULL,
  `removing_root_folder` tinyint(1) NOT NULL default '0',
  `archiving_current_folder` tinyint(1) NOT NULL default '0',
  `exporting_static_ressources` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- 
-- Structure de la table `ezstaticexport_process`
-- 

CREATE TABLE `ezstaticexport_process` (
  `id` bigint(20) NOT NULL auto_increment,
  `export_id` bigint(20) NOT NULL,
  `pid` varchar(10) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;