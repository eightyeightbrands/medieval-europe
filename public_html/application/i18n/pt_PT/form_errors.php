<?php defined('SYSPATH') OR die('No direct access allowed.');

$lang = Array
(

'username' => Array 
(
	'required' => 'Campo de preenchimento obrigatório.', 
	'alpha_numeric' => 'O campo deve ser preenchido apenas com números e caracteres.', 
	'length' => 'Campo deve ter entre 5 a 20 caracteres.', 
	'username_exists' => 'Este nome já está a ser usado.', 
	'not_found' => 'Nome de utilizador ou Palavra-passe incorretos.', 
	'default' => 'Inserção inválida.', 
),

'password' => Array 
(
	'required' => 'Campo de preenchimento obrigatório.', 
	'length' => 'A palavra-passe deve ter pelo menos 5 carateres.', 
	'default' => 'Inserção inválida.', 
),

'password_confirm' => Array 
(
	'required' => 'Campo de preenchimento obrigatório.', 
	'matches' => 'As palavras-passes que introduziste não são idênticas.', 
	'default' => 'Inserção inválida.', 
),

'email' => Array 
(
	'email' => 'Por favor introduz um email válido.', 
	'length' => 'O campo deve ter no máximo 30 carateres.', 
	'email_exists' => 'Este email já está a ser utilizado.', 
	'required' => 'Campo de preenchimento obrigatório.', 
	'blocked_domain' => 'Não aceitamos emails temporários.', 
	'default' => 'Inserção inválida.', 
),

'captchaanswer' => Array 
(
	'captchaerror' => 'Por favor, resolver o captcha.', 
),

'emailconfirm' => Array 
(
	'matches' => 'Os emails que introduziste não são idênticos.', 
	'required' => 'Campo de preenchimento obrigatório.', 
	'default' => 'Inserção inválida.', 
),

'referral_id' => Array 
(
	'numeric' => 'Por favor insere um "ID de Padrinho" válido.', 
	'id_notexisting' => 'O ID de Padrinho inserido não foi encontrado.', 
),

'accepttos' => Array 
(
	'tos_notaccepted' => 'Tens que ler e aceitar os Termos de Utilização e de Privacidade.', 
),

'charname' => Array 
(
	'required' => 'Campo de preenchimento obrigatório.', 
	'wrongformat' => 'Este campo deve contar apenas carateres europeus, sem números ou carateres especiais.', 
	'length' => 'Campo deve ter entre 5 a 20 carateres.', 
	'username_exists' => 'O nome escolhido já está em uso.', 
	'not_found' => 'Nome de utilizador ou Palavra-passe incorretos.', 
	'default' => 'Inserção inválida.', 
),

'charsurname' => Array 
(
	'required' => 'Campo de preenchimento obrigatório.', 
	'wrongformat' => 'Este campo deve contar apenas carateres europeus, sem números ou carateres especiais.', 
	'length' => 'Campo deve ter entre 5 e 20 carateres.', 
	'username_exists' => 'O nome escolhido já está a ser usado.', 
	'not_found' => 'Nome de utilizador ou Palavra-passe incorretos.', 
	'default' => 'Inserção inválida.', 
),

'charpoints' => Array 
(
	'chars' => 'Há ainda alguns pontos a serem distribuídos nas estatísticas.', 
	'notequal_50' => 'A soma total das estatísticas tem que ser igual a 50 pontos.', 
	'notinrange' => 'Uma ou mais caraterísticas não respeitam o limite previsto (1-20).', 
),

'charspokenlanguage1' => Array 
(
	'required' => 'Campo de preenchimento obrigatório.', 
),

'to' => Array 
(
	'required' => 'Campo de preenchimento obrigatório.', 
	'length' => 'Campo deve ter entre 1 a 20 carateres.', 
	'char_not_exist' => 'Este destinatário não existe.', 
	'incoherentmode' => 'Não podes enviar uma mensagem em massa e indicar um destinatário.', 
),

'choosenkingdom_id' => Array 
(
	'required' => 'Por favor escolhe um Reino.', 
),

'subject' => Array 
(
	'required' => 'Campo de preenchimento obrigatório.', 
	'length' => 'Campo deve ter entre 1 a 20 carateres.', 
	'postcontainsbadwords' => 'O campo contém algumas palavras proibidas (marcadas com  ***).', 
),

'body' => Array 
(
	'required' => 'Campo de preenchimento obrigatório.', 
	'postcontainsbadwords' => 'O campo contém algumas palavras proibidas (marcadas com  ***).', 
),

'law_name' => Array 
(
	'required' => 'Campo de preenchimento obrigatório.', 
	'length' => 'Campo deve ter entre 3 a 50 carateres.', 
),

'law_desc' => Array 
(
	'required' => 'Campo de preenchimento obrigatório.', 
	'length' => 'Campo deve ter entre 3 a 2048 carateres.', 
),

'description' => Array 
(
	'alpha_numeric' => 'O campo deve ser preenchido apenas com números e carateres.', 
	'length' => 'Campo deve ter entre 1 a 2048 carateres.', 
),

'boarddescription' => Array 
(
	'required' => 'Campo de preenchimento obrigatório.', 
	'length' => 'Campo deve ter entre 5 a 255 carateres.', 
),

'old_password' => Array 
(
	'required' => 'Campo de preenchimento obrigatório.', 
	'matches' => 'A atual palavra-passe está incorreta.', 
),

'promomessage' => Array 
(
	'length' => 'Campo deve ter menos de 255 carateres.', 
),

'ann_title' => Array 
(
	'required' => 'Campo de preenchimento obrigatório.', 
	'length' => 'Campo deve ter entre 3 a 50 carateres.', 
),

'name' => Array 
(
	'required' => 'Campo de preenchimento obrigatório.', 
	'length' => 'Campo deve ter entre 5 a 50 carateres.', 
),

'ann_desc' => Array 
(
	'required' => 'Campo de preenchimento obrigatório.', 
	'length' => 'Campo deve ter entre 3 a 4096 carateres.', 
),

'region' => Array 
(
	'required' => 'Campo de preenchimento obrigatório.', 
	'length' => 'Campo deve ter entre 3 a 50 carateres.', 
	'doesnotexist' => 'Região não encontrada.', 
),

'quantity' => Array 
(
	'required' => 'Campo de preenchimento obrigatório.', 
),

'slogan' => Array 
(
	'length' => 'Campo deve ter entre 1 a 30 carateres.', 
),

'group_name' => Array 
(
	'required' => 'Campo de preenchimento obrigatório.', 
	'length' => 'Campo deve ter entre 3 a 60 carateres.', 
	'groupname_exists' => 'O nome escolhido já está a ser utilizado.', 
),

'group_description' => Array 
(
	'required' => 'Campo de preenchimento obrigatório.', 
	'length' => 'Campo deve ter entre 3 e 255 carateres.', 
),

'group_image' => Array 
(
	'default' => 'Inserção inválida.', 
	'valid' => 'A imagem não é válida.', 
	'required' => 'necessária.', 
	'type' => 'A imagem deve ter o formato .png', 
	'size' => 'A imagem do Escudo não pode exceder os 300kb.', 
),

'group_charname' => Array 
(
	'required' => 'Campo de preenchimento obrigatório.', 
	'char_not_exist' => 'Esse personagem não existe.', 
),

'group_message' => Array 
(
	'required' => 'Campo de preenchimento obrigatório.', 
	'length' => 'Campo deve ter entre 1 a 1024 carateres.', 
),

'group_subject' => Array 
(
	'required' => 'Este campo é obrigatório.', 
	'length' => 'Este campo deve ter entre 1 e 255 caracteres.', 
),

'independentregion' => Array 
(
	'required' => 'Campo de preenchimento obrigatório.', 
),

'captain' => Array 
(
	'required' => 'Campo de preenchimento obrigatório.', 
),

'kingcandidate' => Array 
(
	'doesnotexist' => 'Este(a) jogador(a) não existe.', 
),

'foreignershourlycost' => Array 
(
	'default' => 'Inserir um valor > 0', 
),

'message' => Array 
(
	'default' => 'Campo de preenchimento obrigatório.', 
),

'validity' => Array 
(
	'default' => 'Inserir um valor > 2', 
),

'title' => Array 
(
	'required' => 'Campo de preenchimento obrigatório.', 
	'length' => 'O comprimento do campo deve estar entre 3 a 50 caracteres.', 
),

'reason' => Array 
(
	'required' => 'Campo de preenchimento obrigatório.', 
),

'wardrobe_parts' => Array 
(
	'default' => 'Imagem inválida; deve ter o formato .png e a dimensão máxima de 150K.', 
),

'date' => Array 
(
	'required' => 'Campo de preenchimento obrigatório.', 
),

'time' => Array 
(
	'required' => 'Campo de preenchimento obrigatório.', 
),

'location' => Array 
(
	'required' => 'Campo de preenchimento obrigatório.', 
),

'domainname' => Array 
(
	'required' => 'Campo de preenchimento obrigatório.', 
),

);

?>