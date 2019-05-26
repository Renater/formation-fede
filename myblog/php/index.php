<?php
	include_once('Blog.class.php');
	include_once('Billet.class.php');
	
	$blog = new Blog();
	$blog->execute();
	$blog->repondre();
?>
