<?php defined('SYSPATH') OR die('No direct access allowed.');

$lang = Array
(

'username' => Array 
(
	'required' => 'Bu alanı doldurmak zorundasınız.', 
	'alpha_numeric' => 'Alan alfanümerik olarak doldurulabilir. (Sadece rakam ve harfler)', 
	'length' => '5 ila 20 karakter uzunluğunda olmalıdır.', 
	'username_exists' => 'İsim zaten kullanımda.', 
	'not_found' => 'Yanlış kullanıcı ismi ya da şifre.', 
	'default' => 'Girdi geçerli değil.', 
),

'password' => Array 
(
	'required' => 'Bu alanı doldurmak zorundasınız.', 
	'length' => 'Şifre en az 5 karakter uzunluğunda olmalıdır.', 
	'default' => 'Girdi geçerli değil.', 
),

'password_confirm' => Array 
(
	'required' => 'Bu alanı doldurmak zorundasınız.', 
	'matches' => 'Şifreler aynı değil!', 
	'default' => 'Girdi geçersiz.', 
),

'email' => Array 
(
	'email' => 'Geçerli bir e-posta adresi girin.', 
	'length' => 'En fazla 30 karakter kullanabilirsiniz.', 
	'email_exists' => 'Bu e-posta adresi zaten kullanımda.', 
	'required' => 'Bu alanı doldurmak zorundasınız.', 
	'blocked_domain' => 'Geçici eposta adreslerini kabul etmiyoruz.', 
	'default' => 'Girdi geçersiz.', 
),

'captchaanswer' => Array 
(
	'captchaerror' => 'Lütfen Captcha\'yı çözünüz.', 
),

'emailconfirm' => Array 
(
	'matches' => 'E-posta adresleri aynı değil!', 
	'required' => 'Bu alanı doldurmak zorundasınız.', 
	'default' => 'Girdi geçersiz.', 
),

'referral_id' => Array 
(
	'numeric' => 'Geçerli bir Davetçi ID giriniz.', 
	'id_notexisting' => 'Belirtilen ID veritabanımızda bulunamadı.', 
),

'accepttos' => Array 
(
	'tos_notaccepted' => 'Gizlilik ve Kullanım Koşulları Kurallarını okuyup kabul etmelisiniz.', 
),

'charname' => Array 
(
	'required' => 'Bu alanı doldurmak zorundasınız.', 
	'wrongformat' => 'Bu alan sadece Avrupa kökenli dil karakterleri içermeli. Özel karakterler ve sayılar kabul edilmez.', 
	'length' => '5 ila 20 karakter uzunluğunda olmalıdır.', 
	'username_exists' => 'İsim zaten kullanımda.', 
	'not_found' => 'Yanlış kullanıcı ismi ya da şifre.', 
	'default' => 'Girdi geçersiz.', 
),

'charsurname' => Array 
(
	'required' => 'Bu alanı doldurmak zorundasınız.', 
	'wrongformat' => 'Bu alan sadece Avrupa kökenli dil karakterleri içermeli. Özel karakterler ve sayılar kabul edilmez.', 
	'length' => '5 ila 20 karakter uzunluğunda olmalıdır.', 
	'username_exists' => 'İsim zaten kullanımda.', 
	'not_found' => 'Yanlış kullanıcı ismi ya da şifre.', 
	'default' => 'Girdi geçersiz.', 
),

'charpoints' => Array 
(
	'chars' => 'Özellikler bölümünde dağıtabileceğiniz puanlar var.', 
	'notequal_50' => 'Özellik puanlarınızın toplamı 50 olmalıır.', 
	'notinrange' => 'Bir ya da daha fazla özellik için limitler dışında değer girdiniz (1 - 15)', 
),

'charspokenlanguage1' => Array 
(
	'required' => 'Bu alan zorunludur.', 
),

'to' => Array 
(
	'required' => 'Bu alanı doldurmak zorundasınız.', 
	'length' => '1 ila 50 karakter uzunluğunda olmalıdır.', 
	'char_not_exist' => 'Alıcı bulunmadı.', 
	'incoherentmode' => 'Büyük modu seçip bir alıcı belirtemezsiniz.', 
),

'choosenkingdom_id' => Array 
(
	'required' => 'Please choose a Kingdom.', 
),

'subject' => Array 
(
	'required' => 'Bu alanı doldurmak zorundasınız.', 
	'length' => '1 ila 50 karakter uzunluğunda olmalıdır.', 
	'postcontainsbadwords' => 'Bu alan yasaklı ifadeler içermektedir (*** ile işaretlenmiştir)', 
),

'body' => Array 
(
	'required' => 'Bu alanı doldurmak zorundasınız.', 
	'postcontainsbadwords' => 'Bu alan yasaklı ifadeler içermektedir (*** ile işaretlenmiştir)', 
),

'law_name' => Array 
(
	'required' => 'Bu alanı doldurmak zorundasınız.', 
	'length' => '3 ila 50 karakter uzunluğunda olmalıdır.', 
),

'law_desc' => Array 
(
	'required' => 'Bu alanı doldurmak zorundasınız.', 
	'length' => '3 ila 2048 karakter uzunluğunda olmalıdır.', 
),

'description' => Array 
(
	'alpha_numeric' => 'Alan alfanümerik olarak doldurulabilir. (Sadece rakam ve harfler)', 
	'length' => '1 ila 2048 karakter uzunluğunda olmalıdır.', 
),

'boarddescription' => Array 
(
	'required' => 'Bu alan zorunudur', 
	'length' => '5 ila 255 karakter uzunluğunda olmalıdır.', 
),

'old_password' => Array 
(
	'required' => 'Bu alanı doldurmak zorundasınız.', 
	'matches' => 'Mevcut şifreyi yanlış girdiniz.', 
),

'promomessage' => Array 
(
	'length' => 'Bu alana en fazla 255 karakter girebilirsiniz.', 
),

'ann_title' => Array 
(
	'required' => 'Bu alanı doldurmak zorundasınız.', 
	'length' => '3 ila 50 karakter uzunluğunda olmalıdır.', 
),

'name' => Array 
(
	'required' => 'Bu alan zorunudur', 
	'length' => '5 ila 50 karakter uzunluğunda olmalıdır.', 
),

'ann_desc' => Array 
(
	'required' => 'Bu alanı doldurmak zorundasınız.', 
	'length' => '3 ila 4096 karakter uzunluğunda olmalıdır.', 
),

'region' => Array 
(
	'required' => 'Bu alanı doldurmak zorundasınız.', 
	'length' => '3 ila 50 karakter uzunluğunda olmalıdır.', 
	'doesnotexist' => 'Bölge bulunamadı.', 
),

'quantity' => Array 
(
	'required' => 'Bu alanı doldurmak zorundasınız.', 
),

'slogan' => Array 
(
	'length' => '3 ila 30 karakter uzunluğunda olmalıdır.', 
),

'group_name' => Array 
(
	'required' => 'Bu alanı doldurmak zorundasınız.', 
	'length' => '3 ila 60 karakter uzunluğunda olmalıdır.', 
	'groupname_exists' => 'Bu isim zaten kullanımda.', 
),

'group_description' => Array 
(
	'required' => 'Bu alanı doldurmak zorundasınız.', 
	'length' => '3 ila 30 karakter uzunluğunda olmalıdır.', 
),

'group_image' => Array 
(
	'default' => 'Girdi geçerli değil.', 
	'valid' => 'Geçersiz resim.', 
	'required' => 'req', 
	'type' => 'Resimler .png formatında olmalı.', 
	'size' => 'Arma resminin boyutu 300kb\'ı aşmamalı', 
),

'group_charname' => Array 
(
	'required' => 'Bu alanı doldurmak zorundasınız.', 
	'char_not_exist' => 'Böyle bir karater yok.', 
),

'group_message' => Array 
(
	'required' => 'Bu alanı doldurmak zorundasınız.', 
	'length' => '3 ila 30 karakter uzunluğunda olmalıdır.', 
),

'independentregion' => Array 
(
	'required' => 'Bu alanı doldurmak zorundasınız.', 
),

'captain' => Array 
(
	'required' => 'Bu alanı doldurmak zorundasınız.', 
),

'kingcandidate' => Array 
(
	'doesnotexist' => 'Böyle bir oyuncu yok.', 
),

'foreignershourlycost' => Array 
(
	'default' => 'Değer sıfırdan büyük olmalıdır.', 
),

'message' => Array 
(
	'default' => 'Bu alan zorunlu', 
),

'validity' => Array 
(
	'default' => '2\'den büyük bir değer giriniz', 
),

'title' => Array 
(
	'required' => 'Bu alanı doldurmak zorundasınız.', 
	'length' => '3 ila 30 karakter uzunluğunda olmalıdır.', 
),

'reason' => Array 
(
	'required' => 'Bu alan zorunlu', 
),

'wardrobe_parts' => Array 
(
	'default' => 'Geçersiz resim.', 
),

'date' => Array 
(
	'required' => 'Bu alan zorunlu', 
),

'time' => Array 
(
	'required' => 'Bu alan zorunlu', 
),

'location' => Array 
(
	'required' => 'Bu alan zorunlu', 
),

);

?>