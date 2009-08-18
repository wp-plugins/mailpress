<?php

// -----------------------------------------------
// Cryptographp v1.4
// (c) 2006-2007 Sylvain BRISON 
//
// www.cryptographp.com 
// cryptographp@alphpa.com 
//
// Licence CeCILL modifiée
// => Voir fichier Licence_CeCILL_V2-fr.txt)
// -----------------------------------------------


// -------------------------------------
// Configuration du fond du cryptogramme
// -------------------------------------

$cryptwidth  = 130;  // Largeur du cryptogramme (en pixels)
$cryptheight = 40;   // Hauteur du cryptogramme (en pixels)

$bgR  = 255;         // Couleur du fond au format RGB: Red (0->255)
$bgG  = 255;         // Couleur du fond au format RGB: Green (0->255)
$bgB  = 255;         // Couleur du fond au format RGB: Blue (0->255)

$bgclear = false;     // Fond transparent (true/false)
                     // Uniquement valable pour le format PNG

$bgimg = '';                 // Le fond du cryptogramme peut-être une image  
                             // PNG, GIF ou JPG. Indiquer le fichier image
                             // Exemple: $fondimage = 'photo.gif';
				                     // L'image sera redimensionnée si nécessaire
                             // pour tenir dans le cryptogramme.
                             // Si vous indiquez un répertoire plutôt qu'un 
                             // fichier l'image sera prise au hasard parmi 
                             // celles disponibles dans le répertoire

$bgframe = true;    // Ajoute un cadre de l'image (true/false)


// ----------------------------
// Configuration des caractères
// ----------------------------

// Couleur de base des caractères

$charR = 0;     // Couleur des caractères au format RGB: Red (0->255)
$charG = 0;     // Couleur des caractères au format RGB: Green (0->255)
$charB = 0;     // Couleur des caractères au format RGB: Blue (0->255)

$charcolorrnd = true;      // Choix aléatoire de la couleur.
$charcolorrndlevel = 2;    // Niveau de clarté des caractères si choix aléatoire (0->4)
                           // 0: Aucune sélection
                           // 1: Couleurs très sombres (surtout pour les fonds clairs)
                           // 2: Couleurs sombres
                           // 3: Couleurs claires
                           // 4: Couleurs très claires (surtout pour fonds sombres)

$charclear = 0;   // Intensité de la transparence des caractères (0->127)
                  // 0=opaques; 127=invisibles
	                // interessant si vous utilisez une image $bgimg
	                // Uniquement si PHP >=3.2.1

// Polices de caractères

//$tfont[] = 'Alanden_.ttf';       // Les polices seront aléatoirement utilisées.
//$tfont[] = 'bsurp___.ttf';       // Vous devez copier les fichiers correspondants
//$tfont[] = 'ELECHA__.TTF';       // sur le serveur.
$tfont[] = 'luggerbu.ttf';         // Ajoutez autant de lignes que vous voulez   
//$tfont[] = 'RASCAL__.TTF';       // Respectez la casse ! 
//$tfont[] = 'SCRAWL.TTF';  
//$tfont[] = 'WAVY.TTF';   
//$tfont[] = 'verdana.ttf';

// Caracteres autorisés
// Attention, certaines polices ne distinguent pas (ou difficilement) les majuscules 
// et les minuscules. Certains caractères sont faciles à confondre, il est donc
// conseillé de bien choisir les caractères utilisés.

$charel = '012345689';       // Caractères autorisés

$crypteasy = false;       // Création de cryptogrammes "faciles à lire" (true/false)
                         // composés alternativement de consonnes et de voyelles.

$charelc = 'BCDFGKLMPRTVWXZ';   // Consonnes utilisées si $crypteasy = true
$charelv = 'AEIOUY';              // Voyelles utilisées si $crypteasy = true

$difuplow = false;          // Différencie les Maj/Min lors de la saisie du code (true, false)

