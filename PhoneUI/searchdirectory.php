<?php
/*
	searchdirectory.php - Search Contacts Page
	XML Directory
	
	Joe Hopkins <joe@csma.biz>
	Copyright (c) 2005, McFadden Associates.  All rights reserved.
*/

require_once "../lib/xtpl.php";
require_once "../lib/utils.php";
require_once "../lib/mysql.php";

require_once "lib/urlbase.php";
require_once "lib/security.php";//grab mac address info, along with global preferences
require_once "lib/headers.php";


if ($ph_sec == 'Yes' && $registered == 'FALSE')
{
	//Security to stop unregistered users from going any further if 'Phone Security' is on. XML images cannot be templated with XTPL.
	require_once "templates/img_sec_breach.php";

} elseif (try_get('srch'))
//User has sent all information needed to search
{	
	if (try_get('srch') == "contact")
	{
		//User is searching for a contact

		$varLN = try_defang('lname');
		$varFN = try_defang('fname');
		$varCP = try_defang('company');

		if (try_get('search1'))
		//user is searching specific directory
		{
			$dirID = defang_input(try_get('search1'));
			$dir = "AND contacts.member_of = '".$dirID."'";
		} else {
		//user is doing a global search	
			$dir = '';
		}
		
		$style = 'Seperate';//currently the only style, code here just for future
		$find = "contact";//we are searching for a contact, not a phone
		
		list_contacts ($style,$varLN,$varFN,$varCP,$MAC,$dir,$find);
	} else {
		//User is searhing for a phone
		if (try_get('lname') != 'any' && try_get('lname') != '')
		{
			$varLN = defang_input(try_get('lname')).'%';
		} else {
			$varLN = '%';//% returns all
		}
		if (try_get('fname') != 'any' && try_get('fname') != '')
		{
			$varFN = defang_input(try_get('fname')).'%';
		} else {
			$varFN = '%';//% returns all
		}
			
		$dir = '0';//this variable is not used when searching for a phone
		$varCP = '0';//this variable is not used when searching for a phone
		
		$style = 'Seperate';//currently the only style, code here just for future
		$find = "phone";//we are searching for a phone, not a contact
		
		list_contacts ($style,$varLN,$varFN,$varCP,$MAC,$dir,$find);
	}
} elseif (try_get('dir')) {
//User screen to input query to search a specific directory	
	
	$dir1 = defang_input(try_get('dir'));//get directory user wants to search
	
	$xtpl=new XTemplate ("templates/dir_search.xml");
	$xtpl->assign("url_base",$URLBase);
	$xtpl->assign("MAC",$MAC);
	$xtpl->assign("dir1",$dir1);
	$xtpl->parse("main");
	$xtpl->out("main");

} elseif (try_get('specific'))  {
	//Dispay list of directories that can be searched

	$per_page = 31;//number of contacts displayed on each page
		
		if (try_get('start'))
		{
			$start = defang_input(try_get('start'));
			$limitstart = 'LIMIT '.$start.','.$per_page;
			
		} else {
			$start = 0;
			$limitstart = 'LIMIT 0,'.$per_page;
		}

	if ($access_lvl == 'Restricted' && $ob_sec == 'Yes') 
	{
		//User is not unrestricted, apply security settings to show only 'Public' access 
		$object_sec_query = "AND object.access = 'Public'";
	} else {
		//Security settings are not in place, or user is unrestricted, do not restrict to 'Public' access
		$object_sec_query = "";
	}
	
	//count how many categories will be shown
	$countQuery = "SELECT
			COUNT(object.id) AS total
			FROM object
			WHERE object.type = 'Category'
			$object_sec_query";
	
	$theCountRES = mysql_query($countQuery, $db);
	//Fetch total items
	if ($in = mysql_fetch_assoc($theCountRES))
	{
		$totalCount = $in['total'];
	}
	
	//Calc remaining rows
	$remainingRows = ($totalCount - $start);
	
	//select categories and information to be shown
	$browseQuery = "SELECT
			object.id AS id,
			object.title AS title,
			object.access AS access,
			object.type AS type
			FROM object
			WHERE object.type = 'Category'
			$object_sec_query
			ORDER BY object.title
			$limitstart";
	
	if ($totalCount != '0')
	{	
		//Categories exist, display listing
		$theBrowseRES = mysql_query($browseQuery, $db);
			
		$xtpl=new XTemplate ("templates/cat_list_srch.xml");

		while ($in2 = mysql_fetch_assoc($theBrowseRES))
		{
			$xtpl->assign("title",$in2[title]);
			$xtpl->assign("url_base",$URLBase);
			$xtpl->assign("MAC",$MAC);
			$xtpl->assign("ID",$in2['id']);
			$xtpl->parse("main.object_menu.ObURL");
			$xtpl->parse("main.object_menu");
		}
		// If there are more entries, show Next
		if ($remainingRows > $per_page)
		{
			$start = $start + $per_page;
			$xtpl->assign("title","More");
			$xtpl->assign("ID",$member_of);
			$xtpl->assign("url_base",$URLBase);
			$xtpl->assign("start",$start);
			$xtpl->assign("MAC",$MAC);
			$xtpl->parse("main.object_more");
		}
		$xtpl->assign("heading",$title);
		$xtpl->parse("main");
		$xtpl->out("main");

	} else {
		//Categories do not exist, show empty
		$xtpl=new XTemplate ("templates/empty_con.xml");
		$xtpl->parse("main");
		$xtpl->out("main");
	}
	
} elseif (try_get('global')) {
	//Get Search Query from what user has entered in on phone
	if (try_get('find'))
	{
		if (try_get('find') == "contact")
		{
			//user wants to find contact
			$xtpl=new XTemplate ("templates/glo_search.xml");
			$xtpl->assign("url_base",$URLBase);
			$xtpl->assign("MAC",$MAC);
			$xtpl->parse("main");
			$xtpl->out("main");
		} else {
			//user wants to find a phone
			$xtpl=new XTemplate ("templates/glo_search_phone.xml");
			$xtpl->assign("url_base",$URLBase);
			$xtpl->assign("MAC",$MAC);
			$xtpl->parse("main");
			$xtpl->out("main");
		}
	} else {
		//user must choose what to "find"
		$xtpl=new XTemplate ("templates/glo_index.xml");
		$xtpl->assign("url_base",$URLBase);
		$xtpl->assign("MAC",$MAC);
		$xtpl->parse("main");
		$xtpl->out("main");
	}
} else {
	//choose global or directory
	$xtpl=new XTemplate ("templates/srch_index.xml");
	$xtpl->assign("url_base",$URLBase);
	$xtpl->assign("MAC",$MAC);
	$xtpl->parse("main");
	$xtpl->out("main");
}


