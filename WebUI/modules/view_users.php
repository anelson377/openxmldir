<?php
/*
	XML Directory - Web User Interface
	
	Joe Hopkins <joe@csma.biz>
	Copyright (c) 2005, McFadden Associates.  All rights reserved.
*/


if (isset($_POST['submit_add']))
{
	// add user
	$tmp_id = create_guid($tmp_id);
	$tmpInitSQL = "INSERT INTO users (id) VALUES ('$tmp_id')";
	if ($tmpInitRES = mysql_query($tmpInitSQL, $db))
	{
		// OK, show editor
		header("Location: index.php?module=edit_user&id=$tmp_id&new=true");
	} else {
	 // Failure
	 echo "Unable to create user.";
	}
} else {
	//display user listings
	render_HeaderSidebar("Open 79XX XML Directory - User View");
	output_view_users();
	render_Footer();		
}

//
//  FUNCTIONS
//

function output_view_users ()
{
	
	global $db;
	$xtpl=new XTemplate ("WebUI/modules/templates/view_users.html");

	// Content
	
	//custum order by
	if (isset($_GET['ob']))
	{
		if ($_GET['ob'] == "ob_username")
		{
			$ob = "username";
		} elseif ($_GET['ob'] == "ob_email") { 
			$ob = "email";
		} elseif ($_GET['ob'] == "ob_account_type") { 
			$ob = "account_type";
		}
	} else {
	$ob = "username";
	}
	$theSQL = "SELECT id,username,email,account_type FROM users ORDER BY $ob";
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
		$xtpl->assign("id",$in['id']);
		$xtpl->assign("username",$in['username']);
		$xtpl->assign("email",$in['email']);
		$xtpl->assign("account_type",$in['account_type']);
		if ($_SESSION['user_id'] == $in['id'] || $in['id'] == '0')
		{
			$xtpl->assign("delete","");
		} else {
			$xtpl->assign("delete","delete");
		}

		$xtpl->parse("main.row");
		$oddRow = !$oddRow;
	}
	
	// Output
	$xtpl->parse("main");
	$xtpl->out("main");		
}
?>