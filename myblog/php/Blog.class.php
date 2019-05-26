<?php
	include_once('Billet.class.php');
	
	class Blog {
		static $cgi = 'index.php';
		
		var $utilisateur = '';
		var $role_utilisateur = '';
		var $action = '';
		var $url_redirection = '';
		var $titre = '';
		var $next_action = '';
		
		var $billets = array();
		var $billet = null;
		
		var $erreurs = array();
		
		
		function __construct() {
			if(isset($_COOKIE['BLOG_USER']) && !empty($_COOKIE['BLOG_USER'])) {
				$this->utilisateur = $_COOKIE['BLOG_USER'];
				$users = self::chargeUsers();
				$this->role_utilisateur = $users[$this->utilisateur]['role'];
			}
			
			$this->action = 'index';
			if(isset($_REQUEST['action']) && !empty($_REQUEST['action'])) $this->action = $_REQUEST['action'];
		}
		
		function execute() {
			do {
				if($this->next_action != '') {
					$this->action = $this->next_action;
					$this->next_action = '';
				}
				
				if($this->action == 'index') {
					$this->req_index();
				}else if($this->action == 'lire') {
					$this->req_lire();
				}else if($this->action == 'ecrire') {
					$this->req_ecrire();
				}else if($this->action == 'login') {
					$this->req_login();
				}else if($this->action == 'logout') {
					$this->req_logout();
				}else if($this->action == 'effacer') {
					$this->req_effacer();
				}
			} while($this->next_action != '');
		}
		
		function repondre() {
			if($this->utilisateur != '') {
				setcookie('BLOG_USER', $this->utilisateur);
			}else {
				setcookie('BLOG_USER', $this->utilisateur, -3600);
			}

			if($this->url_redirection != '') {
				header('Location: '.$this->url_redirection);
			}else{
				include_once('templates/index.tpl.php');
			}
		}
		
		function req_index() {
			$this->billets = Billet::liste();
			$this->titre = 'liste des billets';
		}

		function req_lire() {
			if(!isset($_REQUEST['id']) || empty($_REQUEST['id'])) {
				$this->next_action = 'index';
				return;
			}
			$this->billet = new Billet(array('id' => $_REQUEST['id']));
			$this->titre = $this->billet->titre;
		}

		function req_ecrire() {
			if(isset($_REQUEST['titre']) && !empty($_REQUEST['titre']) && isset($_REQUEST['contenu']) && !empty($_REQUEST['contenu'])) {
			  $billet = new Billet(array('titre' => $_REQUEST['titre'], 'auteur' => $this->utilisateur, 'contenu' => $_REQUEST['contenu']));
				$billet->sauve();
				$this->next_action = 'index';
			}else{
				$this->titre = 'écrire un billet';
			}
		}

		function req_effacer() {
			if(!isset($_REQUEST['id']) || empty($_REQUEST['id'])) {
				$this->next_action = 'index';
				return;
			}
			$billet = new Billet(array('id' => $_REQUEST['id']));
			$billet->supprimer();
			$this->next_action = 'index';
		}

		function req_login() {
			if(isset($_REQUEST['identifiant']) && !empty($_REQUEST['identifiant']) && isset($_REQUEST['mot_de_passe']) && !empty($_REQUEST['mot_de_passe'])) {
				$users = self::chargeUsers();
				if(isset($users[$_REQUEST['identifiant']])) {
					$user = $users[$_REQUEST['identifiant']];
					if($user['passwd'] == $_REQUEST['mot_de_passe']) {
						$this->utilisateur = $_REQUEST['identifiant'];
						$this->role_utilisateur = $user['role'];
						$this->next_action = 'index';
					}else{
						$this->errors[] = "Incorrect password";
					}
				}else{
					$this->errors[] = "Unknown user";
				}
			}else{
				$this->titre = 'login';
			}
		}

		function req_logout() {
			$this->utilisateur = '';
			$this->role_utilisateur = '';
			$this->next_action = 'index';
		}
		
		
		static function chargeUsers() {
			$users = array();
			$list = explode("\n", (string)@file_get_contents('./users'));
			for($i=0; $i<count($list); $i++) {
				if(preg_match('/^([^:]+):([^:]+):(.*)$/', $list[$i], $reg)) {
					$users[$reg[1]] = array(
						'role' => $reg[3],
						'passwd' => $reg[2]
					);
				}
			}
			return $users;
		}
	}
?>
