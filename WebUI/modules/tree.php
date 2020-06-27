
<?php

/*
	XML Directory - Web User Interface
	
	Joe Hopkins <joe@csma.biz>
	Copyright (c) 2005, McFadden Associates.  All rights reserved.
*/





if($_GET['view'] == "print")
{
	$xtpl=new XTemplate ("WebUI/modules/templates/tree_print.html");
	$member_of_sql = "SELECT * FROM object WHERE member_of = '0' ORDER BY 'title'";
	$member_of_qry = mysql_query($member_of_sql, $db);
	output_tree(0,0);
} else {
	render_HeaderSidebar("Open 79XX XML Directory - User Edit");
	$xtpl=new XTemplate ("WebUI/modules/templates/tree.html");
	
	$member_of_sql = "SELECT * FROM object WHERE member_of = '0' ORDER BY 'title'";
	$member_of_qry = mysql_query($member_of_sql, $db);
	
	//output header
	$xtpl->parse("header");
	$xtpl->out("header");
	
	
	output_tree(0,0);
	
	//end table
	$xtpl->parse("end");
	$xtpl->out("end");
	
	render_Footer();
}

//
// FUNCTIONS
//
//

function output_tree($member_of,$indent)
{
	global $db, $xtpl;
	
	$member_of_sql2 = "SELECT * FROM object WHERE member_of = '$member_of' ORDER BY 'title'";
	$member_of_qry2 = mysql_query($member_of_sql2, $db);
	
	while ($mo2 = mysql_fetch_assoc($member_of_qry2))
	{
		$x=0;
		$xtpl->assign("spacer","");
				$xtpl->parse("spacer_blank");
				$xtpl->out("spacer_blank");
				$xtpl->reset("spacer_blank");
		while ($x < $indent)
		{
			if ($x +1 == $indent)
			{
				$xtpl->assign("spacer","");
				$xtpl->parse("spacer");
				$xtpl->out("spacer");
				$xtpl->reset("spacer");
				$x++;
			} else {
				$xtpl->assign("spacer","");
				$xtpl->parse("spacer_blank");
				$xtpl->out("spacer_blank");
				$xtpl->reset("spacer_blank");
				$x++;
			}
		}
		if ($mo2['type'] == "Link")
		{
			$xtpl->assign("title",$mo2['title']);
			$xtpl->assign("id",$mo2['id']);
			if($mo2['access'] == 'Private')
			{
				$xtpl->assign("access_lvl"," (".$mo2['access'].")");
			}
			$xtpl->parse("link");
			$xtpl->out("link");
			$xtpl->reset("link");
			$xtpl->assign("access_lvl","");
		} elseif ($mo2['type'] == "Container") {
			$xtpl->assign("title",$mo2['title']);
			$xtpl->assign("id",$mo2['id']);
			if($mo2['access'] == 'Private')
			{
				$xtpl->assign("access_lvl"," (".$mo2['access'].")");
			}
			$xtpl->parse("container");
			$xtpl->out("container");
			$xtpl->reset("container");
			$xtpl->assign("access_lvl","");
		} else {
			//object is a category
			$xtpl->assign("title",$mo2['title']);
			$xtpl->assign("id",$mo2['id']);
			if($mo2['access'] == 'Private')
			{
				$xtpl->assign("access_lvl"," (".$mo2['access'].")");
			}
			$xtpl->parse("category");
			$xtpl->out("category");
			$xtpl->reset("category");
			$xtpl->assign("access_lvl","");
			
		}
		output_tree($mo2['id'], $indent+1);
	}
}
?>
