<?php
/*
	XML Directory - Web User Interface
	
	Joe Hopkins <joe@csma.biz>
	Copyright (c) 2005, McFadden Associates.  All rights reserved.
*/

//Checks if id is known, stores in variable
if (isset($_GET['id'])) $tmp_id = defang_input($_GET['id']);

$user = "good"; //defaults user to good before chances of it beging invalid

if (isset($_POST['action']) || isset($_GET['submit_delete']))
{
	//User wants to save, cancel, or delete object
	$myAction = defang_input($_POST['action']);
	if ($myAction == "edit" || $_GET['submit_delete'] == yes)
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
			$tmp_email = defang_input($_POST['email']);
			$tmp_account_type = defang_input($_POST['account_type']);
			
			if ($tmp_raw_password != "password_is_saved1")
			{
				//password was changed, save
				$password_sql = "password = '$tmp_password',";
			} else {
				//password was not changed, dont save
				$password_sql = "";
			}
			
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
						email = '$tmp_email',
						account_type = '$tmp_account_type'
						WHERE id ='$tmp_id'";
						
					mysql_query($tmpUpdateSQL, $db);
					header("Location: index.php?module=view_users");
				}	
			}
		} else if (isset($_POST['submit_delete']) || $_GET['submit_delete'] == 'yes') {
			// Deleting
			$tmp_id = defang_input($tmp_id);
			if ($tmp_id != '0' && $tmp_id != $_SESSION['user_id'])
			{
				delete_user($tmp_id);
			} else {
				//deleting of admin account is not allowed
				header("Location: index.php?module=view_users");
			}
			header("Location: index.php?module=view_users");

		} else if (isset($_POST['submit_cancel'])) {
			// Cancel
			if ($_GET['new'] == "true")
			{
				delete_user($tmp_id);
			}
			header("Location: index.php?module=view_users");
			
		} else {
			// Action, but no valid submit button.
			header("Location: index.php?module=view_users");
		}
		
	} else {
		// Bad action
		header("Location: index.php?module=view_users");
	}
	
} else {
	// NO action
	render_HeaderSidebar("Open 79XX XML Directory - User Edit");
	output_edit_user($tmp_id,$user);
	render_Footer();
}

function delete_user ($tmp_id)
{
	$sql = "DELETE FROM users WHERE id='$tmp_id'";
    $result = mysql_query($sql);
}			

//Create page and fill in known data
function output_edit_user ($myId,$user)
{
	global $db;
	$xtpl=new XTemplate ("WebUI/modules/templates/edit_user.html");

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
			$xtpl->assign("var_account_type","User");
		} else {
			$xtpl->assign("account_type","User");
			$xtpl->assign("var_account_type","Admin");
		}
	}
	
	if ($user == "bad")
	{
		$xtpl->parse("main.bad_username");
		$xtpl->assign("email",defang_input($_POST['email']));
		$xtpl->assign("username",defang_input($_POST['username']));
		$xtpl->assign("password",defang_input($_POST['password']));
		if ($_SESSION['user_id'] == $in['id'] || $in['id'] == '0')
		{
			//dont show delete
		} else {
			$xtpl->parse("main.notuser");
		}
		if (defang_input($_POST['account_type']) == "Admin")
		{
			$xtpl->assign("account_type","Admin");
			$xtpl->assign("var_account_type","User");
		} else {
			$xtpl->assign("account_type","User");
			$xtpl->assign("var_account_type","Admin");
		}
	}
	// Output
	$xtpl->parse("main");
	$xtpl->out("main");		
}
?>