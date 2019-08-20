<?php defined('SYSPATH') OR die('No direct access allowed.');

$lang = Array
(

'username' => Array 
(
	'required' => 'Dies ist ein Pflichtfeld.', 
	'alpha_numeric' => 'Die Eingabe muss alphanummerisch sein.', 
	'length' => '5-20 Zeichen', 
	'username_exists' => 'Der Name ist bereits vergeben.', 
	'not_found' => 'Falscher Benutzername oder Passwort.', 
	'default' => 'keine gültige Eingabe', 
),

'password' => Array 
(
	'required' => 'Dies ist ein Pflichtfeld.', 
	'length' => 'Das Passwort muss mind. 5 Zeichen lang sein.', 
	'default' => 'keine gültige Eingabe', 
),

'password_confirm' => Array 
(
	'required' => 'Dies ist ein Pflichtfeld.', 
	'matches' => 'Die Passworte stimmen nicht überein.', 
	'default' => 'keine gültige Eingabe', 
),

'email' => Array 
(
	'email' => 'Bitte gib eine gültige Emailadresse an.', 
	'length' => 'max. 30 Zeichen', 
	'email_exists' => 'Diese Emailadresse wird bereits genutzt.', 
	'required' => 'Dies ist ein Pflichtfeld.', 
	'blocked_domain' => 'Wir akzeptieren keine temporären Emailkonten.', 
	'default' => 'keine gültige Eingabe', 
),

'captchaanswer' => Array 
(
	'captchaerror' => 'Bitte fülle das Captcha aus.', 
),

'emailconfirm' => Array 
(
	'matches' => 'Die Emailadressen stimmen nicht überein.', 
	'required' => 'Dies ist ein Pflichtfeld.', 
	'default' => 'keine gültige Eingabe', 
),

'referral_id' => Array 
(
	'numeric' => 'Bitte gib eine gültige Empfehlungs-ID an.', 
	'id_notexisting' => 'Die angegebene ID wurde nicht in unserer Datenbank gefunden.', 
),

'accepttos' => Array 
(
	'tos_notaccepted' => 'Du musst die Datenschutzbestimmungen und AGB lesen und bestätigen.', 
),

'charname' => Array 
(
	'required' => 'Dies ist ein Pflichtfeld.', 
	'wrongformat' => 'Dieses Feld darf nur europäische Zeichen, keine Sonderzeichen oder Zahlen enthalten.', 
	'length' => '5-20 Zeichen', 
	'username_exists' => 'Der Name ist bereits vergeben.', 
	'not_found' => 'Falscher Benutzername oder Passwort.', 
	'default' => 'keine gültige Eingabe', 
),

'charsurname' => Array 
(
	'required' => 'Dies ist ein Pflichtfeld.', 
	'wrongformat' => 'Dieses Feld darf nur europäische Zeichen, keine Sonderzeichen oder Zahlen enthalten.', 
	'length' => '5-20 Zeichen', 
	'username_exists' => 'Der Name ist bereits vergeben.', 
	'not_found' => 'falscher Benutzername oder Passwort', 
	'default' => 'keine gültige Eingabe', 
),

'charpoints' => Array 
(
	'chars' => 'Du kannst noch einige Punkte bei den Eigenschaften vergeben.', 
	'notequal_50' => 'Die Gesamtsumme der Eigenschaftspunkte muss 50 ergeben.', 
	'notinrange' => 'Eine oder mehr Eigenschaften liegen nicht im vorgeschriebenen Bereich (1-20).', 
),

'charspokenlanguage1' => Array 
(
	'required' => 'Pflichtfeld', 
),

'to' => Array 
(
	'required' => 'Dies ist ein Pflichtfeld.', 
	'length' => '1-20 Zeichen', 
	'char_not_exist' => 'Empfänger nicht gefunden', 
	'incoherentmode' => 'Ihr könnt nicht den Rundbrief auswählen und gleichzeitig einen Empfänger angeben.', 
),

'choosenkingdom_id' => Array 
(
	'required' => 'Bitte wähle ein Königreich aus.', 
),

'subject' => Array 
(
	'required' => 'Dies ist ein Pflichtfeld.', 
	'length' => '1-20 Zeichen', 
	'postcontainsbadwords' => 'Das Feld enthält verbotene Worte (mit *** markiert).', 
),

'body' => Array 
(
	'length' => 'Die Feldlänge muss zwischen 20 und 4096 Zeichen betragen.', 
	'required' => 'Dies ist ein Pflichtfeld.', 
	'postcontainsbadwords' => 'Das Feld enthält verbotene Worte (mit *** markiert).', 
),

'law_name' => Array 
(
	'required' => 'Dies ist ein Pflichtfeld.', 
	'length' => '3-50 Zeichen', 
),

'law_desc' => Array 
(
	'required' => 'Dies ist ein Pflichtfeld.', 
	'length' => '3-2048 Zeichen', 
),

'description' => Array 
(
	'alpha_numeric' => 'die Eingabe muss alphanummerisch sein', 
	'length' => '1-2048 Zeichen', 
),

'boarddescription' => Array 
(
	'required' => 'Dies ist ein Pflichtfeld.', 
	'length' => '5-255 Zeichen', 
),

'old_password' => Array 
(
	'required' => 'dies ist ein Pflichtfeld.', 
	'matches' => 'Das aktuelle Passwort ist falsch.', 
),

'promomessage' => Array 
(
	'length' => 'Eingabe weniger als 255 Zeichen', 
),

'ann_title' => Array 
(
	'required' => 'Dies ist ein Pflichtfeld.', 
	'length' => '3-50 Zeichen', 
),

'name' => Array 
(
	'required' => 'Dies ist ein Pflichtfeld.', 
	'length' => '5-50 Zeichen', 
),

'ann_desc' => Array 
(
	'required' => 'Dies ist ein Pflichtfeld.', 
	'length' => '3-4096 Zeichen', 
),

'region' => Array 
(
	'required' => 'Dies ist ein Pflichtfeld.', 
	'length' => '3-50 Zeichen', 
	'doesnotexist' => 'Region nicht gefunden', 
),

'quantity' => Array 
(
	'required' => 'Dies ist ein Pflichtfeld.', 
),

'slogan' => Array 
(
	'length' => '1-30 Zeichen', 
),

'group_name' => Array 
(
	'required' => 'Dies ist ein Pflichtfeld.', 
	'length' => '3-60 Zeichen', 
	'groupname_exists' => 'Der gewählte Name ist bereits vergeben.', 
),

'group_description' => Array 
(
	'required' => 'Dies ist ein Pflichtfeld.', 
	'length' => '3-255 Zeichen', 
),

'group_image' => Array 
(
	'default' => 'ungültige Eingabe', 
	'valid' => 'ungültiges Bild', 
	'required' => 'erforderl.', 
	'type' => 'Das Bild muss im png-Format sein.', 
	'size' => 'Das Bild des Wappens darf 300 kB nicht überschreiten.', 
),

'group_charname' => Array 
(
	'required' => 'Dies ist ein Pflichtfeld.', 
	'char_not_exist' => 'Dieser Charakter existiert nicht.', 
),

'group_message' => Array 
(
	'required' => 'Dies ist ein Pflichtfeld.', 
	'length' => '1-1024 Zeichen', 
),

'group_subject' => Array 
(
	'required' => 'Pflichtfeld', 
	'length' => '1-255 Zeichen', 
),

'independentregion' => Array 
(
	'required' => 'Dies ist ein Pflichtfeld.', 
),

'captain' => Array 
(
	'required' => 'Dies ist ein Pflichtfeld.', 
),

'kingcandidate' => Array 
(
	'doesnotexist' => 'Der Spieler existiert nicht.', 
),

'foreignershourlycost' => Array 
(
	'default' => 'Der Wert muss > 0 sein.', 
),

'message' => Array 
(
	'default' => 'Dies ist ein Pflichtfeld.', 
),

'validity' => Array 
(
	'default' => 'einen Wert >2 eingeben', 
),

'title' => Array 
(
	'required' => 'Dies ist ein Pflichtfeld.', 
	'length' => '3-50 Zeichen', 
),

'reason' => Array 
(
	'required' => 'Dies ist ein Pflichtfeld.', 
),

'wardrobe_parts' => Array 
(
	'default' => 'Bilddatei nicht gültig, das Format muss png sein und die Grösse <= 150 k.', 
),

'date' => Array 
(
	'required' => 'Pflichtfeld', 
),

'time' => Array 
(
	'required' => 'Pflichtfeld', 
),

'location' => Array 
(
	'required' => 'Pflichtfeld', 
),

'domainname' => Array 
(
	'required' => 'Pflichtfeld', 
),

'discussionurl' => Array 
(
	'required' => 'Pflichtfeld', 
),

);

?>