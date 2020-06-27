<?php
/*
	XML Directory - Web User Interface
	
	Joe Hopkins <joe@csma.biz>
	Copyright (c) 2005, McFadden Associates.  All rights reserved.
*/

$myPref = "primary";

if (isset($_POST['submit_cancel'])) 
{
	// Cancel
	header("Location: index.php?module=menu");

} elseif (isset($_POST['submit_save'])) {
		// Saving
		if (defang_input($_POST['ph_sec']) == "ph_sec")
		{
			$tmp_ph_sec = "Yes";
		} else {
			$tmp_ph_sec = "No";
		}
		if (defang_input($_POST['ob_sec']) == "ob_sec")
		{
			$tmp_ob_sec = "Yes";
		} else {
			$tmp_ob_sec = "No";
		}
		if (defang_input($_POST['ph_prfx']) == "ph_prfx")
		{
			$tmp_ph_prfx = "Yes";
		} else {
			$tmp_ph_prfx = "No";
		}
		
		$tmp_memo_ob = defang_input($_POST['memo_ob']);
		$tmp_prefix = defang_input($_POST['prefix']);

		$tmpUpdateSQL = "UPDATE global_pref SET
			ph_sec = '$tmp_ph_sec',
			ph_prfx = '$tmp_ph_prfx',
			prefix = '$tmp_prefix',
			memo_ob = '$tmp_memo_ob',
			ob_sec = '$tmp_ob_sec'
			WHERE preference = '$myPref'";
			
		mysql_query($tmpUpdateSQL, $db);
		header("Location: index.php?module=menu");
} else {
	output_glob_pref($myPref);
	
}

//Create page and fill in known data
function output_glob_pref($myPref)
{	
	render_HeaderSidebar("Open 79XX XML Directory - Global Preferences");
	$xtpl=new XTemplate ("WebUI/modules/templates/global_pref.html");
	$theSQL = "SELECT * FROM global_pref WHERE preference = '$myPref'";
	
	global $db;
	
	$theRES = mysql_query($theSQL, $db);

	if ($in = mysql_fetch_assoc($theRES))
	{
		if ($in['ph_sec'] == "Yes")
		{
			$xtpl->assign("ph_sec_check",'CHECKED'); //place check in box
		}
		if ($in['ob_sec'] == "Yes")
		{
			$xtpl->assign("ob_sec_check",'CHECKED'); //place check in box
		}
		if ($in['ph_prfx'] == "Yes")
		{
			$xtpl->assign("ph_prfx_check",'CHECKED'); //place check in box
		}
		if ($in['memo_ob'] == "Sender")
		{
			$xtpl->assign("selected_sender",'selected');
		} elseif ($in['memo_ob'] == "Date") {
			$xtpl->assign("selected_date",'selected');
		} else {
			$xtpl->assign("selected_Title",'selected');
		}

		$xtpl->assign("prefix",$in['prefix']); //display current prefix
	} else {
		echo "Unable to save preferences.";
	}
	
	// Output
	$xtpl->parse("main");
	$xtpl->out("main");
	render_Footer();
}

?>