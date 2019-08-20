<?php defined('SYSPATH') OR die('No direct access allowed.');

$lang = Array
(

'username' => Array 
(
	'required' => 'Ce champ est obligatoire.', 
	'alpha_numeric' => 'Merci de n&rsquo;utiliser que des caract&egrave;res alphanum&eacute;riques.', 
	'length' => 'Le texte doit &ecirc;tre compos&eacute; de 5 &agrave; 20 caract&egrave;res.', 
	'username_exists' => 'Ce nom est d&eacute;j&agrave; utilis&eacute;.', 
	'not_found' => 'Identifiant ou mot de passe incorrects.', 
	'default' => 'Entr&eacute;e non valide.', 
),

'password' => Array 
(
	'required' => 'Ce champ est obligatoire.', 
	'length' => 'Le mot de passe doit comporter au moins 5 caract&egrave;res.', 
	'default' => 'Entr&eacute;e non valide.', 
),

'password_confirm' => Array 
(
	'required' => 'Ce champ est obligatoire.', 
	'matches' => 'Les mots de passe ne correspondent pas.', 
	'default' => 'Entr&eacute;e non valide.', 
),

'email' => Array 
(
	'email' => 'Veuillez sp&eacute;cifier une adresse mail valide.', 
	'length' => 'Ce texte ne peut pas comporter plus de 30 caract&egrave;res.', 
	'email_exists' => 'Cette adresse mail est d&eacute;j&agrave; utilis&eacute;e.', 
	'required' => 'Ce champ est obligatoire.', 
	'blocked_domain' => 'Nous n&rsquo;acceptons pas les adresses mail de ce type.', 
	'default' => 'Entr&eacute;e non valide.', 
),

'captchaanswer' => Array 
(
	'captchaerror' => 'S\'il vous plaît résoudre le captcha.', 
),

'emailconfirm' => Array 
(
	'matches' => 'Les adresses mail ne correspondent pas.', 
	'required' => 'Ce champ est obligatoire.', 
	'default' => 'Entr&eacute;e non valide.', 
),

'referral_id' => Array 
(
	'numeric' => 'Merci d&rsquo;indiquer un No d&rsquo;identification valide (il doit &ecirc;tre compos&eacute; uniquement de chiffres).', 
	'id_notexisting' => 'Le No d&rsquo;identification saisi n&rsquo;existe pas dans notre base de donn&eacute;es.', 
),

'accepttos' => Array 
(
	'tos_notaccepted' => 'Vous devez lire et accepter les conditions d&rsquo;utilisation.', 
),

'charname' => Array 
(
	'required' => 'Ce champ est obligatoire.', 
	'wrongformat' => 'Merci de ne saisir que des caract&egrave;res alphab&eacute;tiques europ&eacute;ens (pas de caract&egrave;res sp&eacute;ciaux ni de chiffres).', 
	'length' => 'Merci de saisir 5 &agrave; 20 caract&egrave;res.', 
	'username_exists' => 'Ce nom est d&eacute;j&agrave; utilis&eacute;.', 
	'not_found' => 'Identifiant ou mot de passe incorrects.', 
	'default' => 'Entr&eacute;e non valide.', 
),

'charsurname' => Array 
(
	'required' => 'Ce champ est obligatoire.', 
	'wrongformat' => 'Merci de ne saisir que des caract&egrave;res alphab&eacute;tiques europ&eacute;ens (pas de caract&egrave;res sp&eacute;ciaux ni de chiffres).', 
	'length' => 'Merci de saisir 5 &agrave; 20 caract&egrave;res.', 
	'username_exists' => 'Ce nom est d&eacute;j&agrave; utilis&eacute;.', 
	'not_found' => 'Identifiant ou mot de passe incorrects.', 
	'default' => 'Entr&eacute;e non valide.', 
),

'charpoints' => Array 
(
	'chars' => 'Vous disposez de points &agrave; r&eacute;partir dans la section "Statistique".', 
	'notequal_50' => 'Le total des points doit &ecirc;tre de 50.', 
	'notinrange' => 'Toutes les caract&eacute;ristiques doivent &ecirc;tre comprises entre 1 et 20.', 
),

'charspokenlanguage1' => Array 
(
	'required' => 'Le domaine est obligatoire.', 
),

'to' => Array 
(
	'required' => 'Ce champ est obligatoire.', 
	'length' => 'Merci de saisir 1 &agrave; 20 caract&egrave;res.', 
	'char_not_exist' => 'Le destinataire n&rsquo;a pas &eacute;t&eacute; trouv&eacute;.', 
	'incoherentmode' => 'Si vous choisissez l&rsquo;envoi en masse, vous ne pouvez pas sp&eacute;cifier le destinataire.', 
),

'choosenkingdom_id' => Array 
(
	'required' => 'Please choose a Kingdom.', 
),

'subject' => Array 
(
	'required' => 'Ce champ est obligatoire.', 
	'length' => 'Merci de saisir 1 &agrave; 20 caract&egrave;res.', 
	'postcontainsbadwords' => 'Le champ contient les mots interdits (marqués avec ***).', 
),

'body' => Array 
(
	'required' => 'Ce champ est obligatoire.', 
	'postcontainsbadwords' => 'Le champ contient les mots interdits (marqués avec ***).', 
),

'law_name' => Array 
(
	'required' => 'Ce champ est obligatoire.', 
	'length' => 'Merci de saisir 1 &agrave; 20 caract&egrave;res.', 
),

'law_desc' => Array 
(
	'required' => 'Ce champ est obligatoire.', 
	'length' => 'Merci de saisir entre 3 et 2 018 caract&egrave;res.', 
),

'description' => Array 
(
	'alpha_numeric' => 'Merci de n&rsquo;utiliser que des caract&egrave;res alphanum&eacute;riques.', 
	'length' => 'Merci de saisir entre 1 et 2018 caract&egrave;res.', 
),

'boarddescription' => Array 
(
	'required' => 'Le domaine est obligatoire.', 
	'length' => 'La longueur de réponse doit être de 5 à 255 caractères.', 
),

'old_password' => Array 
(
	'required' => 'Ce champ est obligatoire.', 
	'matches' => 'Le mot de passe est incorrect.', 
),

'promomessage' => Array 
(
	'length' => 'Merci de saisir moins de 255 caract&egrave;res.', 
),

'ann_title' => Array 
(
	'required' => 'Ce champ est obligatoire.', 
	'length' => 'Merci de saisir 1 &agrave; 20 caract&egrave;res.', 
),

'name' => Array 
(
	'required' => 'Le domaine est obligatoire.', 
	'length' => 'La longueur de réponse doit être de 5 à 50 caractères.', 
),

'ann_desc' => Array 
(
	'required' => 'Ce champ est obligatoire.', 
	'length' => 'Merci de saisir entre 3 et 4 096 caract&egrave;res.', 
),

'region' => Array 
(
	'required' => 'Ce champ est obligatoire.', 
	'length' => 'Merci de saisir entre 3 et 50 caract&egrave;res.', 
	'doesnotexist' => 'La r&eacute;gion est introuvable.', 
),

'quantity' => Array 
(
	'required' => 'Ce champ est obligatoire.', 
),

'slogan' => Array 
(
	'length' => 'Merci de saisir entre 1 et 30 caract&egrave;res.', 
),

'group_name' => Array 
(
	'required' => 'Ce champ est obligatoire.', 
	'length' => 'Merci de saisir entre 3 et 60 caract&egrave;res.', 
	'groupname_exists' => 'Ce nom est d&eacute;j&agrave; utilis&eacute;.', 
),

'group_description' => Array 
(
	'required' => 'Ce champ est obligatoire.', 
	'length' => 'Merci de saisir entre 3 et 255 caract&egrave;res.', 
),

'group_image' => Array 
(
	'default' => 'Entr&eacute;e non valide.', 
	'valid' => 'L&rsquo;image n&rsquo;est pas valide.', 
	'required' => 'Image requise.', 
	'type' => 'Le format d\'image pris en charge seulement en .png', 
	'size' => 'Le blason doit faire moins de 300 Kb.', 
),

'group_charname' => Array 
(
	'required' => 'Ce champ est obligatoire.', 
	'char_not_exist' => 'Ce personnage n&rsquo;existe pas.', 
),

'group_message' => Array 
(
	'required' => 'Ce champ est obligatoire.', 
	'length' => 'Merci de saisir entre 1 et 1 024 caract&egrave;res.', 
),

'group_subject' => Array 
(
	'required' => 'Le champ est obligatoire.', 
	'length' => 'La longueur du champ doit être comprise entre 1 et 255 caractères.', 
),

'independentregion' => Array 
(
	'required' => 'Ce champ est obligatoire.', 
),

'captain' => Array 
(
	'required' => 'Ce champ est obligatoire.', 
),

'kingcandidate' => Array 
(
	'doesnotexist' => 'Ce joueur n&rsquo;existe pas.', 
),

'foreignershourlycost' => Array 
(
	'default' => 'La valeur doit &ecirc;tre sup&eacute;rieure &agrave; 0.', 
),

'message' => Array 
(
	'default' => 'Ce champ est obligatoire.', 
),

'validity' => Array 
(
	'default' => 'La valeur doit &ecirc;tre sup&eacute;rieure &agrave; 2.', 
),

'title' => Array 
(
	'required' => 'Ce champ est obligatoire.', 
	'length' => 'Merci de saisir entre 3 et 50 caract&egrave;res.', 
),

'reason' => Array 
(
	'required' => 'Ce champ est obligatoire.', 
),

'wardrobe_parts' => Array 
(
	'default' => 'L&rsquo;image doit &ecirc;tre en format png et ne pas d&eacute;passer 150Kb.', 
),

'date' => Array 
(
	'required' => 'Ce champ est obligatoire.', 
),

'time' => Array 
(
	'required' => 'Ce champ est obligatoire.', 
),

'location' => Array 
(
	'required' => 'Ce champ est obligatoire.', 
),

'domainname' => Array 
(
	'required' => 'Ce champ est obligatoire.', 
),

);

?>