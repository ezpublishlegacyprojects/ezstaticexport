/**
* Database objects creation script for the Static Cache Export
* @version $Id: $
* @author Gaetano Giunta
* @copyright (c) 2007 eZ Systems France
* @license
*/

CREATE TABLE  `ezstaticexport_contenttype` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `node_path_string` varchar(255) NOT NULL,
  `content_type` varchar(16) NOT NULL,
  `flag_type` varchar(10) NOT NULL default 'node',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `Index_node_path_string` USING BTREE (`node_path_string`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `ezstaticexport_export` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `start_date` varchar(45) NOT NULL,
  `type` varchar(45) NOT NULL,
  `path_string` varchar(45) NOT NULL,
  `status` int(1) unsigned NOT NULL,
  `target` varchar(100) NOT NULL,
  `schedule_type` varchar(45) NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `ezstaticexport_log` (
  `export_id` int(11) NOT NULL default '0',
  `date` int(11) NOT NULL,
  `message` varchar(255) NOT NULL,
  `status` int(10) unsigned NOT NULL,
  `id` int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  USING BTREE (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `ezstaticexport_scheduler` (
  `id` int(11) NOT NULL auto_increment,
  `date` int(11) NOT NULL,
  `path_string` varchar(255) NOT NULL,
  `type` varchar(10) NOT NULL,
  `recurrence` enum('hourly','daily','weekly','monthly','none') default 'none',
  `target` varchar(100) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
