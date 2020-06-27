<?php 
/*
	index.php
	XML Directory
	
	Joe Hopkins <joe@csma.biz>
	Copyright (c) 2005, McFadden Associates.  All rights reserved.
*/

session_start();
require_once "lib/utils.php";
require_once "lib/mysql.php";
require_once "lib/xtpl.php";


// Check Login Status
if (isset($_SESSION['user_id']))
{
	// Login OK
	if (isset($_GET['module']))
	{
		// Choose which module to view
		$ModuleName = defang_input($_GET['module']);
		if ($ModuleName == "menu"){
			require_once "WebUI/modules/menu.php";
	
		} elseif ($ModuleName == "view_objects") {
			require_once "WebUI/modules/view_objects.php";
			
		} elseif ($ModuleName == "object_type") {
			require_once "WebUI/modules/object_type.php";	
			
		} elseif ($ModuleName == "edit_object") {
			require_once "WebUI/modules/edit_object.php";
		
		} elseif ($ModuleName == "view_contacts") {
			require_once "WebUI/modules/view_contacts.php";
		
		} elseif ($ModuleName == "edit_contact") {
			require_once "WebUI/modules/edit_contact.php";
			
		} elseif ($ModuleName == "import_contacts") {
			require_once "WebUI/modules/import_contacts.php";
			
		} elseif ($ModuleName == "view_phones"){
			require_once "WebUI/modules/view_phones.php";
		
		} elseif ($ModuleName == "edit_phone"){
			require_once "WebUI/modules/edit_phone.php";
		
		
		//this is temprorary, it is to fix the displays at a client.....	
		} elseif ($ModuleName == "display_name_fix"){
			require_once "WebUI/modules/display_name_fix.php";
		///////////////////////////////////////////////////////////////////
			
		} elseif ($ModuleName == "view_memos"){
			require_once "WebUI/modules/view_memos.php";
			
		} elseif ($ModuleName == "correct_members"){
			require_once "WebUI/modules/correct_members.php";
			
		} elseif ($ModuleName == "correct_ob_members"){
			require_once "WebUI/modules/correct_ob_members.php";

		} elseif ($ModuleName == "edit_memos"){
			require_once "WebUI/modules/edit_memos.php";

		} elseif ($ModuleName == "view_users"){
			if ($_SESSION['account_type'] == 'Admin')
			{			
				require_once "WebUI/modules/view_users.php";
			} else {
				require_once "WebUI/modules/not_admin.php";
			}
		} elseif ($ModuleName == "edit_user"){
			if ($_SESSION['account_type'] == 'Admin')
			{			
				require_once "WebUI/modules/edit_user.php";
			} else {
				require_once "WebUI/modules/not_admin.php";
			}
		
		} elseif ($ModuleName == "my_account"){	
			require_once "WebUI/modules/my_account.php";
			
		} elseif ($ModuleName == "global_pref"){
			if ($_SESSION['account_type'] == 'Admin')
			{
				require_once "WebUI/modules/global_pref.php";
			} else {
				require_once "WebUI/modules/not_admin.php";
			}
		} elseif ($ModuleName == "msg"){
			require_once "WebUI/modules/msg.php";
			
		} elseif ($ModuleName == "tree"){
			require_once "WebUI/modules/tree.php";
			
		} elseif ($ModuleName == "testanchor"){
			require_once "WebUI/modules/testanchor.php";
			
		} elseif ($ModuleName == "help"){
			require_once "WebUI/modules/help.php";
			
		} elseif ($ModuleName == "delete_error"){
			require_once "WebUI/modules/delete_error.php";
			//End Modules Section
		} else {
			//Bad Module was supplied, go to main menu
			require_once "WebUI/modules/menu.php";
		}
	} else {
		//No module was supplied, go to main menu
		require_once "WebUI/modules/menu.php";
	}		
} else {
	//User is not logged in, direct to login page
	header("Location: WebUI/login.php");
}
//extra java
require_once "WebUI/lib/xtra_java.php";
		
//function to bring header and navigation bar
function render_HeaderSidebar ($mytitle) { 
	$xtpl=new XTemplate ("WebUI/header.html");
	$xtpl->assign("page_title",$mytitle);
	$xtpl->assign("current",$_SESSION['user_name']);
	
	if ($_SESSION['account_type'] == 'Admin')
	{
		$xtpl->parse("main.admin_section");
	}
	$xtpl->parse("main");
	$xtpl->out("main");
}

function render_Footer() {
	$xtpl=new XTemplate ("WebUI/sidebar.html");

	if ($_SESSION['account_type'] == 'Admin')
	{
		$xtpl->parse("main.admin_section");
	}
	$xtpl->parse("main");
	$xtpl->out("main");
}
		
?>