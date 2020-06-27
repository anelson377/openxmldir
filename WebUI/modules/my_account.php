<?php
/*
	XML Directory - Web User Interface
	
	Joe Hopkins <joe@csma.biz>
	Copyright (c) 2005, McFadden Associates.  All rights reserved.
*/

//Checks if id is known, stores in variable
$tmp_id = defang_input($_SESSION['user_id']);

$user = "good"; //defaults user to good before chances of it beging invalid

if (isset($_POST['action']))
{
	//User wants to save, cancel, or delete object
	$myAction = defang_input($_POST['action']);
	if ($myAction == "edit" )
	{
		if (isset($_POST['submit_save']))
		{
			// Saving
			$tmp_id = defang_input($_POST['id']);
			
			$tmp_username = defang_input($_POST['username']);
			$unique_user_sql = "SELECT username,id FROM `users` WHERE username = '$tmp_username' AND id != '$tmp_id'";
			$other_usernames = mysql_query($unique_user_sql, $db);
			
			$tmp_raw_password = defang_input($_POST['password0']);
			
			$tmp_password = md5($tmp_raw_password);
			
			if ($tmp_raw_password != "password_is_saved1")
			{
				//password was changed, save
				$password_sql = "password = '$tmp_password',";
			} else {
				//password was not changed, dont save
				$password_sql = "";
			}
			
			$tmp_email = defang_input($_POST['email']);
			//$tmp_account_type = defang_input($_POST['account_type']);
			
			
			if ($un = mysql_fetch_assoc($other_usernames))
			{
				//There is already a user with this name
				render_HeaderSidebar("Open 79XX XML Directory - User Edit");
				$user = "bad";
				output_edit_user($tmp_id,$user);
				render_Footer();

			} else {
				if ($unique['username'] != $tmp_username)
				{
					$tmpUpdateSQL = "UPDATE users SET
						username = '$tmp_username',
						$password_sql
						email = '$tmp_email'
						WHERE id ='$tmp_id'";		
					mysql_query($tmpUpdateSQL, $db);
					header("Location: index.php?module=menu");
				}	
			}

		} else if (isset($_POST['submit_cancel'])) {
			// Cancel
			header("Location: index.php?module=menu");
			
		} else {
			// Action, but no valid submit button.
			header("Location: index.php?module=my_account");
		}
		
	} else {
		// Bad action
	header("Location: index.php?module=my_account");
	}
	
} else {
	// NO action
	render_HeaderSidebar("Open 79XX XML Directory - User Edit");
	output_edit_user($tmp_id,$user);
	render_Footer();
}		

//Create page and fill in known data
function output_edit_user ($myId,$user)
{
	global $db;
	$xtpl=new XTemplate ("WebUI/modules/templates/my_account.html");

	$theSQL = "SELECT * FROM users WHERE id='$myId'";

	$theRES = mysql_query($theSQL, $db);

	if ($in = mysql_fetch_assoc($theRES))
	{
		$xtpl->assign("id",$in['id']);
		$xtpl->assign("fake_password","password_is_saved1");//do not output real password.
		$xtpl->assign("email",$in['email']);
		$xtpl->assign("username",$in['username']);

		if ($in['account_type'] == "Admin")
		{
			$xtpl->assign("account_type","Admin");
		} else {
			$xtpl->assign("account_type","User");
		}
	}
	
	if ($user == "bad")
	{
		$xtpl->parse("main.bad_username");
		$xtpl->assign("email",defang_input($_POST['email']));
		$xtpl->assign("username",defang_input($_POST['username']));
		$xtpl->assign("password",defang_input($_POST['password']));
		if (defang_input($_POST['account_type']) == "Admin")
		{
			$xtpl->assign("account_type","Admin");
		} else {
			$xtpl->assign("account_type","User");
		}
	}
	// Output
	$xtpl->parse("main");
	$xtpl->out("main");		
}
?>