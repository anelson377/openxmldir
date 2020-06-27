<?php
/*
	XML Directory - Web User Interface
	
	Joe Hopkins <joe@csma.biz>
	Copyright (c) 2005, McFadden Associates.  All rights reserved.
*/


//Checks if id is known, stores in variable
$xtpl=new XTemplate ("WebUI/modules/templates/edit_object.html");
if (isset($_GET['id']))
{
	$tmp_id = defang_input($_GET['id']);
	
	$type_chk_query = "SELECT type FROM `object` WHERE id ='$tmp_id'";
	
	$current_type_qry = mysql_query($type_chk_query, $db);
	
	if ($tp = mysql_fetch_assoc($current_type_qry))
	{
		$current_type = $tp['type'];
	} else {
		//Could not select object
		header("Location: index.php?module=view_objects");
	}
}
if (isset($_POST['action']) || $_GET['submit_delete'] == 'yes')
{
	//User wants to save, cancel, or delete object
	$myAction = defang_input($_POST['action']);
	if ($myAction == "edit" || $_GET['submit_delete'] == 'yes')
	{
		if (isset($_POST['submit_save']))
		{
			if ($current_type == 'Link')
			{
				// Saving
				$tmp_id = defang_input($_POST['id']);
				$tmp_title = defang_input($_POST['title']);
				$member_of_container = defang_input($_POST['member_of']);
				$tmp_href = defang_input($_POST['href']);
				$tmp_access = defang_input($_POST['access']);
				
				$tmpUpdateSQL = "UPDATE object SET
					title = '$tmp_title',
					member_of = '$member_of_container',
					href = '$tmp_href',
					access = '$tmp_access'		
					WHERE id ='$tmp_id'";
					
					mysql_query($tmpUpdateSQL, $db);
					header("Location: index.php?module=view_objects");
				
			} elseif ($current_type == 'Category') {
				
				// Saving
				$tmp_id = defang_input($_POST['id']);
				$tmp_title = defang_input($_POST['title']);
				$member_of_container = defang_input($_POST['member_of']);
				$tmp_access = defang_input($_POST['access']);
				$tmp_style = defang_input($_POST['style']);
				
				$tmpUpdateSQL = "UPDATE object SET
					title = '$tmp_title',
					style = '$tmp_style',
					member_of = '$member_of_container',
					access = '$tmp_access'		
					WHERE id ='$tmp_id'";
					
					mysql_query($tmpUpdateSQL, $db);
					header("Location: index.php?module=view_objects");
					
			} elseif ($current_type == 'Container') {
				
				// Saving
				$tmp_id = defang_input($_POST['id']);
				$tmp_title = defang_input($_POST['title']);
				$member_of_container = defang_input($_POST['member_of']);
				$tmp_access = defang_input($_POST['access']);
				
				$tmpUpdateSQL = "UPDATE object SET
					title = '$tmp_title',
					member_of = '$member_of_container',
					access = '$tmp_access'		
					WHERE id ='$tmp_id'";
					
					mysql_query($tmpUpdateSQL, $db);
					header("Location: index.php?module=view_objects");
			
			} else {
				//No type specified, direct to speficication page
				header("Location: index.php?module=object_type");
			}			
		} else if (isset($_POST['submit_cancel'])) {
			// Cancel
			if ($_GET['new'] == "true")
			{
				delete_object($tmp_id);
			}	
			header("Location: index.php?module=view_objects");
		
		} else if (isset($_POST['submit_delete']) || $_GET['submit_delete'] == 'yes') {
			// Deleting
			$tmp_id = defang_input($tmp_id);
			
			carefull_delete($tmp_id,$current_type);
		} else {
			// Action, but no valid submit button.
			header("Location: index.php?module=edit_object");
		}
	} else {
		// Bad action
		header("Location: index.php?module=edit_object");
	}
} else {
	// No action
	render_HeaderSidebar("Open 79XX XML Directory - Editing");
	output_edit_object($tmp_id, $current_type);
	render_Footer();
}

//
//  FUNCTIONS
//

function delete_object ($tmp_id)
{
	global $db;
	$sql = "DELETE FROM object WHERE id='$tmp_id'";
    $result = mysql_query($sql);
}

