<?php defined('SYSPATH') OR die('No direct access allowed.');

$lang = Array
(

'username' => Array 
(
	'required' => 'Il campo &egrave; obbligatorio.', 
	'alpha_numeric' => 'Il campo deve essere composto solamente da caratteri e numeri.', 
	'length' => 'La lunghezza del campo deve essere compresa tra 5 e 20 caratteri.', 
	'username_exists' => 'Il nome specificato &egrave; gi&agrave; in uso.', 
	'not_found' => 'Username o password non corretti.', 
	'default' => 'Input non valido.', 
),

'password' => Array 
(
	'required' => 'Il campo &egrave; obbligatorio.', 
	'length' => 'La password deve essere almeno composta da 5 caratteri.', 
	'default' => 'Input non valido.', 
),

'password_confirm' => Array 
(
	'required' => 'Il campo &egrave; obbligatorio.', 
	'matches' => 'Le password fornite non combaciano.', 
	'default' => 'Input non valido.', 
),

'email' => Array 
(
	'email' => 'Inserisci un indirizzo email valido.', 
	'length' => 'La lunghezza del campo deve essere minore di 30 caratteri.', 
	'email_exists' => 'L&#8217;indirizzo email specificato risulta gi&agrave; esistente.', 
	'required' => 'Il campo &egrave; obbligatorio.', 
	'blocked_domain' => 'Non accettiamo caselle di posta temporanee.', 
	'default' => 'Invalid non valido.', 
),

'captchaanswer' => Array 
(
	'captchaerror' => 'Per favore risolvi il captcha.', 
),

'emailconfirm' => Array 
(
	'matches' => 'Gli indirizzi email forniti non combaciano.', 
	'required' => 'Il campo &egrave; obbligatorio.', 
	'default' => 'Invalid non valido.', 
),

'referral_id' => Array 
(
	'numeric' => 'Inserisci un ID referral valido (deve essere numerico).', 
	'id_notexisting' => 'L&#8217; l\' utente con l\' ID specificato non &egrave; stato trovato.', 
),

'accepttos' => Array 
(
	'tos_notaccepted' => 'Devi leggere e accettare le condizioni d&#8217;uso.', 
),

'charname' => Array 
(
	'required' => 'Il campo &egrave; obbligatorio.', 
	'wrongformat' => 'Il campo deve essere composto solamente da caratteri dell\' alfabeto Europeo, non deve contenere numeri n&egrave; caratteri speciali.', 
	'length' => 'La lunghezza del campo deve essere compresa tra 3 e 15 caratteri.', 
	'username_exists' => 'Il nome specificato &egrave; gi&agrave; in uso.', 
	'not_found' => 'Username o password non corretti.', 
	'default' => 'Input non valido.', 
),

'charsurname' => Array 
(
	'required' => 'Il campo &egrave; obbligatorio.', 
	'wrongformat' => 'Il campo deve essere composto solamente da caratteri dell\' alfabeto Europeo, non deve contenere numeri n&egrave; caratteri speciali.', 
	'length' => 'La lunghezza del campo deve essere compresa tra 3 e 15 caratteri.', 
	'username_exists' => 'Il nome specificato &egrave; gi&	; in uso.', 
	'not_found' => 'Username o password non corretti.', 
	'default' => 'Input non valido.', 
),

'charpoints' => Array 
(
	'chars' => 'Ci sono ancora dei punti da distribuire nelle statistiche.', 
	'notequal_50' => 'La somma di tutte le statistiche deve essere uguale a 40', 
	'notinrange' => 'Una o pi&ugrave; stat non &egrave; nel range di punti previsto 1-15', 
),

'charspokenlanguage1' => Array 
(
	'required' => 'Il campo &egrave; obbligatorio.',	
),



'to' => Array 
(
	'required' => 'Il campo &egrave; obbligatorio.', 
	'length' => 'La lunghezza del campo deve essere compresa tra 1 e 50 caratteri.', 
	'char_not_exist' => 'Il destinatario inserito non eriste!', 
	'incoherentmode' => 'Non puoi specificare il modo massivo e specificare un destinatario', 
),

'choosenkingdom_id' => Array
(
'required' => 'La scelta del Regno &egrave; obbligatoria.', 
),

'subject' => Array 
(
	'required' => 'Il campo &egrave; obbligatorio.', 
	'length' => 'La lunghezza del campo deve essere compresa tra 1 e 50 caratteri.', 
	'postcontainsbadwords' => 'Il campo contiene delle parole non ammesse (marcate con ***).'
),

'body' => Array 
(
	'length' => 'La lunghezza del campo deve essere compresa tra 20 e 4096 caratteri.', 
	'required' => 'Il campo &egrave; obbligatorio.', 
	'postcontainsbadwords' => 'Il campo contiene delle parole non ammesse (marcate con ***).'
),

'law_name' => Array 
(
	'required' => 'Il campo &egrave; obbligatorio.', 
	'length' => 'La lunghezza del campo deve essere compresa tra 3 e 50 caratteri.', 
),

'law_desc' => Array 
(
	'required' => 'Il campo &egrave; obbligatorio.', 
	'length' => 'La lunghezza del campo deve essere compresa tra 3 e 2048 caratteri.', 
),

'description' => Array 
(
	'alpha_numeric' => 'Il campo deve essere composto solamente da caratteri e numeri.', 
	'length' => 'La lunghezza del campo deve essere compresa tra 1 e 2048 caratteri.', 
),

'boarddescription' => Array 
(
	'required' => 'Il campo &egrave; obbligatorio.', 
	'length' => 'La lunghezza del campo deve essere compresa tra 5 e 255 caratteri.', 
),

'old_password' => Array 
(
	'required' => 'Il campo &egrave; obbligatorio.', 
	'matches' => 'La password attuale non &egrave;corretta.', 
),

'promomessage' => Array 
(
	'length' => 'La lunghezza del messaggio deve essere inferiore a 255 caratteri.', 
),

'ann_title' => Array 
(
	'required' => 'Il campo &egrave; obbligatorio.', 
	'length' => 'La lunghezza del campo deve essere compresa tra 3 e 50 caratteri.', 
),

'name' => Array 
(
	'required' => 'Il campo &egrave; obbligatorio.', 
	'length' => 'La lunghezza del campo deve essere compresa tra 5 e 50 caratteri.', 
),



'ann_desc' => Array 
(
	'required' => 'Il campo &egrave; obbligatorio.', 
	'length' => 'La lunghezza del campo deve essere compresa tra 3 e 4096 caratteri.', 
),

'region' => Array 
(
	'required' => 'Il campo &egrave; obbligatorio.', 
	'length' => 'La lunghezza del campo deve essere compresa tra 3 e 50 caratteri.', 
	'doesnotexist' => 'Regione non trovata.', 
),

'quantity' => Array 
(
	'required' => 'Il campo &egrave; obbligatorio.', 
),

'slogan' => Array 
(
	'length' => 'La lunghezza del campo deve essere compresa tra 1 e 30 caratteri.', 
),

'group_name' => Array 
(
	'required' => 'Il campo &egrave; obbligatorio.', 
	'length' => 'La lunghezza del campo deve essere compresa tra 3 e 60 caratteri.', 
	'groupname_exists' => 'Il nome specificato &egrave; gi&agrave; in uso.', 
),

'group_description' => Array 
(
	'required' => 'Il campo &egrave; obbligatorio.', 
	'length' => 'La lunghezza del campo deve essere compresa tra 3 e 255 caratteri.', 
),

'group_image' => Array 
(
	'default' => 'Input non valido.', 
	'valid' => 'L&#8217; immagine non &egrave; valida.', 
	'required' => 'req', 
	'type' => 'Il formato immagine ammesso &egrave; solo png.', 
	'size' => 'La grandezza massima per lo stemma &egrave; di 300Kb', 
),

'group_charname' => Array 
(
	'required' => 'Il campo &egrave; obbligatorio.', 
	'char_not_exist' => 'Il personaggio inserito non esiste!', 
),

'group_message' => Array 
(
	'required' => 'Il campo &egrave; obbligatorio.', 
	'length' => 'La lunghezza del campo deve essere compresa tra 1 e 1024 caratteri.', 
),
'group_subject' => Array 
(
	'required' => 'Il campo &egrave; obbligatorio.', 
	'length' => 'La lunghezza del campo deve essere compresa tra 1 e 255 caratteri.', 
),
'independentregion' => Array 
(
	'required' => 'Il campo &egrave; obbligatorio.', 
),

'captain' => Array 
(
	'required' => 'Il campo &egrave; obbligatorio.', 
),

'kingcandidate' => Array 
(
	'doesnotexist' => 'Questo giocatore non esiste.', 
),

'foreignershourlycost' => Array 
(
	'default' => 'Inserisci un valore > 0', 
),

'message' => Array 
(
	'default' => 'Il campo &egrave; obbligatorio', 
),

'validity' => Array 
(
	'default' => 'Inserisci un valore > 2', 
),

'title' => Array 
(
	'required' => 'Il campo &egrave; obbligatorio.', 
	'length' => 'La lunghezza del campo deve essere compresa tra 3 e 50 caratteri.', 
),

'reason' => Array 
(
	'required' => 'Il campo &egrave; obbligatorio.', 
),

'wardrobe_parts' => Array 
(
	'default' => 'Immagine non valida, deve essere formato png, dimensione massima 150K.', 
),

'date' => Array 
(
	'required' => 'Il campo &egrave; obbligatorio.', 
),

'time' => Array 
(
	'required' => 'Il campo &egrave; obbligatorio.', 
),

'location' => Array 
(
	'required' => 'Il campo &egrave; obbligatorio.', 
),
'domainname' => Array 
(
	'required' => 'Il campo &egrave; obbligatorio.', 
),
'discussionurl' => Array 
(
	'required' => 'Il campo &egrave; obbligatorio.', 
),
'reason' => Array 
(
	'required' => 'Il campo &egrave; obbligatorio.', 
),
);

?>
