<?php
header('Content-type: text/html; charset=utf-8');
echo '<?xml version="1.0" encoding="utf-8" ?>';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xml:lang="fr" xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<style type="text/css"><!--
			.erreur { color : red }
			.login {float: left}
			.menu {float: right}
			.footer {text-align: center}
			h1 {text-align: center}
			.billet {border: 1px coral solid; padding: 5px; background-color: lightgrey; margin: 5px }
			.contenu {background-color: coral; border: 1px black solid;}
		--></style>
		<title> monBlog - <?php echo $this->titre; ?></title>
	</head>
	<body>
		<div class="login">
			<?php if($this->utilisateur != '') echo 'Logged in as <strong>'.$this->utilisateur.'</strong><br />'; ?>
			<?php if($this->role_utilisateur != '') echo 'Role : '.$this->role_utilisateur.'<br />'; ?>
		</div>

		<div class="menu">
			<a href="?action=index">liste des billets</a> | 
			<?php if($this->utilisateur != '') echo '<a href="?action=ecrire">écrire un billet</a>'; else echo 'écrire un billet'; ?> | 
			<?php if($this->utilisateur != '') echo '<a href="?action=logout">logout</a>'; else echo '<a href="?action=login">login</a>'; ?>
		</div>


		<h1><?php echo $this->titre; ?></h1>

		<?php if(count($this->erreurs)) { ?>
		<div class="erreur">
		<?php for($i=0; $i<count($this->erreurs); $i++) echo $this->erreurs[$i].'<br />'; ?>
		</div>
		<?php } ?>
		
		<div>
		<?php if($this->action == 'index') { ?>
		<?php foreach($this->billets as $id => $billet) { ?>
		<div class="billet">
		  <?php echo $billet->titre; ?><br />
		  <?php echo $billet->date_affichable; ?> - <?php if($billet->auteur != '') echo $billet->auteur; else echo 'anonyme'; ?><br />
		 <div class="contenu">
		<?php echo nl2br(substr($billet->contenu, 0, 250)); if(strlen($billet->contenu) > 250) echo ' [...]'; ?>
		</div>
		<a href="?action=lire&id=<?php echo $id; ?>">lire ce billet</a><?php if(($this->role_utilisateur == 'admin') || ($this->utilisateur == $billet->auteur)) { ?> - <a href="?action=effacer&id=<?php echo $id; ?>">effacer ce billet</a><?php } ?><br/>
		</div>
		<?php } ?>

		<?php }else if($this->action == 'lire') { ?>
		<div>Auteur : <?php echo $this->billet->auteur; ?></div>
		<div>Date : <?php echo $this->billet->date_affichable; ?></div>
		<div class="contenu"><?php echo nl2br($this->billet->contenu); ?></div>

		<?php }else if($this->action == 'ecrire') { ?>
		<div>
		<form action="" method="post">
		<fieldset>
		  <label for="titre">Titre : </label>
		  <input type="text" id="titre" name="titre" size="60"/><br />

		  <textarea id="contenu" name="contenu" cols="90" rows="25"></textarea>
		<br />

		  <input type="hidden" name="action" value="ecrire">
		  <input type="submit" name="intitule_bouton" value="Ajouter ce billet" />
		</fieldset>
		</form>
		</div>

		<?php }else if($this->action == 'login') { ?>
		<div>
		<form action="" method="post">
		<fieldset>
		  <label for="identifiant">Identifiant : </label>
		  <input type="text" id="identifiant" name="identifiant" size="20"/><br />

		  <label for="mot_de_passe">Mot de passe : </label>
		  <input type="password" id="mot_de_passe" name="mot_de_passe" size="20"/><br />
		<br />

		  <input type="hidden" name="action" value="login">
		  <input type="submit" name="intitule_bouton" value="Login" />
		</fieldset>
		</form>
		</div>
		<?php } ?>
		</div>

		<div class="footer">powered by myBlog</div>

	</body>
</html>
