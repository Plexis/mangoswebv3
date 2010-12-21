<?php
//========================//
if(INCLUDED !== TRUE) 
{
	echo "Not Included!"; 
	exit;
}
$pathway_info[] = array('title' => $lang['login'], 'link' => '');
// ==================== //

/*
	When posting to this page, It MUST be in this format:
	name='action' value='value'
	
	Values:
	'login' = logs the user in
		POST Values:
		login = username;
		pass = password;
	'logout' = logs the user out
	'profile' = redirects user to account screen
*/


// Tell the cache system not to cache this page
define('CACHE_FILE', FALSE);


// Lets check to see if the user has posted something
if(isset($_POST['action']))
{
	// If posted action was login
	if($_POST['action'] == 'login')
	{
		$login = $_POST['login'];
		$pass = $Account->sha_password($login, $_POST['pass']);
		$EMAIL = $DB->selectCell("SELECT `email` FROM `account` WHERE `username` LIKE '".$_POST['login']."' LIMIT 1");
		
		// initiate the login array, and send it in
		$params = array('username' => $login, 'sha_pass_hash' => $pass);
		$Login = $Account->login($params);
		
		// If account login was successful
		if($Login == 1)
		{
			// === Start of Forum Bridges. User login must be successfulll first === //
			
			// Check to see if we are using the phpbb3 registration module
			if($Config->get('module_phpbb3') == 1)
			{
				include('core/lib/class.phpbb.php');
				$phpbb = new phpbb($Config->get('module_phpbb3_path'), 'php');
				
				// If the user doesnt exist in the DB, then create the account
				if($phpbb->get_user_id_from_name($login) == FALSE)
				{
					$phpbb_vars = array(
						"username" => $_POST['login'], 
						"user_password" => $_POST['pass'], 
						"user_email" => $EMAIL, 
						"group_id" => "2"
					);
					$phpbb->user_add($phpbb_vars);
				}
			}
			
			// Else, if the phpbb3 module is not used, check to see if the vbulletin module is used
			elseif($Config->get('module_vbulletin') == 1)
			{
				include('core/lib/class.vbulletin-bridge.php');
				$vb = new vBulletin_Bridge();
				
				// Lets check to see if the user exists
				$check = $vb->fetch_userinfo_from_username($login);
				if(!$check)
				{
					// Register new user
					$userdata = array('username' => $login, 'password' => $_POST['pass'], 'email' => $EMAIL);
					@$vb->register_newuser($userdata, TRUE);
				}
			}
			
			// Once finished, redirect to the page we came from
			redirect($_SERVER['HTTP_REFERER'],1);
		}
	}
	
	// Else if the action is logout
	elseif($_POST['action'] == 'logout')
	{
		$Account->logout();
		redirect($_SERVER['HTTP_REFERER'],1);
	}
	
	// Otherwise redirect to profile
	elseif($_POST['action'] == 'profile')
	{
		redirect('?p=account',1);
	}
}
?>