function carefull_delete ($tmp_id,$current_type)
{
	/*
		user wants to delete object
		
		if container, check to make sure there are no objects that will be lost beacuse of delettion.
		if there are objects, go to correct members page to deal witht hem
		
		if category, do same thing but witht he contacts	
	*/
	global $db;
	
	
	if ($current_type == "Link")
	{
		delete_object($tmp_id);
		header("Location: index.php?module=view_objects&mbr_of=".$_GET['mbr_of']."&drop_type=".$_GET['drop_type']);
	
	} elseif ($current_type == "Category"){
	
		$count = "SELECT count(contacts.id) as total FROM contacts WHERE member_of='$tmp_id'";
		$module = "correct_members";//go to page to fix contacts that were member of category
	} else {
		//object is a container
		$count = "SELECT count(object.id) as total FROM object WHERE member_of='$tmp_id'";
		$module = "correct_ob_members";//go to page to fix objects that were member of container
	}
	//echo $count;
	$countRES = mysql_query($count, $db);

	if ($ct = mysql_fetch_assoc($countRES))
	{
		if ($ct['total'] > '0')
		{
			header("Location: index.php?module=$module&id=$tmp_id");
		} else {
			//no contacts are in this category
			if ($tmp_id != '0') 
			{
				delete_object($tmp_id);
				header("Location: index.php?module=view_objects&mbr_of=".$_GET['mbr_of']."&drop_type=".$_GET['drop_type']);
			} else {
				//prevent user from deleting main container
				header("Location: index.php?module=delete_error");
			}
		}
	} else {
		delete_object($tmp_id);
		header("Location: index.php?module=view_objects&mbr_of=".$_GET['mbr_of']."&drop_type=".$_GET['drop_type']);
	}
}


function output_edit_object ($myId, $current_type)
{
	/*
		//Create page and fill in known data
	
	*/
	global $db, $xtpl;
	//$xtpl=new XTemplate ("WebUI/modules/templates/edit_object.html");

	$theSQL = "SELECT * FROM object WHERE id='$myId'";

	$theRES = mysql_query($theSQL, $db);

	if ($in = mysql_fetch_assoc($theRES))
	{
		
		$xtpl->assign("id",$in['id']);
		if ($in['type'] == "Category")
		{
			$xtpl->assign("type","Contact Holder");
		} else {
			$xtpl->assign("type",$in['type']);
		}
		
		$xtpl->assign("title",$in['title']);
		
		
		if ($in['access'] == "Private")
		{
			$xtpl->assign("access", "Private");
			$xtpl->assign("var_access","Public");
			
		} else {
			$xtpl->assign("access","Public");
			$xtpl->assign("var_access","Private");
		}
		
		$member_of_container = $in['member_of']; //this is the id of the object's container
		
		//load containers into dropdown menu
		$xtpl->assign("member_of","Main");
		$xtpl->assign("container_id",'0');
		$xtpl->parse("main.member_of_dropdown");
		dropdown_menu(0,0,$member_of_container);
			
		if ($current_type == 'Link')
		{		
			$xtpl->assign("href",$in['href']);
			$xtpl->parse('main.link');
			$xtpl->parse('main.link_java');

		} elseif ($current_type == 'Category') {
			
			$xtpl->parse('main.cat_java');
			
			if ($in['style'] == "Together")
			{
				$xtpl->assign("style", "Together");
				$xtpl->assign("var_style","Seperate");
			} else {
				$xtpl->assign("var_style", "Together");
				$xtpl->assign("style","Seperate");
		}
			$xtpl->parse('main.style');
		
		}
	}	
	// Output
	$xtpl->parse("main");
	$xtpl->out("main");		
}
function dropdown_menu($member_of, $indent,$member_of_container)
{
	global $db, $xtpl;

	//Assign containers to dropdown
	$conQRY = "SELECT * FROM object WHERE member_of = '$member_of' ORDER BY 'title'";
	$conRESULT = mysql_query($conQRY, $db);
	
	while ($mo2 = mysql_fetch_assoc($conRESULT))
	{
		if ($mo2['type'] == "Container")
		{
			$xtpl->assign("spacer","../");
			$xtpl->parse("main.member_of_dropdown.spacer");
			//dropdowns
			$x = 0;
			while ($x < $indent)
			{
				$xtpl->assign("spacer","../");
				$xtpl->parse("main.member_of_dropdown.spacer");
				$x++;
			}
			$xtpl->assign("member_of",$mo2['title']);
			$xtpl->assign("container_id",$mo2['id']);
			if ($mo2['id'] == $member_of_container)
			{
				$xtpl->assign("selected","selected");
			}
			
			$xtpl->parse("main.member_of_dropdown");
			$xtpl->assign("selected","");
			//$xtpl->out("main.member_of_dropdown");
		}
		dropdown_menu($mo2['id'], $indent+1,$member_of_container);
	}
}
?>