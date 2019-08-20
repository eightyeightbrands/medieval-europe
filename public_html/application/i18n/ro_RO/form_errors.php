<?php defined('SYSPATH') OR die('No direct access allowed.');

$lang = Array
(

'username' => Array 
(
	'required' => 'Completarea rubricii este obligatorie.', 
	'alpha_numeric' => 'Această rubrică trebuie să fie alfanumerică.', 
	'length' => 'Rubrica poate conţine între 5 la 20 de caractere.', 
	'username_exists' => 'Numele este deja folosit.', 
	'not_found' => 'Numele sau parola greşită.', 
	'default' => 'Nu aţi introdus date valide.', 
),

'password' => Array 
(
	'required' => 'Completarea rubricii este obligatorie.', 
	'length' => 'Parola trebuie să aibă cel puţin 5 caractere.', 
	'default' => 'Nu aţi introdus date valide.', 
),

'password_confirm' => Array 
(
	'required' => 'Completarea rubricii este obligatorie.', 
	'matches' => 'Parolele nu corespund.', 
	'default' => 'Nu aţi introdus date valide.', 
),

'email' => Array 
(
	'email' => 'Te rugăm să specifici o adresă de email validă. ', 
	'length' => 'Lungimea maximă a rubricii este de 30 de caractere.', 
	'email_exists' => 'Această adresă de email este deja folosită.', 
	'required' => 'Completarea rubricii este obligatorie.', 
	'blocked_domain' => 'Nu acceptăm adrese de email temporare.', 
	'default' => 'Nu aţi introdus date valide.', 
),

'captchaanswer' => Array 
(
	'captchaerror' => 'Vă rugăm să rezolve captcha.', 
),

'emailconfirm' => Array 
(
	'matches' => 'Adresa de email nu corespunde.', 
	'required' => 'Completarea rubricii este obligatorie.', 
	'default' => 'Nu aţi introdus date valide.', 
),

'referral_id' => Array 
(
	'numeric' => 'Te rugăm să inserezi un ID valid de recomandare.', 
	'id_notexisting' => 'ID-ul specificat nu a fost găsit în baza de date', 
),

'accepttos' => Array 
(
	'tos_notaccepted' => 'Trebuie să citeşti şi să accepţi Termenii de Folosire şi Regulile.', 
),

'charname' => Array 
(
	'required' => 'Completarea rubricii este obligatorie.', 
	'wrongformat' => 'Câmpul trebuie să conţină numai Caractere Europene, fiind excluse caracterele speciale şi numerele. ', 
	'length' => 'Lungimea câmpului trebuie să fie între 5 şi 20 de caractere.', 
	'username_exists' => 'Nume deja folosit.', 
	'not_found' => 'Nume sau parola greşită.', 
	'default' => 'Nu aţi introdus date valide.', 
),

'charsurname' => Array 
(
	'required' => 'Completarea rubricii este obligatorie.', 
	'wrongformat' => 'Câmpul trebuie să conţină numai Caractere Europene, fiind excluse caracterele speciale şi numerele. ', 
	'length' => 'Lungimea rubricii trebuie să fie între 5 şi 20 de caractere.', 
	'username_exists' => 'Nume deja folosit.', 
	'not_found' => 'Nume sau parola greşită.', 
	'default' => 'Nu aţi introdus date valide.', 
),

'charpoints' => Array 
(
	'chars' => 'Mai sunt încă câteva puncte care pot fi alocate Atributele Personajului.', 
	'notequal_50' => 'Suma totală a atributelor trebuie să fie egală cu 50', 
	'notinrange' => 'Unul sau mai multe atribute nu sunt în limita de puncte (1-20)', 
),

'charspokenlanguage1' => Array 
(
	'required' => 'Această rubrică este obligatorie.', 
),

'to' => Array 
(
	'required' => 'Completarea rubricii este obligatorie.', 
	'length' => 'Lungimea rubricii trebuie să fie între 1 şi 20 de caractere.', 
	'char_not_exist' => 'Destinatarul nu există.', 
	'incoherentmode' => 'Nu poţi seta modul masiv şi să adaugi un destinatar', 
),

'choosenkingdom_id' => Array 
(
	'required' => 'Please choose a Kingdom.', 
),

'subject' => Array 
(
	'required' => 'Completarea rubricii este obligatorie.', 
	'length' => 'Lungimea rubricii trebuie sa fie între 1 şi 20 de caractere.', 
	'postcontainsbadwords' => 'Câmpul conţine câteva cuvinte interzise (marcate cu ***).', 
),

'body' => Array 
(
	'required' => 'Completarea rubricii este obligatorie.', 
	'postcontainsbadwords' => 'Câmpul conţine câteva cuvinte interzise (marcate cu ***).', 
),

'law_name' => Array 
(
	'required' => 'Completarea rubricii este obligatorie.', 
	'length' => 'Lungimea rubricii trebuie să fie între 3 şi 50 de caractere.', 
),

'law_desc' => Array 
(
	'required' => 'Completarea rubricii este obligatorie.', 
	'length' => 'Lungimea rubricii trebuie să fie între 3 şi 2048 de caractere.', 
),

'description' => Array 
(
	'alpha_numeric' => 'Rubrica este alfanumerică.', 
	'length' => 'Lungimea rubricii trebuie să fie între 1 şi 2048 de caractere.', 
),

'boarddescription' => Array 
(
	'required' => 'Aceasta rubrică este obligatorie', 
	'length' => 'Rubrica trebuie sa fie între 5 si 255 de caractere lungime', 
),

'old_password' => Array 
(
	'required' => 'Completarea rubricii este obligatorie.', 
	'matches' => 'Această parolă este greşită.', 
),

'promomessage' => Array 
(
	'length' => 'Lungimea rubricii trebuie să fie mai mică de 255 caractere.', 
),

'ann_title' => Array 
(
	'required' => 'Completarea rubricii este obligatorie.', 
	'length' => 'Lungimea rubricii trebuie să fie între 3 şi 50 de caractere.', 
),

'name' => Array 
(
	'required' => 'Aceasta rubrică este obligatorie', 
	'length' => 'Rubrica trebuie sa fie între 5 si 255 de caractere lungime', 
),

'ann_desc' => Array 
(
	'required' => 'Completarea rubricii este obligatorie.', 
	'length' => 'Lungimea rubricii trebuie să fie între 3 şi 4096 caractere.', 
),

'region' => Array 
(
	'required' => 'Completarea rubricii este obligatorie.', 
	'length' => 'Lungimea rubricii trebuie să fie între 3 şi 50 de caractere.', 
	'doesnotexist' => 'Nu am putut găsi acea regiune.', 
),

'quantity' => Array 
(
	'required' => 'Completarea rubricii este obligatorie.', 
),

'slogan' => Array 
(
	'length' => 'Lungimea rubricii trebuie să fie între 1 şi 30 de caractere.', 
),

'group_name' => Array 
(
	'required' => 'Completarea rubricii este obligatorie.', 
	'length' => 'Lungimea rubricii trebuie să fie între 3 şi 60 de caractere.', 
	'groupname_exists' => 'Numele specificat este deja folosit.', 
),

'group_description' => Array 
(
	'required' => 'Completarea rubricii este obligatorie.', 
	'length' => 'Lungimea rubricii trebuie să fie între 3 şi 255 caractere.', 
),

'group_image' => Array 
(
	'default' => 'Admisie invalidă.', 
	'valid' => 'Imaginea nu este valida.', 
	'required' => 'req.', 
	'type' => ' Imaginea trebuie să fie în format png.', 
	'size' => 'Imaginea stemei nu trebuie să fie mai mare de 300Kb.', 
),

'group_charname' => Array 
(
	'required' => 'Câmpul este obligatoriu.', 
	'char_not_exist' => 'Personajul nu există.', 
),

'group_message' => Array 
(
	'required' => 'Câmpul este obligatoriu.', 
	'length' => 'Lungimea câmpului trebuie să fie între 1 şi 1024 caractere.', 
),

'group_subject' => Array 
(
	'required' => '[missing translation]', 
	'length' => '[missing translation]', 
),

'independentregion' => Array 
(
	'required' => 'Completarea rubricii este obligatorie.', 
),

'captain' => Array 
(
	'required' => 'Completarea rubricii este obligatorie.', 
),

'kingcandidate' => Array 
(
	'doesnotexist' => 'Jucătorul nu există.', 
),

'foreignershourlycost' => Array 
(
	'default' => 'Valoarea trebuie să fie > 0', 
),

'message' => Array 
(
	'default' => 'Această informaţie este obligatorie.', 
),

'validity' => Array 
(
	'default' => 'Introdu o valoare >2', 
),

'title' => Array 
(
	'required' => 'Cerinţa este obligatorie.', 
	'length' => 'Lungimea taxtului trebuie să fie între 3 şi 50 caractere.', 
),

'reason' => Array 
(
	'required' => 'Această informaţie este obligatorie.', 
),

'wardrobe_parts' => Array 
(
	'default' => 'Imaginea nu este validă, formatul trebuie să fie PNG, iar mărimea trebuie să fie <= 150K.', 
),

'date' => Array 
(
	'required' => 'Cerinţa este obligatorie.', 
),

'time' => Array 
(
	'required' => 'Cerinţa este obligatorie.', 
),

'location' => Array 
(
	'required' => 'Cerinţa este obligatorie.', 
),

'domainname' => Array 
(
	'required' => '[missing translation]', 
),

);

?>