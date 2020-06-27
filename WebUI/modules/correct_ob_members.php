<?php
/*
	XML Directory - Web User Interface
	
	Joe Hopkins <joe@csma.biz>
	Copyright (c) 2005, McFadden Associates.  All rights reserved.
*/

if (isset($_GET['id']))
{
	$container = defang_input($_GET['id']);
}

if (isset($_POST['submit_confirm']))
{
	if ($_POST['radiobutton'] == 'reassign')
	{
		if (isset($_POST['member_of']))
		{
			$tmp_new_ob = $_POST['member_of'];
			$tmpUpdateSQL = "UPDATE object SET
					member_of = '$tmp_new_ob'
					WHERE member_of = '$container'";
					mysql_query($tmpUpdateSQL, $db);	
			delete_object($container);
		} else {
			//error
		}
	} else {
		//radiobutton = delete
		delete_object($container);
		delete_old_object($container);
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
	$xtpl=new XTemplate ("WebUI/modules/templates/correct_ob_members.html");
	
	$theSQL = "SELECT id,type,member_of,title,href,access FROM object WHERE member_of = '$container'";
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
		$xtpl->assign("type",$in['type']);
		$xtpl->assign("member_of",$in['member_of']);
		$xtpl->assign("title",$in['title']);
		$xtpl->assign("href",$in['href']);
		$xtpl->assign("access",$in['access']);

		$xtpl->parse("main.row");
		//alternate bg color
		$oddRow = !$oddRow;
	}
	//assign the member of's to dropdown menu
	$member_of_sql = "SELECT * FROM object WHERE type = 'Container' AND id != '$container' AND member_of != '$container'";
	$member_of_sql;
	$member_of_qry = mysql_query($member_of_sql, $db);

	//assign choose container
	$xtpl->assign("container_id","1");
	$xtpl->assign("member_of","- Choose Container -");
	$xtpl->parse('main.member_of_dropdown');
	
	//assign main
	$xtpl->assign("container_id","0");
	$xtpl->assign("member_of","Main");
	$xtpl->parse('main.member_of_dropdown');
	while ($mo = mysql_fetch_assoc($member_of_qry))
	{		
		$xtpl->assign("container_id",$mo['id']);
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

function delete_old_object ($container)
{
	$sql = "DELETE FROM object WHERE member_of='$container'";
    $result = mysql_query($sql);
}
?>