#!/usr/bin/perl

## myBlog, un blog minimaliste en Perl, décembre 2007, copyright CRU
## ce blog a été écrit come application d'exemple pour un TP illustrant la 
## modification d'une application pour la rendre compatible avec le système Shibboleth

use strict;
use Billet;
use Template;
use CGI;
use CGI::Cookie;


my $requete = new Requete;
$requete->execute();
$requete->repondre();


package Requete;

## Nouvelle requete
sub new {
  my $pkg = shift;
  my $requete = {};

  my $http_query = new CGI;

  ## Check session
  my %cookies = fetch CGI::Cookie;
  if (defined %cookies && $cookies{'BLOG_USER'}) {
    $requete->{'utilisateur'} = $cookies{'BLOG_USER'}->value;
    my $users = &charge_users();
    $requete->{'role_utilisateur'} = $users->{$requete->{'utilisateur'}}{'role'};
  }
  

  my %in_vars = $http_query->Vars;
  $requete->{'param_entree'} = \%in_vars;

  ## Check the requested action
  if ($in_vars{'action'} ) {
    $requete->{'action'} = $in_vars{'action'};
  }else {
    $requete->{'action'} = 'index';
  }
  
  bless $requete, $pkg;

  return $requete;
}

## Execution d'une requete
sub execute {
  my $self = shift;
  
  do {
    ## Permet d'enchainer une action avec une autre
    $self->{'action'} = $self->{'next_action'} if ($self->{'next_action'});
    delete $self->{'next_action'}; ## Pour éviter de boucler

    if ($self->{'action'} eq 'index') {
      $self->req_index();
    }elsif ($self->{'action'} eq 'lire') {
      $self->req_lire();
    }elsif ($self->{'action'} eq 'ecrire') {
      $self->req_ecrire();
    }elsif ($self->{'action'} eq 'login') {
      $self->req_login();
    }elsif ($self->{'action'} eq 'logout') {
      $self->req_logout();
    }elsif ($self->{'action'} eq 'effacer') {
      $self->req_effacer();
    }
  } while ($self->{'next_action'});
  
  $self->{'param_sortie'}{'action'} = $self->{'action'}; ## Utile dans les pages
  $self->{'param_sortie'}{'chemin_cgi'} = $ENV{'SCRIPT_NAME'};
  foreach my $key (keys %{$self}) {
      ## On transmet toutes les variables au parser
      $self->{'param_sortie'}{$key} ||= $self->{$key}; #if (scalar $self->{$key});
  }

  return 1;
}

## Affiche le HTML
sub repondre {
  my $self = shift;

  if ($self->{'utilisateur'}) {
    my $cookie = new CGI::Cookie(-name=>'BLOG_USER',-value=>$self->{'utilisateur'});
    printf "Set-Cookie: %s\n", $cookie;
   ## Expiration des cookies
  }else {
    my $cookie = new CGI::Cookie(-name=>'BLOG_USER',-value=>'',-expires=>'-1M');
    printf "Set-Cookie: %s\n", $cookie;    
  }

  if ($self->{'url_redirection'}) {
    printf "Location: %s\n\n", $self->{'url_redirection'};

  }else {
    print "Content-Type: text/html\n\n";
    
    ## Parse template
    my $tt2 = Template->new();
    $tt2->process('templates/index.tt2.html', $self->{'param_sortie'}, \*STDOUT);
  }
  
}

## Traitement de l'index
sub req_index {
  my $self = shift;

  foreach my $b (&Billet::liste_billets()) {
    push @{$self->{'param_sortie'}{'liste_billets'}}, $b->as_hash();
  }
  
  $self->{'param_sortie'}{'titre'} = 'liste des billets';

  return 1;
}

## Voir un billet
sub req_lire {
  my $self = shift;
  
  my $billet = new Billet(id => $self->{'param_entree'}{'id'});
  $self->{'param_sortie'}{'billet'} = $billet->as_hash();

  $self->{'param_sortie'}{'titre'} = $billet->{'titre'};

  return 1;
}

## Ajouter un billet
sub req_ecrire {
  my $self = shift;
  
  ## Soumission de la page
  if ($self->{'param_entree'}{'titre'} && $self->{'param_entree'}{'contenu'}) {
    my $billet = new Billet(titre => $self->{'param_entree'}{'titre'},
			    auteur => $self->{'utilisateur'},
			    contenu => $self->{'param_entree'}{'contenu'});
    
    $billet->sauve();
    $self->{'next_action'} = 'index';

    ## accès au formulaire
  }else {
    $self->{'param_sortie'}{'titre'} = 'écrire un billet';
  }

  return 1;
}

## Effacer un billet
sub req_effacer {
  my $self = shift;

  my $billet = new Billet(id => $self->{'param_entree'}{'id'});
  $billet->supprimer();

  $self->{'next_action'} = 'index';

  return 1;
}

## Login
sub req_login {
  my $self = shift;

  ## Soumission du mdp
  if ($self->{'param_entree'}{'identifiant'} && $self->{'param_entree'}{'mot_de_passe'}) {
    
    my $users = &charge_users();
    if ($users->{$self->{'param_entree'}{'identifiant'}}) {
      my $user = $users->{$self->{'param_entree'}{'identifiant'}};
      if ($user->{'passwd'} eq $self->{'param_entree'}{'mot_de_passe'}) {
	$self->{'utilisateur'} = $self->{'param_entree'}{'identifiant'};
	$self->{'role_utilisateur'} = $user->{'role'};
	$self->{'next_action'} = 'index';
	return 1;
      }else {
	  push @{$self->{'param_sortie'}{'erreurs'}}, "Incorrect password";
	  return 0
	}
    }else {
      push @{$self->{'param_sortie'}{'erreurs'}}, "Unknown user";
      return 0;
    }

    ## Simple accès au formulaire
  }else {
    $self->{'param_sortie'}{'titre'} = 'login';
  }

  return 1;
}

## Logout
sub req_logout {
  my $self = shift;

  delete $self->{'utilisateur'};
  delete $self->{'role_utilisateur'};

  $self->{'next_action'} = 'index';
  
  return 1;
}

## Chargement des utilisateurs
sub charge_users {
  my %users;

  ## Parcour le fichier des utilisateurs
  ## Format user:mdp:role
  open USERS, 'users';
  while (<USERS>) {
    my ($user, $passwd, $role) = split /:/, $_; chomp $passwd;
    $users{$user} = {role => $role,
		    passwd => $passwd};
  }  

  return \%users;
}
