<?php
/*
	XML Directory - Web User Interface
	
	Joe Hopkins <joe@csma.biz>
	Copyright (c) 2005, McFadden Associates.  All rights reserved.
*/


$xtpl=new XTemplate ("WebUI/modules/templates/edit_contact.html");

//Checks if id is known, stores in variable
if (try_get('id'))
	$tmp_id = defang_input(try_get('id'));


if (isset($_POST['action']) || try_get('submit_delete'))
{
	//User wants to save, cancel, or delete object
	
	$myAction = defang_input($_POST['action']);
	if ($myAction == "edit" || try_get('submit_delete') == 'yes')
	{
		if (isset($_POST['submit_save']))
		{
			// Saving
			$tmp_id = defang_input($_POST['id']);
			$tmp_member_of = defang_input($_POST['member_of']);
			$tmp_lname = defang_input($_POST['lname']);
			$tmp_fname = defang_input($_POST['fname']);
			$tmp_company = defang_input($_POST['company']);
			$tmp_title = defang_input($_POST['title']);
			$tmp_office_phone = defang_input($_POST['office_phone']);
			$tmp_home_phone = defang_input($_POST['home_phone']);
			$tmp_custom_phone = defang_input($_POST['custom_phone']);
			$tmp_custom_number = defang_input($_POST['custom_number']);
			$tmp_cell_phone = defang_input($_POST['cell_phone']);
			$tmp_other_phone = defang_input($_POST['other_phone']);
			$tmp_owner = defang_input($_POST['owner']);
			$tmp_speed_dial = defang_input($_POST['speed_dial']);
			
			if (defang_input($_POST['sup_prefix_office']) == "sup_prefix_office")
			{
				$tmp_sup_prefix_office = "1";
			} else {
				$tmp_sup_prefix_office = "0";
			}
			if (defang_input($_POST['sup_prefix_home']) == "sup_prefix_home")
			{
				$tmp_sup_prefix_home = "1";
			} else {
				$tmp_sup_prefix_home = "0";
			}
			if (defang_input($_POST['sup_prefix_cell']) == "sup_prefix_cell")
			{
				$tmp_sup_prefix_cell = "1";
			} else {
				$tmp_sup_prefix_cell = "0";
			}
			if (defang_input($_POST['sup_prefix_other']) == "sup_prefix_other")
			{
				$tmp_sup_prefix_other = "1";
			} else {
				$tmp_sup_prefix_other = "0";
			}
			if (defang_input($_POST['sup_prefix_custom']) == "sup_prefix_custom")
			{
				$tmp_sup_prefix_custom = "1";
			} else {
				$tmp_sup_prefix_custom = "0";
			}
			
			
			//Create clean name for display_name column in contacts table.
			//This is the name used to order and display the contacts on the phone UI
			if ($tmp_lname != '' || $tmp_fname != '')
			{
				if ($tmp_lname != '' && $tmp_fname != '')
				{
					$tmpTitle = $tmp_lname.", ".$tmp_fname;
				} else {
					$tmpTitle = $tmp_lname.$tmp_fname;
				}
				if ($tmp_company != '')
				{
					$tmpTitle = $tmpTitle.' - '.$tmp_company;
				}
			} elseif ($tmp_company != '') {
				//lname,fname is not specified, display company
				$tmpTitle = $tmp_company;
			} else {
				$tmpTitle = $tmp_company;
			}
			
			$tmpUpdateSQL = "UPDATE contacts SET
				member_of='$tmp_member_of',
				display_name= '$tmpTitle',
				fname='$tmp_fname',
				lname='$tmp_lname',
				company='$tmp_company',
				title='$tmp_title',
				office_phone='$tmp_office_phone',
				home_phone='$tmp_home_phone',
				custom_phone='$tmp_custom_phone',
				custom_number='$tmp_custom_number',
				cell_phone='$tmp_cell_phone',
				owner='$tmp_owner',
				speed_dial='$tmp_speed_dial',
				sup_prefix_office='$tmp_sup_prefix_office',
				sup_prefix_home='$tmp_sup_prefix_home',
				sup_prefix_cell='$tmp_sup_prefix_cell',
				sup_prefix_other='$tmp_sup_prefix_other',
				sup_prefix_custom='$tmp_sup_prefix_custom',
				other_phone='$tmp_other_phone'
				WHERE id='$tmp_id'";
				
				mysql_query($tmpUpdateSQL, $db);
					
			header("Location: index.php?module=view_contacts");		
			
		} else if (isset($_POST['submit_delete']) || try_get('submit_delete') == 'yes') {
			// Deleting
			$tmp_id = defang_input($tmp_id);
			delete_contact($tmp_id);
			header("Location: index.php?module=view_contacts&mbr_of=".try_get('mbr_of'));
			
		} else if (isset($_POST['submit_cancel'])) {
			// Cancel
			if (try_get('new') == "true")
			{
				delete_contact($tmp_id);
			}
			//delete_contact($tmp_id);
			header("Location: index.php?module=view_contacts");
			
		} else {
			// Action, but no valid submit button.
			header("Location: index.php?module=edit_contact");
		}
	} else {
		// Bad action
		header("Location: index.php?module=edit_contact");
	}
	
} else {
	// No action
	render_HeaderSidebar("Open 79XX XML Directory - Editing");
	output_edit_contact($tmp_id);
	render_Footer();
}

