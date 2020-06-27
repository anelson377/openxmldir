<?php
/*
	XML Directory - Web User Interface
	
	Joe Hopkins <joe@csma.biz>
	Copyright (c) 2005, McFadden Associates.  All rights reserved.
*/
	
render_HeaderSidebar("Open 79XX XML Directory - Error");
$xtpl=new XTemplate ("WebUI/modules/templates/not_admin.html");
// Output
$xtpl->parse("main");
$xtpl->out("main");


render_Footer();

?>