$charnbmin = 6;         // Nb minimum de caracteres dans le cryptogramme
$charnbmax = 6;         // Nb maximum de caracteres dans le cryptogramme

$charspace = 18;        // Espace entre les caracteres (en pixels)
$charsizemin = 10;      // Taille minimum des caractères
$charsizemax = 18;      // Taille maximum des caractères

$charanglemax  = 0;     // Angle maximum de rotation des caracteres (0-360)
$charup   = false;        // Déplacement vertical aléatoire des caractères (true/false)

// Effets supplémentaires

$cryptgaussianblur = false; // Transforme l'image finale en brouillant: méthode Gauss (true/false)
                            // uniquement si PHP >= 5.0.0
$cryptgrayscal = false;     // Transforme l'image finale en dégradé de gris (true/false)
                            // uniquement si PHP >= 5.0.0

// ----------------------
// Configuration du bruit
// ----------------------

$noisepxmin = 0;      // Bruit: Nb minimum de pixels aléatoires
$noisepxmax = 0;      // Bruit: Nb maximum de pixels aléatoires

$noiselinemin = 0;     // Bruit: Nb minimum de lignes aléatoires
$noiselinemax = 0;     // Bruit: Nb maximum de lignes aléatoires

$nbcirclemin = 0;      // Bruit: Nb minimum de cercles aléatoires 
$nbcirclemax = 0;      // Bruit: Nb maximim de cercles aléatoires

$noisecolorchar  = 2;  // Bruit: Couleur d'ecriture des pixels, lignes, cercles: 
                       // 1: Couleur d'écriture des caractères
                       // 2: Couleur du fond
                       // 3: Couleur aléatoire
                       
$brushsize = 1;        // Taille d'ecriture du princeaiu (en pixels) 
                       // de 1 à 25 (les valeurs plus importantes peuvent provoquer un 
                       // Internal Server Error sur certaines versions de PHP/GD)
                       // Ne fonctionne pas sur les anciennes configurations PHP/GD

$noiseup = false;      // Le bruit est-il par dessus l'ecriture (true) ou en dessous (false) 

// --------------------------------
// Configuration système & sécurité
// --------------------------------

$cryptformat = "png";   // Format du fichier image généré "GIF", "PNG" ou "JPG"
				                // Si vous souhaitez un fond transparent, utilisez "PNG" (et non "GIF")
				                // Attention certaines versions de la bibliotheque GD ne gerent pas GIF !!!

$cryptsecure = "md5";    // Méthode de crytpage utilisée: "md5", "sha1" ou "" (aucune)
                         // "sha1" seulement si PHP>=4.2.0
                         // Si aucune méthode n'est indiquée, le code du cyptogramme est stocké 
                         // en clair dans la session.
                       
$cryptusetimer = 0;        // Temps (en seconde) avant d'avoir le droit de regénérer un cryptogramme

$cryptusertimererror = 3;  // Action à réaliser si le temps minimum n'est pas respecté:
                           // 1: Ne rien faire, ne pas renvoyer d'image.
                           // 2: L'image renvoyée est "images/erreur2.png" (vous pouvez la modifier)
                           // 3: Le script se met en pause le temps correspondant (attention au timeout
                           //    par défaut qui coupe les scripts PHP au bout de 30 secondes)
                           //    voir la variable "max_execution_time" de votre configuration PHP

$cryptusemax = 1000;  // Nb maximum de fois que l'utilisateur peut générer le cryptogramme
                      // Si dépassement, l'image renvoyée est "images/erreur1.png"
                      // PS: Par défaut, la durée d'une session PHP est de 180 mn, sauf si 
                      // l'hebergeur ou le développeur du site en ont décidé autrement... 
                      // Cette limite est effective pour toute la durée de la session. 
                      
$cryptoneuse = false;  // Si vous souhaitez que la page de verification ne valide qu'une seule 
                       // fois la saisie en cas de rechargement de la page indiquer "true".
                       // Sinon, le rechargement de la page confirmera toujours la saisie.                          
                      
?>