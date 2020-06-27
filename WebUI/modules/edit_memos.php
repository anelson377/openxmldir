<?php
/*
	XML Directory - Web User Interface
	
	Joe Hopkins <joe@csma.biz>
	Copyright (c) 2005, McFadden Associates.  All rights reserved.
*/

//Checks if id is known, stores in variable
if (try_get('id'))
{
	$tmp_id = defang_input(try_get('id'));
}

if (isset($_POST['action']) || try_get('submit_delete') == yes)
{
	//User wants to save, cancel, or delete memo
	$myAction = defang_input($_POST['action']);
	if ($myAction == "edit" || try_get('submit_delete') == yes)
	{
		if (isset($_POST['submit_save']))
		{
			// Saving
			$tmp_id = defang_input($_POST['id']);
			$tmp_title = defang_input($_POST['title']);
			$tmp_access = defang_input($_POST['access']);
			$tmp_msg = defang_input($_POST['msg']);
			
			$tmpUpdateSQL = "UPDATE memos SET
				title = '$tmp_title',
				msg = '$tmp_msg',
				access = '$tmp_access'		
				WHERE id ='$tmp_id'";
				
			if (mysql_query($tmpUpdateSQL, $db))
			{
				header("Location: index.php?module=view_memos");
			} else {
				echo "Unable to save memo.";
			}
										
		} else if (isset($_POST['submit_cancel'])) {
			// Cancel
			if (try_get('new') == 'true')
			{
				delete_memo($tmp_id);
			}	
			header("Location: index.php?module=view_memos");
		
		} else if (isset($_POST['submit_delete']) || try_get('submit_delete') == 'yes') {
				// Deleting
				if ($tmp_id != '0') //prevent user from deleting main container
				{
					delete_memo($tmp_id);
					header("Location: index.php?module=view_memos");
				} else {
					header("Location: index.php?module=delete_error");
				}
		} else {
			// Action, but no valid submit button.
			header("Location: index.php?module=view_memos");
		}
	} else {
		// Bad action
		header("Location: index.php?module=view_memos");
	}
} else {
	// No action
	render_HeaderSidebar("Open 79XX XML Directory - Edit Memo");
	output_edit_memo($tmp_id);
	render_Footer();
}

//
//  FUNCTIONS
//

function delete_memo ($tmp_id)
{
	$sql = "DELETE FROM memos WHERE id='$tmp_id'";
    $result = mysql_query($sql);
}		

//Create page and fill in known data
function output_edit_memo ($myId)
{
	global $db;
	$xtpl=new XTemplate ("WebUI/modules/templates/edit_memos.html");

	$theSQL = "SELECT * FROM memos WHERE id='$myId'";

	$theRES = mysql_query($theSQL, $db);

	if ($in = mysql_fetch_assoc($theRES))
	{
		$tmp_unixtime = $in['date'];
		$displaydate = date("l, F d, Y h:i" ,$tmp_unixtime);
		
		$xtpl->assign("id",$in['id']);
		$xtpl->assign("date",$displaydate);
		$xtpl->assign("title",$in['title']);
		$xtpl->assign("msg",$in['msg']);
		$xtpl->assign("from",$in['sender']);
		
		if ($in['access'] == "Private")
		{
			$xtpl->assign("access","Private");
			$xtpl->assign("var_access","Public");
			
		} else {
			$xtpl->assign("access","Public");
			$xtpl->assign("var_access","Private");
		}
	}	
	// Output
	$xtpl->parse("main");
	$xtpl->out("main");		
}
?>
