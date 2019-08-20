<?php defined('SYSPATH') OR die('No direct access allowed.');

$lang = Array
(

'username' => Array 
(
	'required' => 'Field entry is mandatory.', 
	'alpha_numeric' => 'Field must be alphanumeric.', 
	'length' => 'Field must be long from 5 to 20 characters.', 
	'username_exists' => 'Name is already used.', 
	'not_found' => 'Wrong username or password.', 
	'default' => 'Not a valid input.', 
),

'password' => Array 
(
	'required' => 'Field entry is mandatory.', 
	'length' => 'Password must be long at least 5 characters.', 
	'default' => 'Not a valid input.', 
),

'password_confirm' => Array 
(
	'required' => 'Field entry is mandatory.', 
	'matches' => 'passwords do not match.', 
	'default' => 'Not a valid input.', 
),

'email' => Array 
(
	'email' => 'Please specify a valid email address.', 
	'length' => 'Field max length is 30 characters.', 
	'email_exists' => 'This email address is already existing.', 
	'required' => 'Field entry is mandatory.', 
	'blocked_domain' => 'We do not accept temporary mailboxes.', 
	'default' => 'Not a valid input.', 
),

'captchaanswer' => Array 
(
	'captchaerror' => 'Please resolve the captcha.', 
),

'emailconfirm' => Array 
(
	'matches' => 'Email addresses don&#8217;t match.', 
	'required' => 'Field entry is mandatory.', 
	'default' => 'Not a valid input.', 
),

'referral_id' => Array 
(
	'numeric' => 'Please insert a valid Referral ID (it must be numeric).', 
	'id_notexisting' => 'The specifified ID was not found in our database.', 
),

'accepttos' => Array 
(
	'tos_notaccepted' => 'You must read and accept Privacy and Terms of Usage Rules.', 
),

'charname' => Array 
(
	'required' => 'Field entry is mandatory.', 
	'wrongformat' => 'Field should contain only European characters, no special characters and no numbers.', 
	'length' => 'Field length must be between 5 and 20 characters.', 
	'username_exists' => 'Name is already used.', 
	'not_found' => 'Wrong username/password', 
	'default' => 'Not a valid input.', 
),

'charsurname' => Array 
(
	'required' => 'Field entry is mandatory.', 
	'wrongformat' => 'Field should contain only European characters, no special characters and no numbers.', 
	'length' => 'Field length must be between 5 and 20 characters.', 
	'username_exists' => 'Name is already in use.', 
	'not_found' => 'Wrong username/password', 
	'default' => 'Not a valid input.', 
),

'charpoints' => Array 
(
	'chars' => 'There are some more points that can be allocated to the Statistics section.', 
	'notequal_50' => 'The total sum of the statistics has to be equal to 50 points.', 
	'notinrange' => 'One or more stats are not the in the required range of points (1-20).', 
),

'charspokenlanguage1' => Array 
(
	'required' => 'The field is mandatory.', 
),

'to' => Array 
(
	'required' => 'Field entry is mandatory.', 
	'length' => 'Field length must be between 1 and 20 characters.', 
	'char_not_exist' => 'Recipient not found.', 
	'incoherentmode' => 'You can&#8217;t set massive mode and specify a recipient', 
),

'choosenkingdom_id' => Array 
(
	'required' => 'Please choose a Kingdom.', 
),

'subject' => Array 
(
	'required' => 'Field entry is mandatory.', 
	'length' => 'Field length must be between 1 and 20 characters.', 
	'postcontainsbadwords' => 'The field contains some prohibited words (marked with ***).', 
),

'body' => Array 
(
	'length' => 'The field length must be between 20 and 4096 characters.', 
	'required' => 'Field is mandatory.', 
	'postcontainsbadwords' => 'The field contains some prohibited words (marked with ***).', 
),

'law_name' => Array 
(
	'required' => 'Field entry is mandatory.', 
	'length' => 'Field length must be between 3 and 50 characters.', 
),

'law_desc' => Array 
(
	'required' => 'Field entry is mandatory.', 
	'length' => 'Field length must be between 3 and 2048 characters.', 
),

'description' => Array 
(
	'alpha_numeric' => 'Field must be alphanumeric.', 
	'length' => 'Field length must be between 1 and 2048 characters.', 
),

'boarddescription' => Array 
(
	'required' => 'This field is mandatory', 
	'length' => 'Field length must be from 5 to 255 characters long', 
),

'old_password' => Array 
(
	'required' => 'Field entry is mandatory.', 
	'matches' => 'Current password is wrong.', 
),

'promomessage' => Array 
(
	'length' => 'Field length must be less than 255 characters.', 
),

'ann_title' => Array 
(
	'required' => 'Field entry is mandatory.', 
	'length' => 'Field length must be between 3 and 50 characters.', 
),

'name' => Array 
(
	'required' => 'This field is mandatory', 
	'length' => 'Field length must be from 5 to 50 characters long', 
),

'ann_desc' => Array 
(
	'required' => 'Field entry is mandatory.', 
	'length' => 'Field length must be between 3 and 4096 characters.', 
),

'region' => Array 
(
	'required' => 'This field is mandatory.', 
	'length' => 'The field length should be between 3 to 50 characters.', 
	'doesnotexist' => 'I could not find that region.', 
),

'quantity' => Array 
(
	'required' => 'Field entry is mandatory.', 
),

'slogan' => Array 
(
	'length' => 'Field lenght must be between 1 and 30 characters.', 
),

'group_name' => Array 
(
	'required' => 'This field is mandatory.', 
	'length' => 'The field length should be between 3 to 60 characters long.', 
	'groupname_exists' => 'The name you requested is already in use.', 
),

'group_description' => Array 
(
	'required' => 'This field is mandatory.', 
	'length' => 'The field length should be between 3 to 255 characters long.', 
),

'group_image' => Array 
(
	'default' => 'The input is not valid.', 
	'valid' => 'Image is not valid.', 
	'required' => 'req.', 
	'type' => 'Images have to be in png format.', 
	'size' => 'The Coat of arms image size must not exceed 300Kb.', 
),

'group_charname' => Array 
(
	'required' => 'This field is mandatory.', 
	'char_not_exist' => 'The Character does not exist.', 
),

'group_message' => Array 
(
	'required' => 'This field is mandatory.', 
	'length' => 'The field length should be between 1 to 1024 characters long.', 
),

'group_subject' => Array 
(
	'required' => 'This field is mandatory.', 
	'length' => 'The field length must be between 1 and 255 characters.', 
),

'independentregion' => Array 
(
	'required' => 'This field is mandatory.', 
),

'captain' => Array 
(
	'required' => 'This field is mandatory.', 
),

'kingcandidate' => Array 
(
	'doesnotexist' => 'This player does not exist.', 
),

'foreignershourlycost' => Array 
(
	'default' => 'Value must be > 0', 
),

'message' => Array 
(
	'default' => 'The field is mandatory', 
),

'validity' => Array 
(
	'default' => 'Enter a value > 2', 
),

'title' => Array 
(
	'required' => 'This field is mandatory.', 
	'length' => 'Field lenght must be between 3 and 50 characters.', 
),

'reason' => Array 
(
	'required' => 'This field is mandatory.', 
),

'wardrobe_parts' => Array 
(
	'default' => 'Image is not valid, the format must be PNG and size should be <= 150K.', 
),

'date' => Array 
(
	'required' => 'The field is mandatory', 
),

'time' => Array 
(
	'required' => 'The field is mandatory', 
),

'location' => Array 
(
	'required' => 'The field is mandatory.', 
),

'domainname' => Array 
(
	'required' => 'The field is mandatory.', 
),

'discussionurl' => Array 
(
	'required' => 'This field is mandatory.', 
),

);

?>