<?php
/*
	XML Directory - Web User Interface
	
	Joe Hopkins <joe@csma.biz>
	Copyright (c) 2005, McFadden Associates.  All rights reserved.
*/

//Checks if id is known, stores in variable
if (isset($_GET['id']))
{
	$tmp_id = defang_input($_GET['id']);
}

if (isset($_POST['action']) || isset($_GET['submit_delete']))
{
	//User wants submit or cancel
	
	$myAction = defang_input($_POST['action']);
	if ($myAction == "edit" || $_GET['submit_delete'] == yes)
	{
		if (isset($_POST['submit_create']))
		{
			// Saving
			$tmp_id = defang_input($_POST['id']);
			$tmp_type = defang_input($_POST['type']);
			
			
			$tmpUpdateSQL = "UPDATE object SET
				type = '$tmp_type'		
				WHERE id ='$tmp_id'";
				
				mysql_query($tmpUpdateSQL, $db);
				header("Location: index.php?module=edit_object&id=$tmp_id&new=true");		
				
		} else if (isset($_POST['submit_cancel'])) {
			// Cancel
			if ($tmp_id != '0') //prevent user from deleting main container
				{
					delete_object($tmp_id);
					header("Location: index.php?module=view_objects");
				} else {
					header("Location: index.php?module=delete_error");
				}
		
		} else if (isset($_POST['submit_delete']) || $_GET['submit_delete'] == 'yes') {
			// Deleting
			$tmp_id = defang_input($tmp_id);
			
			if ($tmp_id != '0') //prevent user from deleting main container
				{
					delete_object($tmp_id);
					header("Location: index.php?module=view_objects&mbr_of=".$_GET['mbr_of']."&drop_type=".$_GET['drop_type']);
				} else {
					header("Location: index.php?module=delete_error");
				}
			
		} else {
			// Action, but no valid submit button.
			header("Location: index.php?module=view_objects");
		}
	} else {
		// Bad action
		header("Location: index.php?module=view_objects");
	}
} else {
	// No action
	render_HeaderSidebar("Open 79XX XML Directory - Editing");
	output_edit_type($tmp_id);
	render_Footer();
}	

//
//  FUNCTIONS
//

function delete_object ($tmp_id)
{
	$sql = "DELETE FROM object WHERE id='$tmp_id'";
    $result = mysql_query($sql);
}		

//Create page and fill in known data
function output_edit_type ($myId)
{
	global $db;
	$xtpl=new XTemplate ("WebUI/modules/templates/object_type.html");

	$theSQL = "SELECT * FROM object WHERE id='$myId'";

	$theRES = mysql_query($theSQL, $db);

	if ($in = mysql_fetch_assoc($theRES))
	{
		$xtpl->assign("id",$in['id']);
		$xtpl->assign("type",$in['type']);
	}	
	// Output
	$xtpl->parse("main");
	$xtpl->out("main");		
}


?>