function list_contacts ($style,$varLN,$varFN,$varCP,$MAC,$dir,$find)
{
	/*
	
		List contacts in specified category if user has limited, 
		if not, list contacts from all categories that match the search query.
	
	*/
	
	
	global $db;
	global $URLBase;
	global $registered;
	global $ob_sec;
	global $access_lvl;
	
	if ($style == 'Seperate')
	{
		$per_page = 31;//number of contacts displayed on each page
		
		if (try_get('start'))
		{
			$start = defang_input(try_get('start'));
			$limitstart = 'LIMIT '.$start.','.$per_page;
			
		} else {
			$start = 0;
			$limitstart = 'LIMIT 0,'.$per_page;
		}
	} else {
		//pagination not used in together style
		$limitstart = '';
	}
	
	if ($find == "contact")
	{
		//we are searching for a contact
		
		//beacuse contacts are in categories which have access levels, we need to implement security 
		if ($access_lvl == 'Restricted' && $ob_sec == 'Yes')
		{
			//User is not unrestricted, apply security settings to show only 'Public' access 
			$securityQuery = "AND contacts.member_of = (SELECT
			object.id FROM object
			WHERE object.id = contacts.member_of
			AND object.access = 'Public')";
		} else {
			//Security settings are not in place, or user is unrestricted, do not restrict to 'Public' access
			$securityQuery = "";
		}
		
		//Number of contacts to be displayed	
		$countQuery = "SELECT
			COUNT(contacts.id) AS total
			FROM contacts
			WHERE contacts.lname LIKE '$varLN'
			AND contacts.fname LIKE '$varFN'
			AND contacts.company LIKE '$varCP'
			$securityQuery
			$dir";
		
	} else {
		//we are searching for a phone
		
		//Number of phones to be displayed	
		$countQuery = "SELECT
			COUNT(phone.id) AS total
			FROM phone
			WHERE phone.lname LIKE '$varLN'
			AND phone.fname LIKE '$varFN'";
	}

	$theCountRES = mysql_query($countQuery, $db);
	
	
	//Fetch total items
	if ($in = mysql_fetch_assoc($theCountRES))
	{
		$totalCount = $in['total'];
	}
	
	//Calc remaining rows
	$remainingRows = ($totalCount - $start);
	
	if ($totalCount != "0")
	{
		//Items that match search creteria exist, fetch data
		
		if ($find == "contact")
		{
			//we are searching for a contact
			$browseQuery = "SELECT 
				contacts.id AS id,
				contacts.lname AS lname,
				contacts.display_name as display_name,
				contacts.fname AS fname,
				contacts.company AS company,
				contacts.office_phone AS office_phone,
				contacts.home_phone AS home_phone,
				contacts.custom_phone AS custom_phone,
				contacts.custom_number AS custom_number,
				contacts.cell_phone AS cell_phone,
				contacts.other_phone AS other_phone,
				contacts.title AS title
				FROM contacts
				WHERE contacts.lname LIKE '$varLN'
				AND contacts.fname LIKE '$varFN'
				AND contacts.company LIKE '$varCP'
				$securityQuery
				$dir
				ORDER BY contacts.display_name
				$limitstart";
				
			$xtpl=new XTemplate ("templates/glo_results_contacts.xml");
		} else {
			//we are searching for a phone
			$browseQuery = "SELECT 
				phone.id AS id,
				phone.MAC AS MAC,
				phone.lname AS lname,
				phone.number as number,
				phone.fname AS fname
				FROM phone
				WHERE phone.lname LIKE '$varLN'
				AND phone.fname LIKE '$varFN'
				ORDER BY phone.lname
				$limitstart";
				
			$xtpl=new XTemplate ("templates/glo_results_phones.xml");
		}
			
		$theBrowseRES = mysql_query($browseQuery, $db);
	
		if ($style == 'Seperate')
		{
			//category is set to display phone numbers on a seperate screen
			
			if ($remainingRows <= $per_page)
			{
				$prompt = ($start + 1) ." to ". ($start + $remainingRows) ." of ". $totalCount.".";
			} else {
				$prompt = ($start + 1) ." to ". ($start + $per_page) ." of ". $totalCount.".";
			}

			while ($in2 = mysql_fetch_assoc($theBrowseRES))
			{
				
				if ($find == "contact")
				{
					$tmpTitle = $in2['display_name'];
					$file = "menuItems.php";
				} else {
					//we are viewing phones
					$tmpTitle = $in2['lname'].",".$in2['fname']." (". $in2['number'].")";
					$file = "status.php";
				}

				$title = substr($tmpTitle,0,27);
				
				if ($find == "contact")
				{
					$ID = $in2['id']; //use id to identify contact
				} else {
					$ID = $in2['MAC']; //use mac to identify specific phone
				}
								
				$xtpl->assign("title",$title);
				$xtpl->assign("url_base",$URLBase);
				$xtpl->assign("MAC",$MAC);
				$xtpl->assign("ID",$ID);
				$xtpl->parse("main.contact_menu");
			}
			// If there are more entries, show Next
			if ($remainingRows > $per_page)
			{
				$start = $start + $per_page;

				$xtpl->assign("title","More");
				$xtpl->assign("url_base",$URLBase);
				$xtpl->assign("MAC",$MAC);
				$xtpl->assign("lname",$varLN);
				$xtpl->assign("fname",$varFN);
				$xtpl->assign("company",$varCP);		
				$xtpl->assign("obID",$obID);
				$xtpl->assign("start",$start);
				$xtpl->parse("main.contact_more");
			}
			$xtpl->assign("heading","Results:");
			$xtpl->assign("prompt",$prompt);
			$xtpl->parse("main");
			$xtpl->out("main");
		}
	} else {
		//There are no items that match search
		//Take out '%' before showing user what they searched for that found 0 results
		if ($varLN != '%')
		{
			$varLN = trim(preg_replace("/%/i", ",", $varLN));
		} else {
			$varLN= '- All -,';
		}
		if ($varFN != '%')
		{
			$varFN = trim(preg_replace("/%/i", ",", $varFN));
		} else {
			$varFN= '- All -,';
		}
		if ($varCP != '0')
		{
			if ($varCP != '%')
			{
				$varCP = trim(preg_replace("/%/i", ",", $varCP));
			} else {
				$varCP= '- All -';
			}
		} else {
			//user searched for a phone, no company search field
		}
		
		// Show no results image
		require_once "templates/img_no_res.php";
	}
}
?>
