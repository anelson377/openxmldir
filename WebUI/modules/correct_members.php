<?php
/*
	XML Directory - Web User Interface
	
	Joe Hopkins <joe@csma.biz>
	Copyright (c) 2005, McFadden Associates.  All rights reserved.
*/

if (isset($_GET['id']))
{
	$categoryid = defang_input($_GET['id']);
}

if (isset($_POST['submit_confirm']))
{
	if ($_POST['radiobutton'] == 'reassign')
	{
		if (isset($_POST['member_of']))
		{
			$tmp_new_cat = $_POST['member_of'];
			$tmpUpdateSQL = "UPDATE contacts SET
					member_of = '$tmp_new_cat'
					WHERE member_of = '$categoryid'";
					mysql_query($tmpUpdateSQL, $db);	
			delete_object($categoryid);
		} else {
			//error
		}
	} else {
		//radiobutton = delete
		delete_object($categoryid);
		delete_old_contacts($categoryid);
	}
	//return to object listing
	header("Location: index.php?module=view_objects");
} elseif (isset($_POST['submit_cancel'])) {
	//return without performing actions
	header("Location: index.php?module=view_objects");
}

render_HeaderSidebar("Open 79XX XML Directory - Warning");

//Checks if id is known, stores in variable
if (isset($_GET['id']))
{
	$xtpl=new XTemplate ("WebUI/modules/templates/correct_members.html");
	
	$theSQL= "SELECT * FROM contacts WHERE member_of ='$categoryid'";
	
	$qry = mysql_query($theSQL, $db);
	
	while ($in = mysql_fetch_assoc($qry))
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
		

		$xtpl->parse("main.row");
		$oddRow = !$oddRow;
	}
	//assign the member of's to dropdown menu
	$member_of_sql = "SELECT * FROM `object` WHERE type = 'Category' AND id != '$categoryid'";
	$member_of_qry = mysql_query($member_of_sql, $db);

	$xtpl->assign("category_id","1");
	$xtpl->assign("member_of","- Choose Contact Holder -");
	$xtpl->parse('main.member_of_dropdown');
	while ($mo = mysql_fetch_assoc($member_of_qry))
	{		
		$xtpl->assign("category_id",$mo['id']);
		$xtpl->assign("member_of",$mo['title']);	
		$xtpl->parse('main.member_of_dropdown');
	}
}

// Output
$xtpl->parse("main");
$xtpl->out("main");		
render_Footer();
//
//  FUNCTIONS
//

function delete_object ($tmp_id)
{
	$sql = "DELETE FROM object WHERE id='$tmp_id'";
    $result = mysql_query($sql);
}

function delete_old_contacts ($categoryid)
{
	$sql = "DELETE FROM contacts WHERE member_of='$categoryid'";
    $result = mysql_query($sql);
}
?>