<?php
//========================//
if(INCLUDED !== true) {
	echo "Not Included!"; 
	exit;
}
$pathway_info[] = array('title' => $lang['realmstatus'], 'link' => '');
// ==================== //

// Define we want this page to be cached
define("CACHE_FILE", FALSE);

// Start a page desc
$PAGE_DESC = $lang['realm_status_desc'];

$Realm = array();
$Realm = $DB->select("SELECT * FROM `realmlist` ORDER BY `name`");
$i = 0;
foreach($Realm as $i => $result)
{
	$dbinfo = explode(';', $result['dbinfo']);

	// DBinfo column: char_host;char_port;char_username;char_password;charDBname;world_host;world_port;world_username;world_pass;worldDBname
	$Realm_DB_Info = array(
		'char_db_host' => $dbinfo['0'], // char host
		'char_db_port' => $dbinfo['1'], // char port
		'char_db_username' => $dbinfo['2'], // char user
		'char_db_password' => $dbinfo['3'], // char password
		'char_db_name' => $dbinfo['4'], //char db name
		'w_db_host' => $dbinfo['5'], // world host
		'w_db_port' => $dbinfo['6'], // world port
		'w_db_username' => $dbinfo['7'], // world user
		'w_db_password' => $dbinfo['8'], // world password
		'w_db_name' => $dbinfo['9'], // world db name
		);

	// Free up memory.
	unset($dbinfo, $DB_info); 

	// Establish the Character DB connection
	$CDB_EXTRA = new Database(
		$Realm_DB_Info['char_db_host'],
		$Realm_DB_Info['char_db_port'],
		$Realm_DB_Info['char_db_username'],
		$Realm_DB_Info['char_db_password'],
		$Realm_DB_Info['char_db_name']
		);

	// Establish the World DB connection	
	$WDB_EXTRA = new Database(
		$Realm_DB_Info['w_db_host'],
		$Realm_DB_Info['w_db_port'],
		$Realm_DB_Info['w_db_username'],
		$Realm_DB_Info['w_db_password'],
		$Realm_DB_Info['w_db_name']
		);
	
	// Free up memory
	unset($Realm_DB_Info);
   

    $population = 0;
    if($res_color == 1)
	{
		$res_color = 2;
	}
	else
	{
		$res_color=1;
	}
    $realm_type = $realm_type_def[$result['icon']];
	$realm_num = $result['id'];
    if(check_port_status($result['address'], $result['port']) == TRUE)
    {
        $res_img = $Template['path'].'/images/icons/uparrow2.gif';
        $population = $CDB_EXTRA->count("SELECT COUNT(*) FROM `characters` WHERE online=1");
        $uptime = time() - $DB->selectCell("SELECT `starttime` FROM `uptime` WHERE `realmid`='$realm_num' ORDER BY `starttime` DESC LIMIT 1");
    }
    else
    {
        $res_img = $Template['path'].'/images/icons/downarrow2.gif';
        $population_str = 'n/a';
        $uptime = 0;
    }
    $Realm[$i]['res_color'] = $res_color;
    $Realm[$i]['img'] = $res_img;
    $Realm[$i]['name'] = $result['name'];
    $Realm[$i]['type'] = $realm_type;
    $Realm[$i]['pop'] = $population;
    $Realm[$i]['uptime'] = $uptime;
    unset($WDB_EXTRA);
    unset($CDB_EXTRA);
}

function parse_time($number) 
{
	$time = array();
    $time['d'] = intval($number/3600/24);
	$time['h'] = intval(($number % (3600*24))/3600);
	$time['m'] = intval(($number % 3600)/60);
	$time['s'] = (($number % 3600) % 60);

	return $time;
}

function print_time($time_array) 
{
	global $lang;
	$count = 0;
	if($time_array['d'] > 0) 
	{
		echo $time_array['d'];
		echo "Days";
		$count++;
	}
	if($time_array['h'] > 0) 
	{
        if ($count > 0) 
		{
			echo ',';
		}
		echo $time_array['h'];
		echo "h";
		$count++;
	}
	if($time_array['m'] > 0) 
	{
		if ($count > 0)
		{
			echo ',';
		}
		echo $time_array['m'];
		echo "m";
		$count++;
	}
	if($time_array['s'] > 0) 
	{
		if ($count > 0)
		{
			echo ',';
		}
		echo $time_array['s'];
		echo "s";
	}
}
?>
