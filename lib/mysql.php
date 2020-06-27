<?php 

	
	/////////////////////////////////////////////////////////////////////////////////////////
	/*
			MySQL Authorization Information
			Establish DB Connection
			Entered: 08/04/2016
	*/

	// Include file for converting new-style PHP functions to old ones
//	require_once "fix_mysql.inc.php";
	include_once('fix_mysql.inc.php');

	$installed = 'true'; //to be able to reinstall, change this to false
	
	
	$db = mysql_connect('localhost', 'openxmldir', 'gy8n%$aRHEW');
	mysql_select_db('openxmldir', $db);
	
	
	/////////////////////////////////////////////////////////////////////////////////////////
	
	?>
