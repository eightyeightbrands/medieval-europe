<?php defined('SYSPATH') OR die('No direct access allowed.');

$lang = Array
(

'username' => Array 
(
	'required' => 'A mező kitöltése kötelező.', 
	'alpha_numeric' => 'A mező kizárólag alfanumerikus lehet.', 
	'length' => 'A mező hossza 5-től 20 karakter hosszúságig terjedhet.', 
	'username_exists' => 'A név már használatban van.', 
	'not_found' => 'Hibás felhasználó vagy jelszó.', 
	'default' => 'Hibás bemenet.', 
),

'password' => Array 
(
	'required' => 'A mező kitöltése kötelező.', 
	'length' => 'A jelszónak legalább 5 karakter hosszúságúnak kell lennie.', 
	'default' => 'Hibás bemenet.', 
),

'password_confirm' => Array 
(
	'required' => 'A mező kitöltése kötelező.', 
	'matches' => 'A jelszó nem egyezik.', 
	'default' => 'Hibás bemenet.', 
),

'email' => Array 
(
	'email' => 'Kérjük, adjon meg érvényes e-mail címet.', 
	'length' => 'A mező hossza maximum 30 karakter hosszúságig terjedhet.', 
	'email_exists' => 'Ez az e-mail cím már használatban van.', 
	'required' => 'A mező kitöltése kötelező.', 
	'blocked_domain' => 'Sajnáljuk, de nem fogadunk regisztrációkat ilyen domain című e-mail fiókokkal. Kérjük, adjon meg másikat!', 
	'default' => 'Hibás bemenet.', 
),

'captchaanswer' => Array 
(
	'captchaerror' => 'Kérem old meg a Captchát', 
),

'emailconfirm' => Array 
(
	'matches' => 'Az e-mail címek nem egyeznek.', 
	'required' => 'A mező kitöltése kötelező.', 
	'default' => 'Hibás bemenet.', 
),

'referral_id' => Array 
(
	'numeric' => 'Kérjük, adjon meg érvényes Támogatói azonosítót.', 
	'id_notexisting' => 'A megadott azonosítót nem találjuk az adatbázisban.', 
),

'accepttos' => Array 
(
	'tos_notaccepted' => 'El kell olvasnia és elfogadnia az Adatvédelmi Feltételeket a Használati Szabályokról.', 
),

'charname' => Array 
(
	'required' => 'A mező kitöltése kötelező.', 
	'wrongformat' => 'A mező csak európai írásjeleket tartalmazhat, számokat és speciális írásjeleket nem.', 
	'length' => 'A mező hossza 5-től 20 karakter hosszúságig terjedhet.', 
	'username_exists' => 'A név már használatban van.', 
	'not_found' => 'Hibás felhasználó/jelszó', 
	'default' => 'Hibás bemenet.', 
),

'charsurname' => Array 
(
	'required' => 'A mező kitöltése kötelező.', 
	'wrongformat' => 'A mező csak európai írásjeleket tartalmazhat, számokat és speciális írásjeleket nem.', 
	'length' => 'A mező hossza 5-től 20 karakter hosszúságú lehet.', 
	'username_exists' => 'A név már használatban van', 
	'not_found' => 'Hibás felhasználó/jelszó', 
	'default' => 'Hibás bemenet.', 
),

'charpoints' => Array 
(
	'chars' => 'Van még néhány kiosztható pont a Statisztikák részlegben.', 
	'notequal_50' => 'A jellemzők pontjainak száma összesen 50 pont.', 
	'notinrange' => 'Egy vagy több jellemző nem megfelelő értéket kapott (1-20).', 
),

'charspokenlanguage1' => Array 
(
	'required' => 'Kötelező mező', 
),

'to' => Array 
(
	'required' => 'A mező kitöltése kötelező.', 
	'length' => 'A mező ossza 1-tól 20 karakter hosszúságig terjedhet.', 
	'char_not_exist' => 'A címzett nem található.', 
	'incoherentmode' => 'Nem állíthatsz be masszív módot és állíthatsz be címzettet', 
),

'choosenkingdom_id' => Array 
(
	'required' => 'Please choose a Kingdom.', 
),

'subject' => Array 
(
	'required' => 'A mező kitöltése kötelező.', 
	'length' => 'A mező hossza 1-tól 20 karakter hosszúságig terjedhet.', 
	'postcontainsbadwords' => 'A mező néhány tiltott szavakat tartalmaz (***).', 
),

'body' => Array 
(
	'length' => 'A mező hossza 20 és 4096 karakter között lehet.', 
	'required' => 'Ez a mező kötelező.', 
	'postcontainsbadwords' => 'A mező néhány tiltott szavakat tartalmaz (***).', 
),

'law_name' => Array 
(
	'required' => 'A mező kitöltése kötelező.', 
	'length' => 'A mező hossza 3-tól 50 karakter hosszúságig terjedhet.', 
),

'law_desc' => Array 
(
	'required' => 'A mező kitöltése kötelező.', 
	'length' => 'A mező hossza 3-tól 2048 karakter hosszúságig terjedhet.', 
),

'description' => Array 
(
	'alpha_numeric' => 'A mező tartalma kizárólag alfanumerikus lehet.', 
	'length' => 'A mező hossza 1-től 2048 karakter hosszúságig terjedhet.', 
),

'boarddescription' => Array 
(
	'required' => 'Ez a mező kötelező.', 
	'length' => 'A mező hossza 5-től 255 karakter hosszúságú lehet.', 
),

'old_password' => Array 
(
	'required' => 'A mező kitöltése kötelező.', 
	'matches' => 'A jelenlegi jelszó helytelen.', 
),

'promomessage' => Array 
(
	'length' => 'A mező hossza nem haladhatja meg a 255 karaktert.', 
),

'ann_title' => Array 
(
	'required' => 'A mező kitöltése kötelező.', 
	'length' => 'A mező hossza 3-tól 50 karakter hosszúságig terjedhet.', 
),

'name' => Array 
(
	'required' => 'Ez a mező kötelező.', 
	'length' => 'A mező hossza 5-től 50 karakter hosszúságú lehet.', 
),

'ann_desc' => Array 
(
	'required' => 'A mező kitöltése kötelező.', 
	'length' => 'A mező hossza 3-tól 4096 karakter hosszúságig terjedhet.', 
),

'region' => Array 
(
	'required' => 'A mező kitöltése kötelező.', 
	'length' => 'A mező hossza 3-tól 50 karakter hosszúságig terjedhet.', 
	'doesnotexist' => 'A régió nem található.', 
),

'quantity' => Array 
(
	'required' => 'A mező kitöltése kötelező.', 
),

'slogan' => Array 
(
	'length' => 'A mező hossza 1-től 30 karakter hosszúságig terjedhet.', 
),

'group_name' => Array 
(
	'required' => 'A mező kitöltése kötelező.', 
	'length' => 'A mező hossza 3-tól 60 karakter hosszúságig terjedhet.', 
	'groupname_exists' => 'A megadott név már használatban van.', 
),

'group_description' => Array 
(
	'required' => 'A mező kitöltése kötelező.', 
	'length' => 'A mező hossza 3-tól 255 karakter hosszúságig terjedhet.', 
),

'group_image' => Array 
(
	'default' => 'A bevitel érvénytelen.', 
	'valid' => 'Érvénytelen kép.', 
	'required' => 'szük.', 
	'type' => 'A képeknek png formátumban kell lenniük.', 
	'size' => 'A címer képének mérete nem haladhatja meg a 300Kb-ot.', 
),

'group_charname' => Array 
(
	'required' => 'A mező kitöltése kötelező.', 
	'char_not_exist' => 'A karakter nem létezik.', 
),

'group_message' => Array 
(
	'required' => 'A mező kitöltése kötelező.', 
	'length' => 'A mező hossza 1-től 1024 karakter hosszúságig terjedhet.', 
),

'group_subject' => Array 
(
	'required' => 'Ez a mező kötelező.', 
	'length' => 'A mező hossza 1 és 255 között kell hogy legyen.', 
),

'independentregion' => Array 
(
	'required' => 'A mező kitöltése kötelező.', 
),

'captain' => Array 
(
	'required' => 'A mező kitöltése kötelező.', 
),

'kingcandidate' => Array 
(
	'doesnotexist' => 'Ez a játékos nem létezik.', 
),

'foreignershourlycost' => Array 
(
	'default' => '0-nál nagyobbnak kell lennie.', 
),

'message' => Array 
(
	'default' => 'Ezt a mezőt ki kell töltened.', 
),

'validity' => Array 
(
	'default' => '2-nél nagyobb számot adj meg.', 
),

'title' => Array 
(
	'required' => 'Ezt a mezőt ki kell töltened.', 
	'length' => '3 - 50 karakter hosszúnak kell lennie.', 
),

'reason' => Array 
(
	'required' => 'Ezt a mezőt ki kell töltened.', 
),

'wardrobe_parts' => Array 
(
	'default' => 'Kép nem felel meg. PNG típusúnak kell lennie és 150 KB-nál kisebbnek.', 
),

'date' => Array 
(
	'required' => 'Kötelező mező', 
),

'time' => Array 
(
	'required' => 'Kötelező mező', 
),

'location' => Array 
(
	'required' => 'Kötelező mező', 
),

'domainname' => Array 
(
	'required' => 'Kötelező mező', 
),

'discussionurl' => Array 
(
	'required' => 'Kötelező mező', 
),

);

?>