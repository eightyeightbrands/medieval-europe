<?php defined('SYSPATH') OR die('No direct access allowed.');

$lang = Array
(

'username' => Array 
(
	'required' => 'Plotësimi i kësaj fushe është i detyrueshëm.', 
	'alpha_numeric' => 'Kjo fushë duhet plotësuar vetëm me shkronja dhe numra.', 
	'length' => 'Madhësia e tekstit duhet të jetë nga 5 deri në 20 karaktere.', 
	'username_exists' => 'Ky emër është tashmë në përdorim.', 
	'not_found' => 'Emri i përdoruesit ose fjalëkalimi është i gabuar.', 
	'default' => 'Të dhëna të pavlefshme.', 
),

'password' => Array 
(
	'required' => 'Plotësimi i kësaj fushe është i detyrueshëm.', 
	'length' => 'Fjalëkalimi duhet të ketë të paktën 5 karaktere.', 
	'default' => 'Të dhëna të pavlefshme.', 
),

'password_confirm' => Array 
(
	'required' => 'Plotësimi i kësaj fushe është i detyrueshëm.', 
	'matches' => 'Fjalëkalimi juaj nuk përputhet.', 
	'default' => 'Të dhëna të pavlefshme.', 
),

'email' => Array 
(
	'email' => 'Përcaktoni një adresë të vlefshme poste elektronike.', 
	'length' => 'Madhësia e tekstit duhet të jetë deri në 30 karaktere.', 
	'email_exists' => 'Adresa e postës elektronike e dhënë prej jush është tashmë në përdorim.', 
	'required' => 'Plotësimi i kësaj fushe është i detyrueshëm.', 
	'blocked_domain' => 'Na vjen keq, por nuk pranojmë rregjistrime me postë elektronike të këtij domeni. Ju lutemi të vendosni një adresë tjetër poste elektronike.', 
	'default' => 'Të dhëna të pavlefshme.', 
),

'emailconfirm' => Array 
(
	'matches' => 'Adresat e dhëna të postës elektronike nuk përputhen.', 
	'required' => 'Plotësimi i kësaj fushe është i detyrueshëm.', 
	'default' => 'Të dhëna të pavlefshme.', 
),

'referral_id' => Array 
(
	'numeric' => 'Ju lutemi, vendosni një ID referali të vlefshëm.', 
	'id_notexisting' => 'ID-ja e përcaktuar nuk gjendet në bazën e të dhënave.', 
),

'accepttos' => Array 
(
	'tos_notaccepted' => 'Duhet të lexoni dhe të pranoni Kushtet e Përdorimit.', 
),

'charname' => Array 
(
	'required' => 'Plotësimi i kësaj fushe është i detyrueshëm.', 
	'alpha_numeric' => 'Kjo fushë duhet plotësuar vetëm me shkronja dhe numra.', 
	'length' => 'Madhësia e tekstit duhet të jetë nga 3 deri në 15 karaktere.', 
	'username_exists' => 'Ky emër është tashmë në përdorim.', 
	'not_found' => 'Emri i përdoresit ose fjalëkalimi është i pavlefshëm.', 
	'default' => 'Të dhëna të pavlefshme.', 
),

'charsurname' => Array 
(
	'required' => 'Plotësimi i kësaj fushe është i detyrueshëm.', 
	'alpha_numeric' => 'Kjo fushë duhet plotësuar vetëm me shkronja dhe numra.', 
	'length' => 'Madhësia e tekstit duhet të jetë nga 3 deri në 15 karaktere.', 
	'username_exists' => 'Ky emër është tashmë në përdorim.', 
	'not_found' => 'Emri i përdoresit ose fjalëkalimi është i pavlefshëm.', 
	'default' => 'Të dhëna të pavlefshme.', 
),

'charpoints' => Array 
(
	'chars' => 'Ka akoma disa pikë të pashpërndara midis karakteristikave.', 
	'notequal_50' => 'Shuma e pikëve të shpërndara duhet të jetë e barabartë me 40.', 
	'notinrange' => 'Një ose më shumë prej karakteristikave dalin jashtë standardit 1-15 pikë.', 
),

'to' => Array 
(
	'required' => 'Plotësimi i kësaj fushe është i detyrueshëm.', 
	'length' => 'Madhësia e tekstit duhet të jetë nga 1 deri në 50 karaktere.', 
	'char_not_exist' => 'Marrësi nuk u gjet.', 
	'incoherentmode' => 'Nuk mund të zgjidhni dërgimin në masë dhe të përcaktonivetëm një marrës.', 
),

'subject' => Array 
(
	'required' => 'Plotësimi i kësaj fushe është i detyrueshëm.', 
	'length' => 'Madhësia e tekstit duhet të jetë nga 1 deri në 50 karaktere.', 
),

'body' => Array 
(
	'required' => 'Plotësimi i kësaj fushe është i detyrueshëm.', 
),

'law_name' => Array 
(
	'required' => 'Plotësimi i kësaj fushe është i detyrueshëm.', 
	'length' => 'Madhësia e tekstit duhet të jetë nga 3 deri në 50 karaktere.', 
),

'law_desc' => Array 
(
	'required' => 'Plotësimi i kësaj fushe është i detyrueshëm.', 
	'length' => 'Madhësia e tekstit duhet të jetë nga 3 deri në 2048 karaktere.', 
),

'description' => Array 
(
	'alpha_numeric' => 'Kjo fushë duhet plotësuar vetëm me shkronja dhe numra.', 
	'length' => 'Madhësia e tekstit duhet të jetë nga 3 deri në 2048 karaktere.', 
),

'old_password' => Array 
(
	'required' => 'Plotësimi i kësaj fushe është i detyrueshëm.', 
	'matches' => 'Fjalëkalimi aktual është i gabuar.', 
),

'promomessage' => Array 
(
	'length' => 'Madhësia e tekstit të mesazhit duhet të jetë më e vogël se 255 karaktere.', 
),

'ann_title' => Array 
(
	'required' => 'Plotësimi i kësaj fushe është i detyrueshëm.', 
	'length' => 'Madhësia e tekstit duhet të jetë nga 3 deri në 50 karaktere.', 
),

'ann_desc' => Array 
(
	'required' => 'Plotësimi i kësaj fushe është i detyrueshëm.', 
	'length' => 'Madhësia e tekstit duhet të jetë nga 3 deri në 4096 karaktere.', 
),

'region' => Array 
(
	'required' => 'Plotësimi i kësaj fushe është i detyrueshëm.', 
	'length' => 'Madhësia e tekstit duhet të jetë nga 3 deri në 50 karaktere.', 
	'doesnotexist' => 'Provinca nuk gjendet.', 
),

'quantity' => Array 
(
	'required' => 'Plotësimi i kësaj fushe është i detyrueshëm.', 
),

'slogan' => Array 
(
	'length' => 'Madhësia e tekstit duhet të jetë nga 1 deri në 30 karaktere.', 
),

'group_name' => Array 
(
	'required' => 'Plotësimi i kësaj fushe është i detyrueshëm.', 
	'length' => 'Madhësia e tekstit duhet të jetë nga 3 deri në 60 karaktere.', 
	'groupname_exists' => 'Ky emër është tashmë në përdorim.', 
),

'group_description' => Array 
(
	'required' => 'Plotësimi i kësaj fushe është i detyrueshëm.', 
	'length' => 'Madhësia e tekstit duhet të jetë nga 3 deri në 255 karaktere.', 
),

'group_image' => Array 
(
	'default' => 'Të dhëna të pavlefshme.', 
	'valid' => 'pippo.', 
	'required' => 'req.', 
	'type' => 'Pikturat duhet të jenë në formatin png.', 
	'size' => 'Madhësia e Emblemës nuk duhet t’i kalojë 300 Kb.', 
),

'group_charname' => Array 
(
	'required' => 'Plotësimi i kësaj fushe është i detyrueshëm.', 
	'char_not_exist' => 'Personazhi nuk ekziston.', 
),

'group_message' => Array 
(
	'required' => 'Plotësimi i kësaj fushe është i detyrueshëm.', 
	'length' => 'Madhësia e tekstit duhet të jetë nga 1 deri në 1024 karaktere.', 
),

'independentregion' => Array 
(
	'required' => 'Plotësimi i kësaj fushe është i detyrueshëm.', 
),

'captain' => Array 
(
	'required' => 'Plotësimi i kësaj fushe është i detyrueshëm.', 
),

'kingcandidate' => Array 
(
	'doesnotexist' => 'Ky lojtar nuk ekziston.', 
),

'foreignershourlycost' => Array 
(
	'default' => 'Vendosni një vlerë më të madhe se 0.', 
),

'message' => Array 
(
	'default' => 'X', 
),

'validity' => Array 
(
	'default' => 'Vendos një vlerë > 2', 
),

'title' => Array 
(
	'required' => 'Plotësimi i kësaj fushe është i detyrueshëm.', 
	'length' => 'Madhësia e tekstit duhet të jetë nga 3 deri në 50 karaktere.', 
),

'reason' => Array 
(
	'required' => 'X', 
),

'wardrobe_parts' => Array 
(
	'default' => 'X', 
),

);

?>