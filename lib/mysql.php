<?php 

	
	/////////////////////////////////////////////////////////////////////////////////////////
	/*
			MySQL Authorization Information
			Establish DB Connection
			Entered: 08/04/2016
	*/
	$installed = 'true'; //to be able to reinstall, change this to false
	
	
	$db = mysql_connect('localhost', 'openxmldir', 'gy8n%$aRHEW');
	mysql_select_db('openxmldir', $db);
	
	
	/////////////////////////////////////////////////////////////////////////////////////////
	
	?>