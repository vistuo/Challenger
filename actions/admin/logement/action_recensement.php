<?php

/* *********************************************************/
/* Challenger V3 : Gestion de l'organisation du Challenge **/
/* Créé par Raphaël Kichot' MOULIN *************************/
/* raphael.moulin@ecl13.ec-lyon.fr *************************/
/* *********************************************************/
/* actions/admin/logement/action_recensement.php ***********/
/* Liste des batiments pour le recensement *****************/
/* *********************************************************/
/* Dernière modification : le 16/02/15 *********************/
/* *********************************************************/


$batiments = $pdo->query('SELECT '.
		'c0.batiment, '.
		'(SELECT '.
				'COUNT(c1.id) '.
			'FROM chambres AS c1 WHERE '.
				'(c1.etat = "autorise" OR '.
				'c1.etat = "amies") AND '.
				'c1.batiment = c0.batiment) AS nb_active, '.
		'(SELECT '.
				'COUNT(c2.id) '.
			'FROM chambres AS c2 WHERE '.
				'c2.etat = "refuse" AND '.
				'c2.batiment = c0.batiment) AS nb_inactive '.
	'FROM chambres AS c0 '.
	'GROUP BY c0.batiment '.
	'ORDER BY c0.batiment')
	or DEBUG_ACTIVE && die(print_r($pdo->errorInfo()));
$batiments = $batiments->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_UNIQUE);


foreach (str_split('UVTXABC') AS $batiment) {

	if (empty($batiments[$batiment]))
		$batiments[$batiment] = array(
			'nb_active' => 0,
			'nb_inactive' => 0);

	$batiments[$batiment]['nb_etages'] = in_array($batiment, array('A', 'B', 'C')) ? 4 : 6;
	$batiments[$batiment]['nb_filles_max_chambre'] = in_array($batiment, array('A', 'B', 'C')) ? 5 : 3;
	$batiments[$batiment]['nb_chambres'] = 0;
	

	foreach (range(0, $batiments[$batiment]['nb_etages']) as $etage) {
		$batiments[$batiment]['nb_chambres'] += $etage == 0 ? 
			($batiments[$batiment]['nb_etages'] == 6 ? 8 : 17) :
			($batiments[$batiment]['nb_etages'] == 6 ? 16 : 17);
	}

}


//Inclusion du bon fichier de template
require DIR.'templates/admin/logement/recensement.php';
