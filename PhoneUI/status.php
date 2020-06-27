<?php
/*
	status.php - View employee status
	XML Directory
	
	Joe Hopkins <joe@csma.biz>
	Copyright (c) 2005, McFadden Associates.  All rights reserved.
*/


require_once "../lib/xtpl.php";
require_once "../lib/utils.php";
require_once "../lib/mysql.php";



require_once "lib/urlbase.php";
require_once "lib/security.php";//grab mac address info, along with global preferences
require_once "lib/headers.php";


if ($ph_sec == 'Yes' && $registered == 'FALSE')
{
	//Security to stop unregistered users from going any further if 'Phone Security' is on.  XML images cannot be templated with XTPL.
	require_once "templates/img_sec_breach.php";
} else {
	if (isset($_GET['ur'])) 
	{
		$urMAC = defang_input($_GET['ur']);
		show_status($MAC,$urMAC);
		
	} elseif (isset($_GET['view_my_status'])) {
		
		show_status($MAC,$MAC);
		
	} elseif (isset($_GET['others_status'])) {
	
		//User has inputed all information needed 
		//A list of the users in a certain location 
		//according to their status is now displayed
		
		$others_status = defang_input($_GET['others_status']);
		
		if ($others_status == 'in')
		{
			//user wants to view people in the available, status
			$loc_sql = "WHERE phone.status = 1 AND phone.access_lvl != 'unknown'";
		} elseif ($others_status == 'out') {
			//user wants to view people unavailable, status
			$loc_sql = "WHERE phone.status = 0 AND phone.access_lvl != 'unknown'";
		} else {
			//user wants to view everyones' status
			$loc_sql = "WHERE phone.access_lvl != 'unknown'";
		}
		
		$per_page = 31;//number of phones displayed on each page
			
		if (isset($_GET['start']))
		{
			$start = defang_input($_GET['start']);
			$limitstart = 'LIMIT '.$start.','.$per_page;
			
		} else {
			$start = 0;
			$limitstart = 'LIMIT 0,'.$per_page;
		}
		//Number of phones to be displayed per page
		$countQuery = "SELECT
			COUNT(phone.id) AS total
			FROM phone
			$loc_sql";
	
		$theCountRES = mysql_query($countQuery, $db);
		
		//Fetch total phones
		if ($in = mysql_fetch_assoc($theCountRES))
		{
			$totalCount = defang_input($in['total']);
		}
		
		//Calc remaining rows
		$remainingRows = ($totalCount - $start);
		
		$browseQuery = "SELECT 
				phone.id AS id,
				phone.number AS number,
				phone.fname AS fname,
				phone.lname AS lname,
				phone.number AS number,
				phone.away_msg AS away_msg,
				phone.MAC AS urMAC
				FROM phone
				$loc_sql
				ORDER BY phone.lname
				$limitstart";
		
				$theBrowseRES = mysql_query($browseQuery, $db);
		
		$xtpl=new XTemplate ("templates/status_listing.xml");
		
		if ($remainingRows <= $per_page)
		{
			$prompt = ($start + 1) ." to ". ($start + $remainingRows) ." of ". $totalCount.".";
		} else {
			$prompt = ($start + 1) ." to ". ($start + $per_page) ." of ". $totalCount.".";
		}
		
		while ($in2 = mysql_fetch_assoc($theBrowseRES))
		{
			//assign users to listing of status
			$tmp_dis_msg = num2txt($in2['away_msg']);
			
			if ($in2['away_msg'] != '9' && $in2['away_msg'] != '')
			{
				$tmp_star = '*';
			} else {
				$tmp_star = '';
			}
			
			$tmpTitle = $in2['lname'].",".$in2['fname']." (". $in2['number'].")".$tmp_star;
			
			
			$title = substr($tmpTitle,0,27);
			
			$urMAC= $in2['urMAC'];
							
			$xtpl->assign("title",$title);
			$xtpl->assign("url_base",$URLBase);
			$xtpl->assign("MAC",$MAC);
			$xtpl->assign("ID",$urMAC);
			$xtpl->parse("main.contact_menu");
		}
			
		if ($remainingRows > $per_page)
		{
			// There are more entries, show More
			$start = $start + $per_page;
	
	
			$xtpl->assign("start","$start");
			$xtpl->assign("title","More");
			$xtpl->assign("url_base",$URLBase);
			$xtpl->assign("MAC",$MAC);
			$xtpl->assign("others_status",$others_status);
			$xtpl->assign("start",$start);
			$xtpl->parse("main.contact_more");
		}	
		
		if ($others_status == 'in')
		{
			$xtpl->assign("heading","Available (Currently ".$totalCount.")");
		} elseif ($others_status == 'out') {
			$xtpl->assign("heading","Unavailable (Currently ".$totalCount.")");
		} else {
			$xtpl->assign("heading","Show All (Currently ".$totalCount.")");
		}
		
		$xtpl->assign("prompt",$prompt);
		$xtpl->parse("main");
		$xtpl->out("main");
		
	} elseif (isset($_GET['status_others_index'])) {
		//user has selected to view others' status
		//display screen with mroe specific options wiht who to show
		$xtpl=new XTemplate ("templates/status_others_index.xml");
		$num_in = count_qry("WHERE phone.status = 1 AND phone.access_lvl != 'unknown'");
		$num_out = count_qry("WHERE phone.status = 0 AND phone.access_lvl != 'unknown'");
		$num_all = count_qry("WHERE phone.access_lvl != 'unknown'");
		
		$xtpl->assign("num_in",$num_in);
		$xtpl->assign("num_out",$num_out);
		$xtpl->assign("all",$num_all);
		$xtpl->assign("MAC",$MAC);
		$xtpl->assign("url_base",$URLBase);
		$xtpl->parse("main");
		$xtpl->out("main");
		
	} elseif (isset($_GET['custom_msg'])) {
		
		//Get user's location and custom message 
		$tmp_assign_msg = defang_input($_GET['custom_msg']);
		$tmp_location = defang_input($_GET['location']);
		$tmp_date = time();
		
		//save the status to database
		$tmpUpdateSQL = "UPDATE phone SET
			status = '$tmp_location',
			date = '$tmp_date',
			away_msg = '$tmp_assign_msg'		
			WHERE MAC ='$MAC'";
			
			mysql_query($tmpUpdateSQL, $db);
		
		//Display status change success screen to user
		show_status($MAC,$MAC);
		
	} elseif (isset($_GET['location'])) {
		if ($_GET['msg'] == '0')
		{
			//user requests a custom msg
			$xtpl=new XTemplate ("templates/status_custom.xml");
			
			$xtpl->assign("MAC",$MAC);
			$xtpl->assign("url_base",$URLBase);
			$xtpl->assign("location",defang_input($_GET['location']));
			$xtpl->parse("main");
			$xtpl->out("main");
			
		} else {
			//Grab data from user
			$tmp_assign_msg = defang_input($_GET['msg']);
			$tmp_location = defang_input($_GET['location']);
			$tmp_date = time();
		
			//save to data base
			$tmpUpdateSQL = "UPDATE phone SET
				status = '$tmp_location',
				date = '$tmp_date',
				away_msg = '$tmp_assign_msg'		
				WHERE MAC ='$MAC'";
			
			mysql_query($tmpUpdateSQL, $db);
		
		
		//Display user their status
		show_status($MAC,$MAC);
		}
	} elseif (isset($_GET['in_office'])) {
		
		$tmp_in_office = defang_input($_GET['in_office']);
		
		$xtpl=new XTemplate ("templates/spec_status.xml");
		$xtpl->assign("MAC",$MAC);
		$xtpl->assign("url_base",$URLBase);
		
		$statusqry = "SELECT
				phone.away_msg AS away_msg
				FROM phone
				WHERE MAC = '$MAC'";
			
		$theCountRES = mysql_query($statusqry, $db);
		
		//Fetch away msg status
		if ($in = mysql_fetch_assoc($theCountRES))
		{
			if ($in['away_msg'] == '1')
			{
				$xtpl->assign("1",'*');
			} elseif ($in['away_msg'] == '2') {
				$xtpl->assign("2",'*');
			} elseif ($in['away_msg'] == '3') {
				$xtpl->assign("3",'*');
			} elseif ($in['away_msg'] == '0') {
				$xtpl->assign("9",'*');
			} else {
				$xtpl->assign("0",'*');
			}
		}
		
		if ($tmp_in_office == "true")
		{
			//User is available
			$xtpl->assign("location",'1');
			
		} elseif ($tmp_in_office == "false") {
			//User is not available
			$xtpl->assign("location",'0');
		} else {
			//User is not available
			$xtpl->assign("location",'0');
		}
		
		$xtpl->parse("main");
		$xtpl->out("main");
	
	} elseif (isset($_GET['my_status'])) {
		//User has requested to change their status
		if ($registered == "TRUE")
		{
			$xtpl=new XTemplate ("templates/my_status.xml");
			$statusqry = "SELECT
				phone.status AS status
				FROM phone
				WHERE MAC = '$MAC'";
			
			$theCountRES = mysql_query($statusqry, $db);
			
			//Fetch phone availablility
			if ($in = mysql_fetch_assoc($theCountRES))
			{
				if ($in['status'] == '1')
				{
					$xtpl->assign("available",'*');
					$xtpl->assign("unavailable",'');
				} else {
					$xtpl->assign("available",'');
					$xtpl->assign("unavailable",'*');
				}
			}
			//show prompt to select in office or out of office
			$xtpl->assign("MAC",$MAC);
			$xtpl->assign("url_base",$URLBase);
			$xtpl->parse("main");
			$xtpl->out("main");
		} else {
			//User must have a registered MAC to set a status, display error page
			require_once "templates/img_sec_breach.php";
		}
	} else {
	//User is requesting the main status page,
	//where they choose to view others' status, or set their own
	$xtpl=new XTemplate ("templates/status_index.xml");
	$xtpl->assign("MAC",$MAC);
	$xtpl->assign("url_base",$URLBase);
	$xtpl->parse("main");
	$xtpl->out("main");
	}
}


