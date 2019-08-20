<?php defined('SYSPATH') OR die('No direct access allowed.');

$lang = Array
(

'username' => Array 
(
	'required' => 'Vstupní pole je povinné.', 
	'alpha_numeric' => 'Pole musí být alfanumerické.', 
	'length' => 'Pole musí mít délku 5 až 20 znaků.', 
	'username_exists' => 'Toto jméno se již používá.', 
	'not_found' => 'Chybné uživatelské jméno nebo heslo.', 
	'default' => 'Neplatný vstup.', 
),

'password' => Array 
(
	'required' => 'Vstupní pole je povinné.', 
	'length' => 'Heslo musí být dlouhé alespoň 5 znaků.', 
	'default' => 'Neplatný vstup.', 
),

'password_confirm' => Array 
(
	'required' => 'Vstupní pole je povinné.', 
	'matches' => 'Hesla se neshodují.', 
	'default' => 'Neplatný vstup.', 
),

'email' => Array 
(
	'email' => 'Zadejte, prosím, platnou e-mailovou adresu.', 
	'length' => 'Maximální délka pole je 30 znaků.', 
	'email_exists' => 'Tato e-mailová adresa již existuje.', 
	'required' => 'Vstupní pole je povinné.', 
	'blocked_domain' => 'Dočasné poštovní schránky nepřijímáme.', 
	'default' => 'Neplatný vstup.', 
),

'captchaanswer' => Array 
(
	'captchaerror' => 'Prosím vyřešit kapču (zda nejste robot).', 
),

'emailconfirm' => Array 
(
	'matches' => 'E-mailové adresy se neshodují.', 
	'required' => 'Vstupní pole je povinné.', 
	'default' => 'Neplatný vstup.', 
),

'referral_id' => Array 
(
	'numeric' => 'Prosím vložte platnou referenční ID (musí být číselná).', 
	'id_notexisting' => 'Zadaná ID nebyla nalezena v naší databázi.', 
),

'accepttos' => Array 
(
	'tos_notaccepted' => 'Musíte si přečíst a přijmout podmínky ochrany soukromí a Uživatelská Řád.', 
),

'charname' => Array 
(
	'required' => 'Vstupní pole je povinné.', 
	'wrongformat' => 'Pole by mělo obsahovat pouze evropské znaky, žádné speciální znaky a žádná čísla.', 
	'length' => 'Délka pole musí být mezi 5 až 20 znaků.', 
	'username_exists' => 'Toto jméno se již používá.', 
	'not_found' => 'Nesprávné přihlašovací jméno/heslo.', 
	'default' => 'Neplatný vstup.', 
),

'charsurname' => Array 
(
	'required' => 'Vstupní pole je povinné.', 
	'wrongformat' => 'Pole by mělo obsahovat pouze evropské znaky, žádné speciální znaky a žádná čísla.', 
	'length' => 'Délka pole musí být mezi 5 až 20 znaků.', 
	'username_exists' => 'Jméno je již využíváno.', 
	'not_found' => 'Nesprávné přihlašovací jméno/heslo.', 
	'default' => 'Neplatný vstup.', 
),

'charpoints' => Array 
(
	'chars' => 'Existuje několik dalších bodů, které mohou být alokovány do sekce Statistiky.', 
	'notequal_50' => 'Celkový součet statistik musí být roven 50 bodů.', 
	'notinrange' => 'Jeden nebo více statistik není v požadovaném rozsahu bodů (1-20).', 
),

'charspokenlanguage1' => Array 
(
	'required' => 'Toto pole je povinné', 
),

'to' => Array 
(
	'required' => 'Vstupní pole je povinné.', 
	'length' => 'Délka pole musí být mezi 1 až 20 znaků.', 
	'char_not_exist' => 'Příjemce nenalezen.', 
	'incoherentmode' => 'Nemůžete nastavit masivní režim a určit příjemce', 
),

'choosenkingdom_id' => Array 
(
	'required' => 'Prosím, zvolte si království.', 
),

'subject' => Array 
(
	'required' => 'Vstupní pole je povinné.', 
	'length' => 'Délka pole musí být mezi 1 až 20 znaků.', 
	'postcontainsbadwords' => 'Pole obsahuje několik nepovolených slov (označené ***).', 
),

'body' => Array 
(
	'length' => 'Délka pole musí být mezi 20 a 4096 znaků.', 
	'required' => 'Pole je povinné.', 
	'postcontainsbadwords' => 'Pole obsahuje několik nepovolených slov (označené ***).', 
),

'law_name' => Array 
(
	'required' => 'Vstupní pole je povinné.', 
	'length' => 'Délka pole musí být mezi 3 až 50 znaků.', 
),

'law_desc' => Array 
(
	'required' => 'Vstupní pole je povinné.', 
	'length' => 'Délka pole musí být mezi 3 až 2048 znaků.', 
),

'description' => Array 
(
	'alpha_numeric' => 'Pole musí být alfanumerické.', 
	'length' => 'Délka pole musí být mezi 1 až 2048 znaků.', 
),

'boarddescription' => Array 
(
	'required' => 'Toto pole je povinné', 
	'length' => 'Pole musí obsahovat 5 až 255 znaků', 
),

'old_password' => Array 
(
	'required' => 'Vstupní pole je povinné.', 
	'matches' => 'Aktuální heslo je nesprávné.', 
),

'promomessage' => Array 
(
	'length' => 'Délka pole musí být kratší než 255 znaků.', 
),

'ann_title' => Array 
(
	'required' => 'Vstupní pole je povinné.', 
	'length' => 'Délka pole musí být mezi 3 až 50 znaků.', 
),

'name' => Array 
(
	'required' => 'Toto pole je povinné', 
	'length' => 'Délka pole musí být dlouhé 5 až 50 znaků.', 
),

'ann_desc' => Array 
(
	'required' => 'Vstupní pole je povinné.', 
	'length' => 'Délka pole musí být mezi 3 až 4096 znaků.', 
),

'region' => Array 
(
	'required' => 'Toto pole je povinné.', 
	'length' => 'Délka pole by mělo být mezi 3 až 50 znaků.', 
	'doesnotexist' => 'Nemohl/a jsem najít hledaný region.', 
),

'quantity' => Array 
(
	'required' => 'Vstupní pole je povinné.', 
),

'slogan' => Array 
(
	'length' => 'Délka pole musí být mezi 1 až 30 znaků.', 
),

'group_name' => Array 
(
	'required' => 'Toto pole je povinné.', 
	'length' => 'Délka pole by mělo být dlouhé mezi 3 až 60 znaků.', 
	'groupname_exists' => 'Jméno, které jste požadoval/a, je již používáno.', 
),

'group_description' => Array 
(
	'required' => 'Toto pole je povinné.', 
	'length' => 'Délka pole by mělo být dlouhé mezi 3 až 255 znaků.', 
),

'group_image' => Array 
(
	'default' => 'Neplatný vstup.', 
	'valid' => 'Neplatný obrázek.', 
	'required' => 'pož.', 
	'type' => 'Obrázek musí mít formát PNG.', 
	'size' => 'Velikost obrazu erbu nesmí překročit 300KB.', 
),

'group_charname' => Array 
(
	'required' => 'Toto pole je povinné.', 
	'char_not_exist' => 'Postava neexistuje.', 
),

'group_message' => Array 
(
	'required' => 'Toto pole je povinné.', 
	'length' => 'Délka pole by měla být dlouhá mezi 1 až 1024 znaky.', 
),

'group_subject' => Array 
(
	'required' => 'Toto pole je povinné', 
	'length' => 'Délka pole musí být mezi 1 až 255 znaků.', 
),

'independentregion' => Array 
(
	'required' => 'Toto pole je povinné.', 
),

'captain' => Array 
(
	'required' => 'Toto pole je povinné.', 
),

'kingcandidate' => Array 
(
	'doesnotexist' => 'Tento hráč neexituje.', 
),

'foreignershourlycost' => Array 
(
	'default' => 'Hodnota musí být > 0', 
),

'message' => Array 
(
	'default' => 'Toto pole je povinné', 
),

'validity' => Array 
(
	'default' => 'Zadejte hodnotu > 2', 
),

'title' => Array 
(
	'required' => 'Toto pole je povinné.', 
	'length' => 'Délka pole musí být mezi 3 až 50 znaků.', 
),

'reason' => Array 
(
	'required' => 'Toto pole je povinné.', 
),

'wardrobe_parts' => Array 
(
	'default' => 'Obrázek není platný, musí být formátu PNG a velikost by měla být <= 150K.', 
),

'date' => Array 
(
	'required' => 'Toto pole je povinné', 
),

'time' => Array 
(
	'required' => 'Toto pole je povinné', 
),

'location' => Array 
(
	'required' => 'Toto pole je povinné.', 
),

'domainname' => Array 
(
	'required' => 'Toto pole je povinné', 
),

'discussionurl' => Array 
(
	'required' => 'Toto pole je povinné', 
),

);

?>