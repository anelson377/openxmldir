<?php
/*
	XML Directory - Web User Interface
	
	Joe Hopkins <joe@csma.biz>
	Copyright (c) 2005, McFadden Associates.  All rights reserved.
*/

$xtpl=new XTemplate ("WebUI/modules/templates/view_contacts.html");

if (isset($_POST['submit_add']))
{
	// add contact
	$tmp_id = create_guid($tmp_id);
	$tmp_owner = $_SESSION['user_id'];
	$tmpInitSQL = "INSERT INTO contacts (id,owner) VALUES ('$tmp_id','$tmp_owner')";
	if ($tmpInitRES = mysql_query($tmpInitSQL, $db))
	{
		// show editor
		header("Location: index.php?module=edit_contact&id=$tmp_id&new=true");
	} else {
		 // Failure
		 echo "Unable to create contact";
	}
} elseif (isset($_POST['submit_import'])) {
		header("Location: index.php?module=import_contacts");
	
} else {
	//display contacts
	render_HeaderSidebar("Open 79XX XML Directory - Manage Contacts");
	output_view_contacts();
	render_Footer();		
}

//
//  FUNCTIONS
//

function output_view_contacts ()
{
	global $db, $xtpl;
	

	
	//Assign categories to dropdown
	$member_of_sql = "SELECT * FROM object WHERE type = 'category' ORDER BY object.title";
	$chk = mysql_query($member_of_sql, $db);
	$member_of_qry = mysql_query($member_of_sql, $db);
	
	
	if (isset($_POST['member_of']))
	{
		$in_member = defang_input($_POST['member_of']);
	} else {
		$in_member = defang_input($_GET['mbr_of']);
	}
	
	
	$xtpl->assign("member_of","- Show All -");	
	$xtpl->parse('main.member_of_dropdown');
	
	dropdown_menu(0,0,$in_member);
	
	if ($in_member != '' && $in_member != '- Show All -')
	{ 
		$tmp_sql_view = "WHERE member_of = "."'".$in_member."'";
	}

	
	//custum order by
	if (isset($_GET['ob']))
	{
		if ($_GET['ob'] == "ob_ln")
		{
			$ob = "lname";
		} elseif ($_GET['ob'] == "ob_title") { 
			$ob = "title";
		} elseif ($_GET['ob'] == "ob_company") { 
			$ob = "company";
		}
	} else {
	$ob = "lname";
	}
	
	if (isset($_POST['member_of']) || isset($_GET['ob']) || isset($_GET['mbr_of']))
	{
		//user has hit a search
		if (isset($_POST['member_of']))
		{
			$xtpl->assign("mbr_of",$_POST['member_of']);
		} elseif (isset($_GET['mbr_of'])) {
			$xtpl->assign("mbr_of",$_GET['mbr_of']);
		}
		$xtpl->parse("main.column");//show columns
		//user has submited a search, show the contacts
		$theSQL = "SELECT id,fname,lname,company,title,member_of FROM contacts $tmp_sql_view ORDER BY $ob";
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
			$xtpl->assign("fname",$in['fname']);
			$xtpl->assign("lname",$in['lname']);
			$xtpl->assign("company",$in['company']);
			$xtpl->assign("title",$in['title']);
			$xtpl->assign("office_phone",$in['office_phone']);
			$xtpl->assign("home_phone",$in['home_phone']);
			$xtpl->assign("cell_phone",$in['cell_phone']);
			$xtpl->assign("other_phone",$in['other_phone']);
			
			$parent_id = $in['member_of'];
			$parent_sql = "SELECT object.title as title, object.id AS id FROM object WHERE object.id = '$parent_id'";
			
			$parent_result = mysql_query($parent_sql, $db);
			if ($p = mysql_fetch_assoc($parent_result))
			{
				$xtpl->assign("category",$p['title']);
				$xtpl->assign("cat_id",$p['id']);	
			} else {
				//sql error
			}
			$xtpl->parse("main.row");
			$oddRow = !$oddRow;

			if ($in['fname'] == '' && $in['lname'] == '' && $in['company'] == '' && $in['title'] == '' && $in['office_phone'] == '' && $in['home_phone'] == '' && $in['cell_phone'] == '' && $in['cell_phone'] == '' && $in['other_phone'] == '')
			{
				//contacts has no information, delete the entry
				$tmp_delete_id = $in['id'];
				$sql = "DELETE FROM contacts WHERE id='$tmp_delete_id'";
				$result = mysql_query($sql);
			}
		}
	} else {
		//User has not hit search
		//show some breaks for whitespace
		$xtpl->parse("main.breaks");
	}
	
	// Output
	$xtpl->parse("main");
	$xtpl->out("main");		
}
function dropdown_menu($member_of, $indent,$in_member)
{
	/*
		This function selects the containers and categories from the database
		and places them in the dropdown menu.  The containers are shown just for
		reference as to where each category is.  The categories are colored grey.
	*/
	
	global $db, $xtpl;

	//Assign containers to dropdown
	$conQRY = "SELECT * FROM object WHERE member_of = '$member_of' ORDER BY 'title'";
	$conRESULT = mysql_query($conQRY, $db);
	
	while ($mo2 = mysql_fetch_assoc($conRESULT))
	{
		if ($mo2['type'] == "Category" || $mo2['type'] == "Container")
		{
			//Assign and parse each dropdown item
			
			$x = 0;
			while ($x < $indent)
			{
				$xtpl->assign("spacer","../");
				$xtpl->parse("main.member_of_dropdown.spacer");
				$xtpl->parse("main.row.member_of_list.spacer");
				$x++;
			}
			$xtpl->assign("member_of",$mo2['title']);
			
			if ($mo2['type'] == "Container")
			{
				$xtpl->assign("category_id",'error');
				$xtpl->assign("color",'#9F9F9F'); //color it grey
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
			
			$xtpl->parse("main.member_of_dropdown");
			$xtpl->parse("main.row.member_of_list");
			$xtpl->assign("selected","");
		}
		dropdown_menu($mo2['id'], $indent+1,$in_member);
	}
}
?>