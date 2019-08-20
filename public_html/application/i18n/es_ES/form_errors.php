<?php defined('SYSPATH') OR die('No direct access allowed.');

$lang = Array
(

'username' => Array 
(
	'required' => 'El campo de entrada es mandatorio.', 
	'alpha_numeric' => 'El campo debe ser alfanumérico.', 
	'length' => 'El campo debe tener entre 5 a 20 caracteres.', 
	'username_exists' => 'Este nombre ya está en uso.', 
	'not_found' => 'Nombre de usuario o contraseña incorrecta.', 
	'default' => 'La entrada no es válida.', 
),

'password' => Array 
(
	'required' => 'El campo de entrada es mandatorio.', 
	'length' => 'La contraseña debe ser de al menos 5 caracteres.', 
	'default' => 'La entrada no es válida.', 
),

'password_confirm' => Array 
(
	'required' => 'El campo de entrada es mandatorio.', 
	'matches' => 'Las contraseñas no coinciden.', 
	'default' => 'La entrada no es válida.', 
),

'email' => Array 
(
	'email' => 'Por favor especifique una dirección de correo válida.', 
	'length' => 'El tamaño máximo del campo es de 30 caracteres.', 
	'email_exists' => 'Esta dirección de correo ya existe.', 
	'required' => 'El campo de entrada es mandatorio.', 
	'blocked_domain' => 'Lo sentimos, pero no aceptamos registros con el correro localizado en ese dominio. Por favor especifica otro proveedor de correo.', 
	'default' => 'La entrada no es válida.', 
),

'captchaanswer' => Array 
(
	'captchaerror' => 'Por favor resulva el captcha', 
),

'emailconfirm' => Array 
(
	'matches' => 'Las direcciones de correro no coinciden.', 
	'required' => 'El campo de entrada es mandatorio.', 
	'default' => 'La entrada no es válida.', 
),

'referral_id' => Array 
(
	'numeric' => 'Por favor inserte un ID de referencia válido.', 
	'id_notexisting' => 'El ID especificado no fue encontrado en nuestra base de datos.', 
),

'accepttos' => Array 
(
	'tos_notaccepted' => 'Debes leer y aceptar nuestros Términos de Uso y Privacidad.', 
),

'charname' => Array 
(
	'required' => 'El campo de entrada es mandatorio.', 
	'wrongformat' => 'El campo debe contener solo caracteres del alfabeto europeo, sin numeros ni caracteres especiales.', 
	'length' => 'El campo debe tener entre 5 a 20 caracteres.', 
	'username_exists' => 'Este nombre ya está en uso.', 
	'not_found' => 'Nombre de usuario o contraseña incorrecta.', 
	'default' => 'La entrada no es válida.', 
),

'charsurname' => Array 
(
	'required' => 'El campo de entrada es mandatorio.', 
	'wrongformat' => 'El campo debe contener solo caracteres del alfabeto europeo, sin numeros ni caracteres especiales.', 
	'length' => 'El campo debe tener entre 5 a 20 caracteres.', 
	'username_exists' => 'Este nombre ya está en uso.', 
	'not_found' => 'Nombre de usuario o contraseña incorrecta.', 
	'default' => 'La entrada no es válida.', 
),

'charpoints' => Array 
(
	'chars' => 'Aún quedan puntos por colocar en la sección de Estadísticas.', 
	'notequal_50' => 'La suma total de las estadísticas debe ser de 40 puntos.', 
	'notinrange' => 'Uno o mas de los campos de estadísticas no contienen una cifra dentro del rango requerido (1-20).', 
),

'charspokenlanguage1' => Array 
(
	'required' => 'El campo es obligatorio.', 
),

'to' => Array 
(
	'required' => 'El campo de entrada es mandatorio.', 
	'length' => 'El campo debe tener entre 1 a 20 caracteres.', 
	'char_not_exist' => 'El remitente no fue encontrado.', 
	'incoherentmode' => 'No puedes elegir el modo masivo y especificar un remitente.', 
),

'choosenkingdom_id' => Array 
(
	'required' => 'Please choose a Kingdom.', 
),

'subject' => Array 
(
	'required' => 'El campo de entrada es mandatorio.', 
	'length' => 'El campo debe tener entre 1 a 20 caracteres.', 
	'postcontainsbadwords' => 'El campo contiene palabras no permitidas (marcadas con ***)', 
),

'body' => Array 
(
	'length' => 'La longitud del campo debe ser entre 20 y 4096 caracteres', 
	'required' => 'El campo es obligatorio.', 
	'postcontainsbadwords' => 'El campo contiene palabras no permitidas (marcadas con ***)', 
),

'law_name' => Array 
(
	'required' => 'El campo de entrada es mandatorio.', 
	'length' => 'El campo debe tener entre 3 a 50 caracteres.', 
),

'law_desc' => Array 
(
	'required' => 'El campo de entrada es mandatorio.', 
	'length' => 'El campo debe tener entre 3 a 2048 caracteres.', 
),

'description' => Array 
(
	'alpha_numeric' => 'El campo debe ser alfanumérico.', 
	'length' => 'El campo debe tener entre 3 a 2048 caracteres.', 
),

'boarddescription' => Array 
(
	'required' => 'El campo es obligatorio.', 
	'length' => 'La longitud del campo debe ser entre 5 y 255 caracteres', 
),

'old_password' => Array 
(
	'required' => 'El campo de entrada es mandatorio.', 
	'matches' => 'La contraseña es incorrecta.', 
),

'promomessage' => Array 
(
	'length' => 'El campo debe ser menor a 255 caracteres.', 
),

'ann_title' => Array 
(
	'required' => 'El campo de entrada es mandatorio.', 
	'length' => 'El campo debe tener entre 3 a 50 caracteres.', 
),

'name' => Array 
(
	'required' => 'El campo es obligatorio.', 
	'length' => 'La longitud del campo debe ser entre 5 y 50 caracteres', 
),

'ann_desc' => Array 
(
	'required' => 'El campo de entrada es mandatorio.', 
	'length' => 'El campo debe tener entre 3 a 4096 caracteres.', 
),

'region' => Array 
(
	'required' => 'El campo es obligatorio.', 
	'length' => 'El campo debe tener entre 3 a 50 caracteres.', 
	'doesnotexist' => 'Región no encontrada.', 
),

'quantity' => Array 
(
	'required' => 'El campo de entrada es mandatorio.', 
),

'slogan' => Array 
(
	'length' => 'El campo debe tener entre 1 a 30 caracteres.', 
),

'group_name' => Array 
(
	'required' => 'El campo es obligatorio.', 
	'length' => 'El campo debe tener entre 3 a 60 caracteres.', 
	'groupname_exists' => 'El nombre ya se encuentra en uso.', 
),

'group_description' => Array 
(
	'required' => 'El campo es obligatorio.', 
	'length' => 'El campo debe tener entre 3 a 255 caracteres.', 
),

'group_image' => Array 
(
	'default' => 'La entrada no es válida.', 
	'valid' => 'pippo', 
	'required' => 'req.', 
	'type' => 'Las imágenes deben estar en formato .png', 
	'size' => 'El tamaño de la imágen del escudo de armas no debe exceder los 300kb.', 
),

'group_charname' => Array 
(
	'required' => 'El campo es obligatorio.', 
	'char_not_exist' => 'Este personaje no existe.', 
),

'group_message' => Array 
(
	'required' => 'El campo es obligatorio.', 
	'length' => 'El campo debe tener entre 1 a 1024 caracteres.', 
),

'group_subject' => Array 
(
	'required' => 'El campo es obligatorio.', 
	'length' => 'La longitud del campo debe ser entre 1 y 255 caracteres', 
),

'independentregion' => Array 
(
	'required' => 'El campo es obligatorio.', 
),

'captain' => Array 
(
	'required' => 'El campo es obligatorio.', 
),

'kingcandidate' => Array 
(
	'doesnotexist' => 'Este jugador no existe.', 
),

'foreignershourlycost' => Array 
(
	'default' => 'El valor debe ser mayor a 0.', 
),

'message' => Array 
(
	'default' => 'El campo es obligatorio.', 
),

'validity' => Array 
(
	'default' => 'Ingrese un valor >2', 
),

'title' => Array 
(
	'required' => 'El campo es obligatorio.', 
	'length' => 'El campo debe tener entre 3 a 50 caracteres.', 
),

'reason' => Array 
(
	'required' => 'El campo es obligatorio.', 
),

'wardrobe_parts' => Array 
(
	'default' => 'La imagen no es valida, el formato debe ser PNG y el tamaño debe ser <= 150K.', 
),

'date' => Array 
(
	'required' => 'El campo es obligatorio', 
),

'time' => Array 
(
	'required' => 'El campo es obligatorio', 
),

'location' => Array 
(
	'required' => 'El campo es obligatorio.', 
),

'domainname' => Array 
(
	'required' => 'El campo es obligatorio.', 
),

'discussionurl' => Array 
(
	'required' => 'Este campo es obligatorio.', 
),

);

?>