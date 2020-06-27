<?php
/*
	XML Directory - Web User Interface
	
	Joe Hopkins <joe@csma.biz>
	Copyright (c) 2005, McFadden Associates.  All rights reserved.
*/

if (isset($_POST['submit_add']))
{
	//add new object
	$tmp_id = create_guid($tmp_id);
	$tmpInitSQL = "INSERT INTO object (id) VALUES ('$tmp_id')";
	if ($tmpInitRES = mysql_query($tmpInitSQL, $db))
	{
		// show editor
		header("Location: index.php?module=object_type&id=$tmp_id&new=true");
	} else {
	 // Failure
	 echo "Unable to create object";
	}
} else {
	//display object listings
	render_HeaderSidebar("Open 79XX XML Directory - Manage Objects");
	output_view_objects();
	render_Footer();		
}

//
//  FUNCTIONS
//

function output_view_objects ()
{
	global $db;
	$xtpl=new XTemplate ("WebUI/modules/templates/view_objects.html");
	
	//output static part of page
	$xtpl->parse('main');
	$xtpl->out("main");
	
	
	
	//Assign types to dropdown
	if (isset($_POST['type']))
	{
		$tmp_drop_type = defang_input($_POST['type']);
	} else {
		$tmp_drop_type = defang_input(try_get('drop_type'));
	}
	
	$object_types = array('- Show All -','Container','Link','Contact Holder');
	foreach ($object_types as $typeex) 
	{
		if ($tmp_drop_type == $typeex)	
		{
			$xtpl->assign('selected', "selected");
		}
		$xtpl->assign('type', $typeex);
		$xtpl->parse('main2.type');
		$xtpl->assign('selected', "");
	}
	
	//store membor_of that user wants
	if (isset($_POST['member_of']))
	{
		$member_of_container = defang_input($_POST['member_of']);
	} else {
		$member_of_container = defang_input(try_get('mbr_of'));
	}
	//Assign containers to dropdown
	$member_of_sql = "SELECT * FROM object WHERE type = 'container'";
	$member_of_qry = mysql_query($member_of_sql, $db);
	
	
	//assign show all to container dropdown menu
	$xtpl->assign("member_of","- Show All -");	
	$xtpl->parse("member_of_dropdown");
	$xtpl->out("member_of_dropdown");
	$xtpl->reset("member_of_dropdown");
	
	//show main
	$xtpl->assign("member_of","Main");
	$xtpl->assign("container_id",'0');
	
	if ($member_of_container == '0')
	{
		$xtpl->assign("selected",'selected');
	}	
	$xtpl->parse("member_of_dropdown");
	$xtpl->out("member_of_dropdown");
	//load containers into dropdown menu
	dropdown_menu(0,0,$member_of_container);					
	
	//out second main
	$xtpl->parse("main2");
	$xtpl->out("main2");
	
	$xtpl=new XTemplate ("WebUI/modules/templates/view_objects.html");
	//modify qry for member of
	
	if ($member_of_container != '' && $member_of_container != '- Show All -')
	{ 
		$sql_member_of = $member_of_container;	
	} else {
		$sql_member_of = 0;
		$tmp_sql_view = "";
	}
	//modify qry for type
	if ($tmp_drop_type != '' && $tmp_drop_type != '- Show All -')
	{ 
		$type_view = $tmp_drop_type;	
	} else {
		$type_view = "";
	}
	
	//custum order by
	if (try_get('ob'))
	{
		if (try_get('ob') == "ob_type")
		{
			$ob = "type";
		} elseif (try_get('ob') == "ob_title") { 
			$ob = "title";
		} elseif (try_get('ob') == "ob_access") { 
			$ob = "access";
		}
	} else {
	$ob = "title";
	}
	
	if (isset($_POST['member_of']) || try_get('ob') || try_get('drop_type'))
	{
		//user has hit the search
		if (isset($_POST['member_of']))
		{
			$xtpl->assign("mbr_of",$_POST['member_of']);
		} elseif (try_get('mbr_of')) {
			$xtpl->assign("mbr_of",try_get('mbr_of'));
		}
		if (isset($_POST['type']))
		{
			$xtpl->assign("drop_type",$_POST['type']);
		} elseif (try_get('drop_type')) {
			$xtpl->assign("drop_type",try_get('drop_type'));
		}
		$xtpl->parse("column");//show columns
		$xtpl->out("column");
		// Output
		$xtpl->parse("main3");
		$xtpl->out("main3");
		
		output_tree($sql_member_of,0,$ob,$type_view);
	
	} else {
		//user still needs to select a search query to view objects
		$xtpl->parse("main3");
		$xtpl->out("main3");
		
		include "templates/breaks.html"; //Breaks must be in another file 
										 //or else they will appear above the other outs
	}		
}

function output_tree($member_of,$indent,$ob,$type_view)
{
	global $db, $xtpl;
	$xtpl=new XTemplate ("WebUI/modules/templates/view_objects.html");
	
	$member_of_sql2 = "SELECT * FROM object WHERE member_of = '$member_of' ORDER BY '$ob'";
	$member_of_qry2 = mysql_query($member_of_sql2, $db);

	
	
	while ($mo2 = mysql_fetch_assoc($member_of_qry2))
	{
		if ($mo2['type'] == $type_view || $type_view == '')
		{
			//Generate data rows
			$x = 0;
			while ($x < $indent)
			{
				$xtpl->assign("spacer","../");
				$xtpl->parse("row.spacer");
				$x++;
			}
			$xtpl->assign("title",$mo2['title']);
			if ($mo2['type'] == "Category")
			{
				//change category to better name 
				$xtpl->assign("type","Contact Holder");
			} else {
				$xtpl->assign("type",$mo2['type']);
			}
			
			$xtpl->assign("access",$mo2['access']);
			$xtpl->assign("id",$mo2['id']);
			$xtpl->parse("row.object");
		
			global $oddRow1;
			$oddRow1 = !$oddRow1;
			if ($oddRow1)
			{
				$xtpl->assign("bg","#EFEFEF");
			} else {
				$xtpl->assign("bg","#DFDFDF");
			}
			
			$xtpl->parse("row");
			$xtpl->out("row");
		}
		output_tree($mo2['id'], $indent+1,$ob,$type_view);
	}
}

function dropdown_menu($member_of, $indent,$member_of_container)
{
	/*
		This function selects all of the containers in the database,
		assigns them to the dropdown in the "tree format" so that the hierachy
		is visually seen
	
	*/
	global $db, $xtpl;
	$xtpl=new XTemplate ("WebUI/modules/templates/view_objects.html");

	//Select all containers
	$conQRY = "SELECT * FROM object WHERE member_of = '$member_of' ORDER BY 'title'";
	$conRESULT = mysql_query($conQRY, $db);
	
	while ($mo2 = mysql_fetch_assoc($conRESULT))
	{
		if ($mo2['type'] == "Container")
		{
			//Assign containers to dropdown
			$xtpl->assign("spacer","../");
			$xtpl->parse("member_of_dropdown.spacer");
			$x = 0;
			while ($x < $indent)
			{
				$xtpl->assign("spacer","../");
				$xtpl->parse("member_of_dropdown.spacer");
				$x++;
			}
			$xtpl->assign("member_of",$mo2['title']);
			$xtpl->assign("container_id",$mo2['id']);
			if ($mo2['id'] == $member_of_container)
			{
				$xtpl->assign("selected","selected");
			}
			
			$xtpl->parse("member_of_dropdown");
			$xtpl->out("member_of_dropdown");
		}
		dropdown_menu($mo2['id'], $indent+1,$member_of_container);
	}
}

?>