function delete_contact ($tmp_id)
{
	$sql = "DELETE FROM contacts WHERE id='$tmp_id'";
    $result = mysql_query($sql);
}			

//Create page and fill in known data
function output_edit_contact ($myId)
{
	global $db, $xtpl;
	
	//look up global prefix
	$theSQL = "SELECT prefix FROM global_pref";
	$theRES = mysql_query($theSQL, $db);
	if ($in = mysql_fetch_assoc($theRES))
		$xtpl->assign("prefix",$in['prefix']);


	//look up info specific to contact
	$theSQL = "SELECT * FROM contacts WHERE id='$myId'";
	$theRES = mysql_query($theSQL, $db);
	if ($in = mysql_fetch_assoc($theRES))
	{
		$xtpl->assign("id",$in['id']);
		$xtpl->assign("current_member_of",$in['member_of']);
		$xtpl->assign("lname",$in['lname']);
		$xtpl->assign("fname",$in['fname']);
		$xtpl->assign("company",$in['company']);
		$xtpl->assign("title",$in['title']);
		$xtpl->assign("office_phone",$in['office_phone']);
		$xtpl->assign("home_phone",$in['home_phone']);
		$xtpl->assign("speed_dial",$in['speed_dial']);
		
		if ($in['custom_phone'] != '')
		{
			$xtpl->assign("custom_phone",$in['custom_phone']);
		} else {
			$xtpl->assign("custom_phone","Create Custom");
		}
		if ($in['sup_prefix_office'] == "1")
		{
			$xtpl->assign("sup_prefix_office_check",'CHECKED'); //place check in box
		}
		if ($in['sup_prefix_home'] == "1")
		{
			$xtpl->assign("sup_prefix_home_check",'CHECKED'); //place check in box
		}
		if ($in['sup_prefix_cell'] == "1")
		{
			$xtpl->assign("sup_prefix_cell_check",'CHECKED'); //place check in box
		}
		if ($in['sup_prefix_other'] == "1")
		{
			$xtpl->assign("sup_prefix_other_check",'CHECKED'); //place check in box
		}
		if ($in['sup_prefix_custom'] == "1")
		{
			$xtpl->assign("sup_prefix_custom_check",'CHECKED'); //place check in box
		}
		
		$xtpl->assign("custom_number",$in['custom_number']);
		$xtpl->assign("cell_phone",$in['cell_phone']);
		$xtpl->assign("other_phone",$in['other_phone']);
		
		if ($_SESSION['account_type'] == 'Admin')
		{
			//user is an admin, show dropdown to change owner
			$theSQL = "SELECT username, id FROM users";
			$theRES = mysql_query($theSQL, $db);
			
			$xtpl->assign("user_id","");
			$xtpl->assign("username","Anonymous");
			$xtpl->parse('main.cat_exist.own_edit.owner_dropdown');
			while ($drp = mysql_fetch_assoc($theRES))
			{
				$xtpl->assign("user_id",$drp['id']);
				$xtpl->assign("username",$drp['username']);
				if ($in['owner'] == $drp['id'])
				{
					$xtpl->assign("selected","selected");
				} else {
					$xtpl->assign("selected","");
				}
				$xtpl->parse('main.cat_exist.own_edit.owner_dropdown');
			}
			
			$xtpl->parse('main.cat_exist.own_edit');
		} else {
			//display owner of current contact
			$tmp_owner = $in['owner'];
			$theSQL = "SELECT username FROM users WHERE id='$tmp_owner'";
			$theRES = mysql_query($theSQL, $db);
			if ($in2 = mysql_fetch_assoc($theRES))
			{
				$tmp_owner_name = $in2['username'];
			} else {
				$tmp_owner_name = "Owner not found.";
			}
			$xtpl->assign("owner",$tmp_owner_name);
			$xtpl->parse('main.cat_exist.own_noedit');
			
		}

		$xtpl->assign("date",$in['date']);
		
		if ($in['style'] == "Seperate")
		{
			$xtpl->assign("style", "Seperate");
			$xtpl->assign("var_style","Together");
		} else {
			$xtpl->assign("var_style", "Seperate");
			$xtpl->assign("style","Together");
		}
		
		//$member_of_ttl = $in['member_of'];
		dropdown_menu(0,0,$in['member_of']);
			
	$xtpl->parse('main.cat_exist');
	$xtpl->parse('main');
	$xtpl->out("main");
	}
}

