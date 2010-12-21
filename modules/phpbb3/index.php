<?php
/*
PHPBB Forum manipulation Class
By Felix Manea (felix.manea@gmail.com)
www.ever.ro
Licensed under LGPL
NOTE: You are required to leave this header intact.
*/
//bag clasa
include("core/lib/class.phpbb.php");
define('MODULE_NAME', $_GET['module']);

$phpbb_action = @$_GET["op"];
//***************************************************************
//parameters used at class construction
//first parameter = absoulute physical path of the phpbb 3 forum ($phpbb_root_path variable)
//second parameter = php scripts extensions ($phpEx variable)
$phpbb = new phpbb($Config->get('module_phpbb3_path'), "php");

switch($phpbb_action)
{
	case "login":
		//TESTING DATA
		$phpbb_vars = array("username" => "test", "password" => "123123");
		//END TESTING DATA
		$phpbb_result = $phpbb->user_login($phpbb_vars);
	break;
	
	case "logout":
		$phpbb_result = $phpbb->user_logout();
	break;
	
	case "loggedin":
		$phpbb_result = $phpbb->user_loggedin();
	break;
	
	case "user_add":
		//TESTING DATA
		$phpbb_vars = array("username" => "test", "user_password" => "123123", "user_email" => "test@test.com", "group_id" => "2");
		//END TESTING DATA
		$phpbb_result = $phpbb->user_add($phpbb_vars);
	break;
	
	case "user_delete":
		//TESTING DATA
		$phpbb_vars = array(/*"user_id" => "53", */"username" => "test");
		//END TESTING DATA
		$phpbb_result = $phpbb->user_delete($phpbb_vars);
	break;
	
	case "user_update":
		//TESTING DATA
		$phpbb_vars = array(/*"user_id" => "53", */"username" => "test", "user_email" => "1@2.com", "user_yim" => "my_yim", "user_website" => "http://www.ever.ro");
		//END TESTING DATA
		$phpbb_result = $phpbb->user_update($phpbb_vars);
	break;
	
	case "change_password":
		//TESTING DATA
		$phpbb_vars = array(/*"user_id" => "53", */"username" => "test", "password" => "123123");
		//END TESTING DATA
		$phpbb_result = $phpbb->user_change_password($phpbb_vars);
	break;
}


if(isset($phpbb_result))
{
	echo $phpbb_result."<br /><br />";
}
?>
<a href="?module=<?php echo MODULE_NAME ?>&op=loggedin">loggedin</a><br />
<a href="?module=<?php echo MODULE_NAME ?>&op=login">login</a><br />
<a href="?module=<?php echo MODULE_NAME ?>&op=logout">logout</a><br />
<a href="?module=<?php echo MODULE_NAME ?>&op=user_add">user_add</a><br />
<a href="?module=<?php echo MODULE_NAME ?>&op=user_delete">user_delete</a><br />
<a href="?module=<?php echo MODULE_NAME ?>&op=user_update">user_update</a><br />
<a href="?module=<?php echo MODULE_NAME ?>&op=change_password">change_password</a><br />