<?php

/* *********************************************************/
/* Challenger V3 : Gestion de l'organisation du Challenge **/
/* Créé par Raphaël Kichot' MOULIN *************************/
/* raphael.moulin@ecl13.ec-lyon.fr *************************/
/* *********************************************************/
/* actions/public/login_admin.php **************************/
/* Gére la connexion pour l'administration *****************/
/* *********************************************************/
/* Dernière modification : le 20/11/14 *********************/
/* *********************************************************/


if (!empty($_SESSION['admin']))
	die(header('location:'.url('admin/accueil', false, false)));


require DIR.'includes/_ecl/CAS.php';
phpCAS::client(CAS_VERSION_2_0, CONFIG_CAS_HOST, CONFIG_CAS_PORT, CONFIG_CAS_CONTEXT);
phpCAS::setNoCasServerValidation();


if (!phpCAS::checkAuthentication())   
	phpCAS::forceAuthentication(); 

else
	$cas = phpCAS::getUser();



if (!empty($_POST['login_admin']) &&
	!empty($_POST['login']) &&
	!empty($_POST['pass']) ||
	!empty($cas)) {

	if (empty($_SESSION['tentatives']) ||
		time() - $_SESSION['tentatives']['start'] > APP_WAIT_AUTH)
		$_SESSION['tentatives'] = [
			'start' => time(),
			'count' => 0];


	if (empty($cas)) {

		$hash = hashPass($_POST['pass']);
		$user = $pdo->query('SELECT '.
				'id '.
			'FROM admins WHERE '.
				'auth_type = "db" AND '.
				'login = "'.secure($_POST['login']).'" AND '.
				'pass = "'.$hash.'"') or DEBUG_ACTIVE && die(print_r($pdo->errorInfo()));
	
	} else {

		$user = $pdo->query('SELECT '.
				'id '.
			'FROM admins WHERE '.
				'auth_type = "cas" AND '.
				'login = "'.secure($cas).'"') or DEBUG_ACTIVE && die(print_r($pdo->errorInfo()));

	}

	$user = $user->fetch(PDO::FETCH_ASSOC) ;

	if (!empty($user) && 
		$_SESSION['tentatives']['count'] < APP_MAX_TRY_AUTH) {
		$_SESSION['admin'] = [
			'start' => time(),
			'last' => time(),
			'auth_type' => empty($cas) ? 'db' : 'cas',
			'login' => empty($cas) ? secure($_POST['login']) : secure($cas),
			'user' => $user['id'],
		];
		die(header('location:'.url('admin/accueil', false, false)));
	}

	else if (empty($cas)) {
		$error = true;
		$_SESSION['tentatives']['count']++;
	}
}


//Inclusion du bon fichier de template
require DIR.'templates/public/login_admin.php';