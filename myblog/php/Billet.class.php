<?php
class Billet {
  private static $repertoire_execution = '.';
  private static $datafile_separator_regexp = '/---===### DATA ### SEPARATOR ### [a-z0-9]{32} ===---/';
  
  var $id = '';
  var $titre = '';
  var $contenu = '';
  var $auteur = '';
  var $date = '';
  var $date_affichable = '';
  
  function __construct($param) {
    if(isset($param['titre']) && isset($param['contenu'])) {
      $this->titre = $param['titre'];
      $this->contenu = $param['contenu'];
      $this->auteur = $param['auteur'];
      $this->date = time();
      $this->date_affichable = strftime('%a, %d %b %Y %R %z', $this->date);
      $this->id = self::getId();
    }else if(isset($param['id']) && !empty($param['id'])) {
      $this->id = $param['id'];
      if(@is_readable(self::$repertoire_execution.'/billets/'.$param['id'])) {
	$ctn = (string)@file_get_contents(self::$repertoire_execution.'/billets/'.$param['id']);
	$fields = preg_split(self::$datafile_separator_regexp, $ctn);
	if(count($fields) != 4) {
	  $this->id = '';
	  return;
	}
	$this->titre = $fields[0];
	$this->contenu = $fields[1];
	$this->auteur = $fields[2];
	$this->date = $fields[3];
	$this->date_affichable = strftime('%a, %d %b %Y %R %z', $this->date);
      }
    }
  }
  
  function dataSeparator() {
    $chars = 'a0ze1rt2yu3io4pq5sd6fg7hj8kl9mwxcvbn';
    $salt = '';
    for($i=0; $i<32; $i++) $salt .= $chars[rand(0, strlen($chars) - 1)];
    return str_replace('[a-z0-9]{32}', $salt, self::$datafile_separator_regexp);
  }
  
  function sauve() {
    if(($f = fopen(self::$repertoire_execution.'/billets/'.$this->id, 'w')) !== null) {
      fwrite($f, $this->asData());
      fclose($f);
      return true;
    }
    echo 'Erreur de sauvegarde du billet '.$this->id;
    return false;
  }
  
  function asHash() {
    return array(
		 'titre' => $this->titre,
		 'contenu' => $this->contenu,
		 'auteur' => $this->auteur,
		 'date' => $this->date
		 );
  }
  
  function asData() {
    return implode($this->dataSeparator(), array_values($this->asHash()));
  }
  
  function montre() {
    foreach($this->asHash() as $k => $v) echo $k."\n\t".$v."\n";
  }
  
  function supprimer() {
    return unlink(self::$repertoire_execution.'/billets/'.$this->id);
  }
  
  
  static function liste() {
    $liste = array();
    if(($dir = scandir(self::$repertoire_execution.'/billets/')) !== false) {
      for($i=0; $i<count($dir); $i++) {
	if(is_file(self::$repertoire_execution.'/billets/'.$dir[$i]) && preg_match('/[0-9]+/', $dir[$i])) {
	  $liste[$dir[$i]] = new Billet(array('id' => $dir[$i]));
	}
      }
    }
    return $liste;
  }
  
  static function getId() {
    $id = (int)@file_get_contents(self::$repertoire_execution.'/id.index');
    $id++;
    if(($f = fopen(self::$repertoire_execution.'/id.index', 'w')) !== null) {
      fwrite($f, (string)$id);
      fclose($f);
    }
    return $id;
  }
}
?>
