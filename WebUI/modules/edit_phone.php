<?php
/*
	XML Directory - Web User Interface
	
	Joe Hopkins <joe@csma.biz>
	Copyright (c) 2005, McFadden Associates.  All rights reserved.
*/

//Checks if id is known, stores in variable
if (isset($_GET['id'])) $tmp_id = defang_input($_GET['id']);

if (isset($_POST['action']) || isset($_GET['submit_delete']))
{
	//User wants to save, cancel, or delete object
	
	$myAction = defang_input($_POST['action']);
	if ($myAction == "edit" || $_GET['submit_delete'] == yes)
	{
		if (isset($_POST['submit_save']))
		{
			// Saving
			$tmp_id = defang_input($_POST['id']);
			$tmp_MAC = defang_input($_POST['MAC']);
			$tmp_access_lvl = defang_input($_POST['access_lvl']);
			$tmp_number = defang_input($_POST['number']);
			$tmp_fname = defang_input($_POST['fname']);
			$tmp_lname = defang_input($_POST['lname']);
			
			$tmpUpdateSQL = "UPDATE phone SET
				MAC = '$tmp_MAC',
				number = '$tmp_number',
				fname = '$tmp_fname',
				lname = '$tmp_lname',
				access_lvl = '$tmp_access_lvl'
				WHERE id ='$tmp_id'";
			
			if (mysql_query($tmpUpdateSQL, $db))
			{
				header("Location: index.php?module=view_phones");
			} else {
				echo "Unable to edit phone.";
			}
					
		} else if (isset($_POST['submit_delete']) || $_GET['submit_delete'] == 'yes') {
			// Deleting
			$tmp_id = defang_input($tmp_id);
			delete_phone($tmp_id);
			header("Location: index.php?module=view_phones");

		} else if (isset($_POST['submit_cancel'])) {
			// Cancel
			if ($_GET['new'] == "true")
			{
				
				delete_phone($tmp_id);
			}
			header("Location: index.php?module=view_phones");
		} else {
			// Action, but no valid submit button.
			header("Location: index.php?module=view_phone");
		}
		
	} else {
		// Bad action
		header("Location: index.php?module=view_phone");
	}
	
} else {
	// NO action
	output_edit_phone($tmp_id);
	render_Footer();
}

function delete_phone ($tmp_id)
{
	$sql = "DELETE FROM phone WHERE id='$tmp_id'";
    $result = mysql_query($sql);
}			

//Create page and fill in known data
function output_edit_phone ($myId)
{
	render_HeaderSidebar("Open 79XX XML Directory - Edit Phone Registrations");
	
	global $db;
	$xtpl=new XTemplate ("WebUI/modules/templates/edit_phone.html");

	$theSQL = "SELECT * FROM phone WHERE id='$myId'";

	$theRES = mysql_query($theSQL, $db);

	if ($in = mysql_fetch_assoc($theRES))
	{
		$xtpl->assign("id",$in['id']);
		$xtpl->assign("MAC",$in['MAC']);
		if ($in['access_lvl'] == "Restricted")
		{
			$xtpl->assign("selected_restricted","selected");
			$xtpl->assign("selected_unrestricted","");
			$xtpl->assign("selected_unknown","");
		} else if ($in['access_lvl'] == "Unrestricted"){
			$xtpl->assign("selected_restricted","");
			$xtpl->assign("selected_unrestricted","selected");
			$xtpl->assign("selected_unknown","");
		} else {
			//unknown is selected
			$xtpl->assign("selected_restricted","");
			$xtpl->assign("selected_unrestricted","");
			$xtpl->assign("selected_unknown","selected");
		}
		$xtpl->assign("number",$in['number']);
		$xtpl->assign("fname",$in['fname']);
		$xtpl->assign("lname",$in['lname']);
		
	}	
	// Output
	$xtpl->parse("main");
	$xtpl->out("main");		
}


?>