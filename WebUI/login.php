<?php

/*
	login.php - User Authentication Page
	XML Directory
	
	Joe Hopkins <joe@csma.biz>
	Copyright (c) 2005, McFadden Associates.  All rights reserved.
*/

session_start();

require_once "../lib/mysql.php";	
require_once "../lib/utils.php";
require_once "../lib/xtpl.php";

// Log Out
if (try_get('module') && $_POST['username'] == "")
{
	$ModuleName = defang_input(try_get('module'));
	if ($ModuleName == "logout")
	{
		session_destroy();
		$logout = "You have been successfully logged out!";
	}
}

// Check Login
if(isset($_POST['Login']))
{
	$remoteInfo = "(".$_SERVER['HTTP_USER_AGENT'].")";
	$username = defang_input($_POST['username']);
	$password = defang_input($_POST['password']);
	$crypt_pass = md5($password);
	$checkSQL = "SELECT id,username,account_type FROM users WHERE username='$username' AND password='$crypt_pass'";
	$checkRES = mysql_query($checkSQL, $db);
	
	if ($in = mysql_fetch_assoc($checkRES))
	{
		$_SESSION['user_id'] = $in['id'];
		$_SESSION['user_name'] = $in['username'];
		$_SESSION['account_type'] = $in['account_type'];
		
		header("Location: ../index.php?module=menu");		
	} else {
		//fail to login
		$errMsg = "Bad Username or Password";
	}
}


if ($installed == 'false')
{
	//not installed
} else {
	$installed == 'true';
}
if (defang_input(try_get('newuser')) == "true")
{
	$errMsg = "It recommended that you change your password after logging in";
}

// Produce the login page
output_login_page($errMsg,$logout,$installed);

//
//  FUNCTIONS
//

function output_login_page ($errMsg,$logout,$installed)
{
	
	$xtpl=new XTemplate ("modules/templates/login.html");
	if ($installed == 'false')
	{
		$xtpl->parse("main.install");
	} else {
	
		$xtpl->parse("main.installed");
		$xtpl->assign("error_msg",$errMsg);
		$xtpl->assign("logout",$logout);
		
	}
	$xtpl->parse("main");
	$xtpl->out("main");
}

?>
