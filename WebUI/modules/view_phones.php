<?php
/*
	XML Directory - Web User Interface
	
	Joe Hopkins <joe@csma.biz>
	Copyright (c) 2005, McFadden Associates.  All rights reserved.
*/


if (isset($_POST['submit_add']))
{
	// add phone
	$tmp_id = create_guid($tmp_id);
	$tmpInitSQL = "INSERT INTO phone (id) VALUES ('$tmp_id')";
	if ($tmpInitRES = mysql_query($tmpInitSQL, $db))
	{
		// OK, show editor
		header("Location: index.php?module=edit_phone&id=$tmp_id&new=true");
	} else {
		 // Failure
		 echo "Unable to add phone.";
	}
} else {
	//display phone listings
	render_HeaderSidebar("Open 79XX XML Directory - Manage Phone Registrations");
	output_view_phones();
	render_Footer();		
}

//
//  FUNCTIONS
//

function output_view_phones ()
{
	
	global $db;
	$xtpl=new XTemplate ("WebUI/modules/templates/view_phones.html");

	//custum order by
	if (isset($_GET['ob']))
	{
		if ($_GET['ob'] == "ob_MAC")
		{
			$ob = "MAC";
		} elseif ($_GET['ob'] == "ob_access_lvl") { 
			$ob = "access_lvl";
		} elseif ($_GET['ob'] == "ob_ln") { 
			$ob = "lname";
		} elseif ($_GET['ob'] == "ob_num") { 
			$ob = "number";
		} else {
			$ob = "MAC";
		}
	} else {
	$ob = "MAC";
	}
	
	// Content
	$theSQL = "SELECT id,MAC,access_lvl,fname,lname,number FROM phone ORDER BY $ob";
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
		$xtpl->assign("MAC",$in['MAC']);
		$xtpl->assign("access_lvl",$in['access_lvl']);
		$xtpl->assign("name",$in['lname'].", ".$in['fname']);
		$xtpl->assign("number",$in['number']);

		$xtpl->parse("main.row");
		$oddRow = !$oddRow;
	}
	
	// Output
	$xtpl->parse("main");
	$xtpl->out("main");		
}
?>