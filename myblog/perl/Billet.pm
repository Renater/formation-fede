
package Billet;

use strict;

use Storable;
use POSIX;

my @ISA = qw(Exporter);
my @EXPORT = qw();

my $repertoire_execution = '/tmp';

## Création objet Billet
sub new {
  my $pkg = shift; ## First parameter is the object type
  my %param = @_;
  
  my $billet = {};

  ## Creation d'un nouveau billet
  if ($param{'titre'} &&
      $param{'contenu'}) {
    $billet->{'auteur'} = $param{'auteur'};
    $billet->{'titre'} = $param{'titre'};
    $billet->{'contenu'} = $param{'contenu'};
    $billet->{'date'} = time;
    $billet->{'date_affichable'} = &POSIX::strftime('%a, %d %b %Y %R %z',localtime($billet->{'date'}));
    $billet->{'id'} = &get_id;

   ## Chargement billet existant
  }elsif ($param{'id'}) {
    $billet = &charge_billet($param{'id'});
  }

  bless $billet;

 
  return $billet;
}

## Liste les billets disponibles
sub liste_billets {
  my @liste;

  opendir LISTE, $repertoire_execution.'/billets';
  foreach my $f (grep !/\./, readdir LISTE) {
    my $billet = &charge_billet($f);

    push @liste, $billet;
  }
  close LISTE;

  return @liste;
}

## Sauve le billet sur disque, dans le répertoire billets/
sub sauve {
  my $self = shift;

  ## On cree le répertoire s'il n'existe pas
  unless (-d $repertoire_execution.'/billets') {
    mkdir $repertoire_execution.'/billets', 0777;
  }

  unless (Storable::store($self, $repertoire_execution.'/billets/'.$self->{'id'})) {  
    print STDERR "Erreur sauvegarde billet $self->{'id'}\n";
    return undef;
  }
  
  return 1;
}

## Retourne l'objet sous forme d'un hash
sub as_hash {
  my $self = shift;
  my $hash = {};

  foreach my $attr (keys %{$self}) {
    $hash->{$attr} = $self->{$attr};
  }

  return $hash;
}

## montre le contenu d'un billet
sub montre {
  my $self = shift;

  foreach my $attr (keys %{$self}) {
    printf "%s\n\t%s\n", $attr, $self->{$attr};
  }
  
  return 1;
}

## Charge un billet
sub charge_billet {
  my $id = shift;
  my $billet;

  if (-f $repertoire_execution.'/billets/'.$id) {
    $billet = &Storable::retrieve($repertoire_execution.'/billets/'.$id);
    return $billet;
  }

  return undef;
}

## Allocate a new ID
sub get_id {
  my $id = 0;

  ## Read previous allocated ID
  if (-f $repertoire_execution.'/id.index') {
    open ID, $repertoire_execution.'/id.index';
    $id = <ID>; chomp $id;
    close ID;
  }

  $id++; ## Increment ID
  
  ## Save new ID
  open ID, ">$repertoire_execution/id.index";
  print ID "$id\n";
  close ID;

  return $id;
}

## Supprimer un billet
sub supprimer {
  my $self = shift;

  unlink $repertoire_execution.'/billets/'.$self->{'id'};

  return 1;
}

1;
