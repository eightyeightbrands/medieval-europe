<?PHP

class ClientManager
{
	//Returns exists (already exists & reactivated), actKey (activation key), userID...  
	public static function registerUser($getApi_user_id, $username, $email, $avatar, $referer_name, $camp_id, $userIP)
        {	
			//Register process
			$RegisterProcess = false;
			
			//Make sure you clean and check the data before running it in your DB 
			$getApi_user_id = $db->sql_escape($getApi_user_id);		
			$username = $db->sql_escape($username);
			$email = $db->sql_escape($email);
			$avatar = $db->sql_escape($avatar);
			$referer_name = $db->sql_escape($referer_name);
			$camp_id = $db->sql_escape($camp_id);
			$userIP = $db->sql_escape($userIP);
			
			//Make random string for player password (Anyway the user will auto login but we cannot leave the user without password) 
			$conss = "bcdfghjklmnpqrstvwxyz";
			$vocss = "aeiou";
			for ($x=0; $x < 3; $x++) {
				mt_srand ((double) microtime() * 1000000);
				$cons[$x] = substr($conss, mt_rand(0, strlen($conss)-1), 1);
				$vocs[$x] = substr($vocss, mt_rand(0, strlen($vocss)-1), 1);
			}
			$maxran = 999999;
			$random_numa = mt_rand(1, $maxran);
			$random_numb = mt_rand(1, $maxran);
			
			//User password
			$user_password = "$cons[0]$vocs[0]$random_numa$cons[1]$vocs[1]$random_numb$vocs[2]$cons[2]";
					
			//Check if username exists and in case its exists you create username with number to prevent duplicates
			$usernamevar = 0;
			$usernameCheck1 = self::usernameCheck($username);
			if ($usernameCheck1>0) {
				$usernamevar = 1;
				$username_clean = "$username_clean$random_numa";
				$username = $username_clean;
			}
			
			//Make sure the username not exists
			if ($usernamevar == 1) { 
				$usernameCheck2 = self::usernameCheck($username_clean);
				$username_clean = "$username_clean$random_numb";
				$username = $username_clean;
			}		
		
			//In case all is ok auto register the user in your database
			
			//Return that the user register
			$RegisterProcess = true;
			
			//Return the user registration status
			return $RegisterProcess;
        }
	
	
	//Check if username exists in your database and return your userID
	private static function usernameCheck($UserNameCheck)
	{
        //Clear before checking in DB
		$UserNameCheck = $db->sql_escape($UserNameCheck);
		
		//If username exists in DB return the userID number else return 0
		$userID = 0;
		
        return $userID;
	}
	
	
	//Check if player exists and return userID or false
	public static function exists($getApi_user_id)
	{
        //Clear before checking in DB
		$getApi_user_id = $db->sql_escape($getApi_user_id);
		
		//Check if userID exists in your DB and return your userID else return 0
        		
        return  $UserID;
	}
	
	//Log user and update login data
	public static function loginUser($getApi_user_id, $userIP, $avatar)
	{
		//Clear before checking in DB
		$getApi_user_id = $db->sql_escape($getApi_user_id);
		
		//Load user data and update IP, Avatar etc...in your database...after send true if all is ok
		$statusLogin = true;
		
		return $statusLogin;		
	}			
}
?>