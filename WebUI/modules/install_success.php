<?php


require_once "../../lib/utils.php";
require_once "../../lib/xtpl.php";
require_once "../../lib/mysql.php";
require_once "../../PhoneUI/lib/urlbase.php";

$database_name = defang_input($_GET['database_name']);
$database_server = defang_input($_GET['database_server']);
$database_login = defang_input($_GET['database_login']);
$database_password = defang_input($_GET['database_password']);


if (isset($_POST['url_base']))
{
	$new_url = defang_input($_POST['url_base']);
	//user has entered database information
	$filename = "../../PhoneUI/lib/urlbase.php";
	$file_content = "<?php \n";
	$file_content .=
	"
	\$url_end = '$new_url';
	
	
	//URL has been modified
	\$URLBase = 'http://'.\$_SERVER['HTTP_HOST'].'/'.\$url_end;
	
	
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
	   //success 
	   header("Location: install_success.php?urlupdate=completed");
	
	} else {
	   echo "The file $filename is not writable. <br> Please make sure the Apache (or other web server process) has appropriate write permissions.";
	}

} else if (isset($_POST['create_database']))
{
	$handle = fopen("../../db.sql", "r");
	
	$sql_query = fread($handle, filesize("../../db.sql"));
	
    $pieces = array();
    PMA_splitSqlFile($pieces, $sql_query, PMA_MYSQL_INT_VERSION);
    $pieces_count = count($pieces);
	
	for ($i = 0; $i < $pieces_count; ++$i) 
	{
		$result = mysql_query($pieces[$i]['query']);
	}
	
	
	
	$xtpl=new XTemplate ("templates/install_success.html");
	
	$cUrl = curl_init();
	curl_setopt($cUrl, CURLOPT_URL,"$URLBase");
	curl_setopt($cUrl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($cUrl, CURLOPT_HTTPPROXYTUNNEL, 1);
	$PageContent = curl_exec($cUrl);
	curl_close($cUrl);
	
	$sub_content = trim(substr($PageContent,3,23));
	
	if ($sub_content == "CiscoIPPhoneGraphicMenu")
	{
		//URLbase is correct
		
		$checkSQL = "SELECT id,username,password FROM users WHERE id='0'";
		$checkRES = mysql_query($checkSQL, $db);
		
		if ($in = mysql_fetch_assoc($checkRES))
		{
			$xtpl->assign("username",$in['username']);
			$xtpl->assign("password",$in['password']);
			$xtpl->assign("completed","Completed");
			$xtpl->assign("database_name",$database_name);
			$xtpl->assign("database_login",$database_login);
			$xtpl->parse("main.no_broke_link");
			$xtpl->parse("main");
			$xtpl->out("main");
		
		} else {
		
			$xtpl->parse("main.failure");
			$xtpl->parse("main");
			$xtpl->out("main");
		}
	} else {
		//URL needs to be modified
		$xtpl->assign("url_end",$url_end);
		$xtpl->assign("current_url",$URLBase);
		$ServerBase = 'http://'.$_SERVER['HTTP_HOST'].'/';
		$xtpl->assign("ServerBase",$ServerBase);
		$xtpl->parse("main.broke_link");
		$xtpl->parse("main");
		$xtpl->out("main");
	
	}
	
	
} else if(isset($_GET['urlupdate']))
{
	$xtpl=new XTemplate ("templates/install_success.html");
	
	$cUrl = curl_init();
	curl_setopt($cUrl, CURLOPT_URL,"$URLBase");
	curl_setopt($cUrl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($cUrl, CURLOPT_HTTPPROXYTUNNEL, 1);
	$PageContent = curl_exec($cUrl);
	curl_close($cUrl);
	
	$sub_content = trim(substr($PageContent,3,23));
	
	if ($sub_content == "CiscoIPPhoneGraphicMenu")
	{
		//URLbase is correct
		
		$checkSQL = "SELECT id,username,password FROM users WHERE id='0'";
		$checkRES = mysql_query($checkSQL, $db);
		
		if ($in = mysql_fetch_assoc($checkRES))
		{
			$xtpl->assign("username",$in['username']);
			$xtpl->assign("password",$in['password']);
			$xtpl->assign("completed","Completed");
			$xtpl->assign("database_name",$database_name);
			$xtpl->assign("database_login",$database_login);
			$xtpl->parse("main.no_broke_link");
			$xtpl->parse("main");
			$xtpl->out("main");
		
		} else {
			
			$xtpl->parse("main.failure");
			$xtpl->parse("main");
			$xtpl->out("main");
		}
	} else {
		//URL needs to be modified
		$xtpl->assign("url_end",$url_end);
		$xtpl->assign("current_url",$URLBase);
		$ServerBase = 'http://'.$_SERVER['HTTP_HOST'].'/';
		$xtpl->assign("ServerBase",$ServerBase);
		$xtpl->parse("main.broke_link");
		$xtpl->parse("main");
		$xtpl->out("main");
	
	}
	 
} else {

	$xtpl=new XTemplate ("templates/install_success.html");
	$xtpl->assign("database_name",$database_name);
	$xtpl->assign("database_login",$database_login);
	$xtpl->parse("main.proccess");
	$xtpl->parse("main");
	$xtpl->out("main");



}

/* $Id: read_dump.lib.php,v 2.10 2004/10/19 12:49:21 nijel Exp $ */
// vim: expandtab sw=4 ts=4 sts=4:

/**
 * Removes comment lines and splits up large sql files into individual queries
 *
 * Last revision: September 23, 2001 - gandon
 *
 * @param   array    the splitted sql commands
 * @param   string   the sql commands
 * @param   integer  the MySQL release number (because certains php3 versions
 *                   can't get the value of a constant from within a function)
 *
 * @return  boolean  always true
 *
 * @access  public
 */
function PMA_splitSqlFile(&$ret, $sql, $release)
{
    // do not trim, see bug #1030644
    //$sql          = trim($sql);
    $sql          = rtrim($sql, "\n\r");
    $sql_len      = strlen($sql);
    $char         = '';
    $string_start = '';
    $in_string    = FALSE;
    $nothing      = TRUE;
    $time0        = time();

    for ($i = 0; $i < $sql_len; ++$i) {
        $char = $sql[$i];

        // We are in a string, check for not escaped end of strings except for
        // backquotes that can't be escaped
        if ($in_string) {
            for (;;) {
                $i         = strpos($sql, $string_start, $i);
                // No end of string found -> add the current substring to the
                // returned array
                if (!$i) {
                    $ret[] = array('query' => $sql, 'empty' => $nothing);
                    return TRUE;
                }
                // Backquotes or no backslashes before quotes: it's indeed the
                // end of the string -> exit the loop
                else if ($string_start == '`' || $sql[$i-1] != '\\') {
                    $string_start      = '';
                    $in_string         = FALSE;
                    break;
                }
                // one or more Backslashes before the presumed end of string...
                else {
                    // ... first checks for escaped backslashes
                    $j                     = 2;
                    $escaped_backslash     = FALSE;
                    while ($i-$j > 0 && $sql[$i-$j] == '\\') {
                        $escaped_backslash = !$escaped_backslash;
                        $j++;
                    }
                    // ... if escaped backslashes: it's really the end of the
                    // string -> exit the loop
                    if ($escaped_backslash) {
                        $string_start  = '';
                        $in_string     = FALSE;
                        break;
                    }
                    // ... else loop
                    else {
                        $i++;
                    }
                } // end if...elseif...else
            } // end for
        } // end if (in string)

        // lets skip comments (/*, -- and #)
        else if (($char == '-' && $sql_len > $i + 2 && $sql[$i + 1] == '-' && $sql[$i + 2] <= ' ') || $char == '#' || ($char == '/' && $sql_len > $i + 1 && $sql[$i + 1] == '*')) {
            $i = strpos($sql, $char == '/' ? '*/' : "\n", $i);
            // didn't we hit end of string?
            if ($i === FALSE) {
                break;
            }
            if ($char == '/') $i++;
        }

        // We are not in a string, first check for delimiter...
        else if ($char == ';') {
            // if delimiter found, add the parsed part to the returned array
            $ret[]      = array('query' => substr($sql, 0, $i), 'empty' => $nothing);
            $nothing    = TRUE;
            $sql        = ltrim(substr($sql, min($i + 1, $sql_len)));
            $sql_len    = strlen($sql);
            if ($sql_len) {
                $i      = -1;
            } else {
                // The submited statement(s) end(s) here
                return TRUE;
            }
        } // end else if (is delimiter)

        // ... then check for start of a string,...
        else if (($char == '"') || ($char == '\'') || ($char == '`')) {
            $in_string    = TRUE;
            $nothing      = FALSE;
            $string_start = $char;
        } // end else if (is start of string)

        elseif ($nothing) {
            $nothing = FALSE;
        }

        // loic1: send a fake header each 30 sec. to bypass browser timeout
        $time1     = time();
        if ($time1 >= $time0 + 30) {
            $time0 = $time1;
            header('X-pmaPing: Pong');
        } // end if
    } // end for

    // add any rest to the returned array
    if (!empty($sql) && preg_match('@[^[:space:]]+@', $sql)) {
        $ret[] = array('query' => $sql, 'empty' => $nothing);
    }

    return TRUE;
} // end of the 'PMA_splitSqlFile()' function



?>