<?php
/*
	XML Directory - Web User Interface
	
	Joe Hopkins <joe@csma.biz>
	Copyright (c) 2005, McFadden Associates.  All rights reserved.
*/

render_HeaderSidebar("Open 79XX XML Directory - Home");

$xtpl=new XTemplate ("WebUI/modules/templates/menu.html");

if ($_SESSION['account_type'] == 'Admin')
{
	$xtpl->parse("main.admin_section1");
	$xtpl->parse("main.admin_section2");
}
$xtpl->parse("main");
$xtpl->out("main");

$xtpl=new XTemplate ("WebUI/sidebar.html");

if ($_SESSION['account_type'] == 'Admin')
{
	$xtpl->parse("main.admin_section");
}
$xtpl->parse("main");
$xtpl->out("main");
?>
