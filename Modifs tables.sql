ALTER TABLE `glpi_tickets`
ADD `id_contrat` int(11),
ADD `intervention_lieu` varchar(255),
ADD `intervention_jour` datetime,
ADD `intervention_heure_debut` time,
ADD `intervention_duree` time,
ADD `intervention_heure_fin` time,
ADD `intervention_type_installation` boolean,
ADD `intervention_type_reparation` boolean,
ADD `intervention_type_reseau` boolean, 
ADD `intervention_type_administration` boolean, 
ADD `intervention_type_formation` boolean,
ADD `intervention_type_autre` boolean, 
ADD `intervention_materiel_concerne` varchar(255),
ADD `intervention_detail_de_la_panne` longtext, 
ADD `intervention_detail_de_la_prestation` longtext,
ADD `intervention_intervenant` int(11),
ADD FOREIGN KEY (`id_contrat`) REFERENCES glpi_contracts(`id`);

DROP TABLE IF EXISTS `glpi_contractheures`;
CREATE TABLE `glpi_contractheures`(
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`id_contrat` int(11) NOT NULL,
	`heures_contrat` time NOT NULL DEFAULT '00:00:00',
	`date_dernier_achat` datetime NOT NULL,
	`date_fin_contrat` datetime NOT NULL,
	`heures_totales` time NOT NULL,
	`commentaire` text COLLATE utf8_unicode_ci,
	PRIMARY KEY (`id`),
	FOREIGN KEY (`id_contrat`) REFERENCES gplpi_contracts(`id`) ON DELETE CASCADE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_operation_heures`;
CREATE TABLE `glpi_operation_heures`(
	`id` int(11) NOT NULL AUTO_INCREMENT, 
	`id_ticket` int(11),
	`id_contrat` int(11) NOT NULL,
	`temps_initial` time NOT NULL,
	`temps_final` time NOT NULL,
	`date_operation` datetime NOT NULL,
	PRIMARY KEY (`id`),
	FOREIGN KEY (`id_ticket`) REFERENCES glpi_tickets(`id`) ON DELETE CASCADE,
	FOREIGN KEY (`id_contrat`) REFERENCES glpi_contracts(`id`) ON DELETE CASCADE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;






#Sélection du résultat du nombre d'heures total
SELECT ADDTIME(`glpi_contracts`.`heures_totales`, `glpi_contractheures`.`heures_contrat`) FROM `glpi_contracts` NATURAL JOIN `glpi_contractheures`

#Update du nomnbre d'heures total
UPDATE `glpi_contracts`, `glpi_contractheures`SET `glpi_contracts`.`heures_totales` = ADDTIME(`glpi_contracts`.`heures_totales`, `glpi_contractheures`.`heures_contrat`) WHERE `glpi_contracts`.`id` = `glpi_contractheures`.`contracts_id`