//
//
// Functions
//
//

function count_qry($location)
{
	global $db;
	
	$countQuery = "SELECT
			COUNT(phone.id) AS total
			FROM phone
			$location
			AND phone.access_lvl != 'unknown'";
			$theCountRES = mysql_query($countQuery, $db);
	if ($in = mysql_fetch_assoc($theCountRES))
	{
		//display # in category, beforegoing into it
		return $in['total'];
	}
}

function show_status ($MAC,$urMAC)
{
	/*
		The user has selected the message she wishes to view, the MAC address
		is used to select the corresponding fields from the db
		the "away_msg" if using the preprogrammed is a number, that corresponds to a msg
		written in the php in the function.  If user has a custom message, the message
		is written in text in the away_msg field
	*/
	global $db;
	global $URLBase;
	
	$browseQuery = "SELECT 
		phone.number AS number,
		phone.fname AS fname,
		phone.lname AS lname,
		phone.away_msg AS away_msg,
		phone.date AS date,
		phone.status AS status
		FROM phone
		WHERE phone.MAC = '$urMAC'";
	
		$theContactRES = mysql_query($browseQuery, $db);

	if ($in = mysql_fetch_assoc($theContactRES))
	{
		//Assign user msg info to the screen
		$tmp_unixtime = $in2['date'];
		$displaydate = date("n/d g:ia" ,$tmp_unixtime);
		
		
		$xtpl=new XTemplate ("templates/status_detail.xml");
		if ($MAC == $urMAC)
		{
			$xtpl=new XTemplate ("templates/view_my_status.xml");
			$xtpl->assign("url_base",$URLBase);
			$xtpl->assign("MAC",$MAC);
			$tmp_display = num2txt($in['away_msg']);
			$xtpl->assign("msg",$tmp_display);
		} else {
			$xtpl=new XTemplate ("templates/status_detail.xml");
			$xtpl->assign("msg",$in['lname'].",".$in['fname']);
			
		}
		
		$curphone = parse_phone($in['number']);
		$number = return_dial($curphone);
		
		$tmp_display = num2txt($in['away_msg']);
		
		$tmp_location = $in['status'];
		
		if ($tmp_location == '1')
		{
			$display_away = "Available since ".$displaydate;
			$tmp_your = "Available";
		} elseif ($tmp_location == '0') {
			$display_away = "Unavailable since ".$displaydate;
			$tmp_your = "Unavailable";
		} else {
			$display_away = "Availability Unknown since ".$displaydate;
			$tmp_your = "Status Unknown";
		}
		
		$xtpl->assign("prompt",$tmp_display);
		$xtpl->assign("tmpyour",$tmp_your);
		$xtpl->assign("tmpTitle",$display_away);
		$xtpl->assign("number",$number);
		$xtpl->parse("main");
		$xtpl->out("main");
	}
}

function num2txt($number)
{
	if ($number != '')
	{
		if ($number == '1')
		{
			$tmp_display = "Not taking calls.";
		} elseif ($number == '2') {
			$tmp_display = "Please call me.";
		} elseif ($number == '3') {
			$tmp_display = "Stepped out, brb.";
		} elseif ($number == '9'){
			$tmp_display = "No message supplied.";
		} else {
			$tmp_display = $number;
		}
	} else {
		$tmp_display = "No message supplied.";
	}
	return $tmp_display;
}

function parse_phone ($in_phone)
{
	//remove extraneous characters from phone number
	$chk_phone = trim($in_phone);
	$chkExp = "(\-)|(\.)|(\()|(\))|(\ )"; 
	$out_phone = trim(eregi_replace($chkExp, "", $chk_phone));
	return $out_phone;
}


function return_dial($phone)
{
	/*
		The user is able to hit dial from this screen
	*/
	
	$number = $phone;
	return $number;
}
?>