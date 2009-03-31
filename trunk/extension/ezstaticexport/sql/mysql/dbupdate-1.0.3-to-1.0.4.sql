-- 
-- Prise en charge des ressources statiques pour l'export planifie
-- 

ALTER TABLE  `ezstaticexport_scheduler` ADD  `static_resources` TINYINT( 1 ) UNSIGNED ZEROFILL NOT NULL ;