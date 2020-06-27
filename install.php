<?php



	
require_once "lib/utils.php";
require_once "lib/xtpl.php";
require_once "lib/mysql.php";

if ($installed == "111true")
{
	//database installation has already occurred
	$xtpl=new XTemplate ("WebUI/modules/templates/install.html");
	$xtpl->parse("main.installed");
	$xtpl->parse("main");
	$xtpl->out("main");

} else if(isset($_POST['database_name']))
{

	$database_name = defang_input($_POST['database_name']);
	$database_server = defang_input($_POST['database_server']);
	$database_login = defang_input($_POST['database_login']);
	$database_password = defang_input($_POST['database_password']);
	
	
	
	
	//user has entered database information
	$filename = 'lib/mysql.php';
	$file_content = "<?php \n";
	$file_content .=
	"
	
	/////////////////////////////////////////////////////////////////////////////////////////
	/*
			MySQL Authorization Information
			Establish DB Connection
			Entered: ".date("m")."/".date("d")."/".date("Y")."
	*/
	\$installed = 'true'; //to be able to reinstall, change this to false
	
	
	\$db = mysql_connect('$database_server', '$database_login', '$database_password');
	mysql_select_db('$database_name', \$db);
	
	
	/////////////////////////////////////////////////////////////////////////////////////////
	
	";
	$file_content .= "?>";
	// Let's make sure the file exists and is writable first.
	if (is_writable($filename)) {
	
	   // In our example we're opening $filename in append mode.
	   // The file pointer is at the bottom of the file hence
	   // that's where $somecontent will go when we fwrite() it.
	   if (!$handle = fopen($filename, 'w')) {
			 echo "Cannot open file ($filename)";
			 exit;
	   }
	
	   // Write $somecontent to our opened file.
	   if (fwrite($handle, $file_content) === FALSE) {
		   echo "Cannot write to file ($filename)";
		   exit;
	   }

	  
	   fclose($handle);
	   //success go to database dump page
	   header("Location: WebUI/modules/install_success.php?database_name=$database_name&database_server=$database_server&database_login=$database_login&database_password");
	
	} else {
	   echo "The file $filename is not writable. <br> Please make sure the Apache (or other web server process) has appropriate write permissions.";
	}

} else {
	//user need to enter database information
	$xtpl=new XTemplate ("WebUI/modules/templates/install.html");
	$xtpl->parse("main.install");
	$xtpl->parse("main");
	$xtpl->out("main");
}





?>
