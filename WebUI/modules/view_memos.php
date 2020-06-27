<?php
/*
	view_memos.php
	XML Directory
	
	Joe Hopkins <joe@csma.biz>
	Copyright (c) 2005, McFadden Associates.  All rights reserved.
*/


if (isset($_POST['submit_add']))
{
	//add new memo
	$tmp_id = create_guid($tmp_id);
	$tmpInitSQL = "INSERT INTO memos (id) VALUES ('$tmp_id')";
	if ($tmpInitRES = mysql_query($tmpInitSQL, $db))
	{
	//memo has been created
		$tmp_date = time();
		$tmp_from = $_SESSION['user_name'];	
		
		$tmpUpdateSQL = "UPDATE memos SET
				date = '$tmp_date',
				sender = '$tmp_from'		
				WHERE id ='$tmp_id'";
				mysql_query($tmpUpdateSQL, $db);
		// show editor
		header("Location: index.php?module=edit_memos&id=$tmp_id&new=true");
	} else {
	 // Failure
	echo "Unable to create memo.";
	}
} else {
	//display memo listings
	render_HeaderSidebar("Open 79XX XML Directory - Memo View");
	output_view_memos();
	render_Footer();		
}

//
//  FUNCTIONS
//

function output_view_memos()
{
	global $db;
	$xtpl=new XTemplate ("WebUI/modules/templates/view_memos.html");
	
	
	$obprefSQL = "SELECT memo_ob FROM global_pref WHERE preference = 'primary'";
	$obRES = mysql_query($obprefSQL, $db);
	if ($gl = mysql_fetch_assoc($obRES))
	{
		$memo_ob = $gl['memo_ob'];
	} else {
		//sql error
		$memo_ob = "Date";
	}
	//custum order by
	if (isset($_GET['ob']))
	{
		if ($_GET['ob'] == "ob_date")
		{
			$ob = "date DESC";
		} elseif ($_GET['ob'] == "ob_title") { 
			$ob = "title";
		} elseif ($_GET['ob'] == "ob_access") { 
			$ob = "access";
		} elseif ($_GET['ob'] == "ob_sender") { 
			$ob = "sender";
		}
	} else {
		if ($memo_ob == "Date")
		{
			//global says to order by date, make order DESC, to show the oldest first
			$ob = $memo_ob." DESC";
		} else {
			//global says to order by sender or title, dont need to order by DESC
			$ob = $memo_ob;
		}
	}
	
	$theSQL = "SELECT id,title,access,sender,date FROM memos ORDER BY $ob";
	$theRES = mysql_query($theSQL, $db);
	$oddRow = true;
	while ($in = mysql_fetch_assoc($theRES))
	{
		//Generate data rows
		if ($oddRow)
		{
			$xtpl->assign("bg","#EFEFEF");
		} else {
			$xtpl->assign("bg","#DFDFDF");
		}
		
		$tmp_unixtime = $in['date'];
		$displaydate = date("n/d, h:i A" ,$tmp_unixtime);
		
		$xtpl->assign("id",$in['id']);
		$xtpl->assign("title",$in['title']);
		$xtpl->assign("date",$displaydate);
		$xtpl->assign("access",$in['access']);
		$xtpl->assign("from",$in['sender']);

		$xtpl->parse("main.row");
		//alternate bg color
		$oddRow = !$oddRow;
	}
	
	// Output
	$xtpl->parse("main");
	$xtpl->out("main");		
}
?>