<?php
render_HeaderSidebar("Open 79XX XML Directory - Home");
$xtpl=new XTemplate ("WebUI/modules/templates/help.html");

$xtpl->parse("main");
	$xtpl->out("main");

render_Footer();

?>