function dropdown_menu($member_of, $indent,$in_member)
{
	global $db, $xtpl;

	//Assign containers to dropdown
	$conQRY = "SELECT * FROM object WHERE member_of = '$member_of' ORDER BY 'title'";
	$conRESULT = mysql_query($conQRY, $db);
	
	if($member_of == '0' && $indent == '0')
	{
		//this is the first parse of dropdown assign label
		$xtpl->assign("member_of","- Choose Contact Holder -");
		$xtpl->assign("category_id","0");
		$xtpl->assign("selected","");
		$xtpl->parse('main.cat_exist.member_of_dropdown');
	}
		
	
	while ($mo2 = mysql_fetch_assoc($conRESULT))
	{
		
		
		if ($mo2['type'] == "Category" || $mo2['type'] == "Container")
		{

			//dropdowns
			$x = 0;
			while ($x < $indent)
			{
				$xtpl->assign("spacer","../");
				$xtpl->parse("main.cat_exist.member_of_dropdown.spacer");
				$x++;
			}
			$xtpl->assign("member_of",$mo2['title']);
			
			if ($mo2['type'] == "Container")
			{
				$xtpl->assign("category_id",'error');
				$xtpl->assign("color",'#9F9F9F');
				//$xtpl->assign("bgcolor",'#FFFFFF');
			
			} else { //object is a category
				$xtpl->assign("category_id",$mo2['id']);
				$xtpl->assign("color",'#000000');
				//$xtpl->assign("bgcolor",'#999999');
			}
			if ($mo2['id'] == $in_member)
			{
				$xtpl->assign("selected","selected");
			}
			
			$xtpl->parse("main.cat_exist.member_of_dropdown");
			$xtpl->assign("selected","");
			//$xtpl->out("main.member_of_dropdown");
		}
		dropdown_menu($mo2['id'], $indent+1,$in_member);
	}
}
?>
