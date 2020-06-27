<?php
/*
	memos.php - Search Contacts Page
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
	//Security to stop unregistered users from going any further if 'Phone Security' is on.  
	require_once "templates/img_sec_breach.php";
	
} elseif (try_get('mem')) {
	// We are selecting a memo
	$memID = defang_input(try_get('mem'));
	$memQuery = "SELECT 
	memos.date AS date,
	memos.id AS id,
	memos.title AS title,
	memos.access AS access,
	memos.msg AS msg,
	memos.sender AS sender
	FROM memos WHERE memos.id='$memID'";
	$thememRES = mysql_query($memQuery, $db);
	
	if ($in = mysql_fetch_assoc($thememRES))
	{
		$xtpl=new XTemplate ("templates/memo_detail.xml");
		if ($in['access'] == 'Public' || $access_lvl == 'Unrestricted' || $ob_sec == 'No') 
		{
			$tmp_unixtime = $in['date'];
			$displaydate = date("n/d, h:ia Y" ,$tmp_unixtime);
			
			$xtpl->assign("title",$in['title']);
			$xtpl->assign("date",$displaydate);
			$xtpl->assign("sender",$in['sender']);
			$xtpl->assign("msg",$in['msg']);
			
		} else {
			//User did not meet security requirements to view memo
			$xtpl->assign("msg",'You must be using an Unrestricted phone to view this message');
		}
		$xtpl->parse("main");
		$xtpl->out("main");
	}
} else {
	//echo $ob_sec, $registered;
	list_memos ($ob_sec,$MAC,$registered);
}

function list_memos ($ob_sec,$MAC,$registered)
{
	/*
		Set page count to 28, count how many memos are going to be listed.
		List titles of memos, showing their dates by what the global says, and if user
		has chosen an order, order by their custom order
		Create URLs for each memo that will direct user to the text in the memo
	
	*/
	global $db;
	global $URLBase;
	global $access_lvl;
	global $memo_ob;
	
	$per_page = 27;//number of memos displayed on each page
		
		if (try_get('start'))
		{
			$start = defang_input(try_get('start'));
			$limitstart = 'LIMIT '.$start.','.$per_page;
			
		} else {
			$start = 0;
			$limitstart = 'LIMIT 0,'.$per_page;
		}
	
	if ($access_lvl == 'Restricted' && $ob_sec == 'Yes')
	{
		//User is restricted, apply security settings to show only 'Public' access 
		$securityQuery = "WHERE memos.access = 'Public'";
	} else {
		//Security settings are not in place, or is restricted, do not restrict to 'Public' access
		$securityQuery = "";
	}
	
	$countQuery = "SELECT
		COUNT(memos.id) AS total
		FROM memos
		$securityQuery";
	
	$theCountRES = mysql_query($countQuery, $db);
	//Fetch total items
	if ($in = mysql_fetch_assoc($theCountRES))
	{
		$totalCount = $in['total'];
	}
	
	//Calc remaining rows
	$remainingRows = ($totalCount - $start);
		
		
	//Get order by preferences
	if (try_get('ob'))
	{
		//user has chosen an option from the phone
		$memo_ob = defang_input(try_get('ob'));
		$ob_saved =  "&amp;ob=".$memo_ob; //save for the 'more' object
		if ($memo_ob == "Date")
		{
			//User has selected on phone to make order DESC, to show the oldest first
			$memo_ob_sql = $memo_ob." DESC";
		} else {
			//User has selected on phone to order by sender or title, which default to ASC order
			$memo_ob_sql = $memo_ob;
		}
	} else {
		//user is using the settings according to the global preferences
		$ob_saved =  ""; //do not save for 'more' object, user has not selected a custom order
		if ($memo_ob == "Date")
		{
			//global says to order by date, make order DESC, to show the oldest first
			$memo_ob_sql = $memo_ob." DESC";
		} else {
			//global says to order by sender or title, dont need to order by DESC
			$memo_ob_sql = $memo_ob;
		}
	}
	
	//Qry
	$browseQuery = "SELECT
		memos.id AS id,
		memos.title AS title,
		memos.date AS date,
		memos.access AS access,
		memos.sender AS sender
		FROM memos
		$securityQuery
		ORDER BY memos.$memo_ob_sql
		$limitstart";
		
	
	if ($remainingRows <= $per_page)
		{
			$prompt = ($start + 1) ." to ". ($start + $remainingRows) ." of ". $totalCount.".";
		} else {
			$prompt = ($start + 1) ." to ". ($start + $per_page) ." of ". $totalCount.".";
		}
	if ($totalCount != '0')
	{	
		$theBrowseRES = mysql_query($browseQuery, $db);
		$xtpl=new XTemplate ("templates/memo_menu.xml");
		
			while ($in2 = mysql_fetch_assoc($theBrowseRES))
			{
				if ($in2['access'] == 'Public' || $access_lvl == 'Unrestricted' || $ob_sec == 'No')	
				{	
					//User is registered, or a public Container is listed, or object security is turned off
					$tmp_unixtime = $in2['date'];
					$displaydate = date("n/d-" ,$tmp_unixtime);
					
					$tmpTitle = $displaydate.$in2['title'] ;
					$title = substr($tmpTitle,0,25);
					
					$xtpl->assign("prompt",$prompt);
					$xtpl->assign("title",$title);
					$xtpl->assign("url_base",$URLBase);
					$xtpl->assign("MAC",$MAC);
					$xtpl->assign("ID",$in2['id']);
					$xtpl->parse("main.memo_menu");
				}
			}			
		
		// If there are more entries, show Next
		if ($remainingRows > $per_page)
		{
			$start = $start + $per_page;
			$xtpl->assign("title","More");
			$xtpl->assign("saved_order",$ob_saved);
			$xtpl->assign("url_base",$URLBase);
			$xtpl->assign("start",$start);
			$xtpl->assign("MAC",$MAC);
			$xtpl->parse("main.memo_more");
		}
		
		// Display objects that can re-order the memos
		$order_title[0] = 'Sender';
		$order_title[1] = 'Date';
		$order_title[2] = 'Title';
		$number = 3;
		$x = 0;
		while ($x < $number) {
			$xtpl->assign("title",$order_title[$x]);
			$xtpl->assign("ob",$order_title[$x]);
			$xtpl->assign("url_base",$URLBase);
			$xtpl->assign("MAC",$MAC);
			$xtpl->parse("main.order_opt");
		++$x;
		}
		
		//output
		$xtpl->parse("main");
		$xtpl->out("main");

	} else {
		//There are no memos
		require_once "templates/img_empty_cont.php";
	}
}
?>
