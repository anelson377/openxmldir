<?php
/*
	menuItems.php - Browse Objects
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
	//Security to stop unregistered users from going any further if 'Phone Security' is on.  XML images cannot be templated with XTPL.
	require_once "templates/img_sec_breach.php";

} elseif (try_get('obj')) {
	// We are selecting an object
	$obID = defang_input(try_get('obj'));
	$objQuery = "SELECT 
	object.member_of AS member_of,
	object.id AS id,
	object.title AS title,
	object.access AS access,
	object.type AS type,
	object.style AS style
	FROM object WHERE object.id='$obID'";
	$theobjRES = mysql_query($objQuery, $db);
	

	if ($in = mysql_fetch_assoc($theobjRES))
	{
		if ($in['access'] == 'Public' || $access_lvl == 'Unrestricted' || $ob_sec == 'No') 
		{
			//User is registered, or a public Container is listed, or object security is turned off	
			if ($in['type'] == 'Container') 
			{
				//The object Type is a container, use list objects function
				list_objects ($in['id'],$in['title'],$MAC,$registered,$ob_sec,$ph_sec);
	
			} elseif ($in['type'] = 'Category') {
				//The object Type is a category, use list contacts function
				list_contacts ($in['title'],$in['style'],$obID,$MAC);
			}
		} else {
			//User did not meet security requirements to view objects, send to main
			list_objects (0,'Main',$registered,$ob_sec,$ph_sec);
		}
	}
} elseif (try_get('ur')) {
	//Specific contact has been selected (style was seperate)
	$urID = defang_input(try_get('ur'));
	$browseQuery = "SELECT 
		contacts.id AS id,
		contacts.lname AS lname,
		contacts.fname AS fname,
		contacts.company AS company,
		contacts.office_phone AS office_phone,
		contacts.home_phone AS home_phone,
		contacts.cell_phone AS cell_phone,
		contacts.other_phone AS other_phone,
		contacts.custom_phone AS custom_phone,
		contacts.custom_number AS custom_number,
		contacts.sup_prefix_office AS sup_prefix_office,
		contacts.sup_prefix_cell AS sup_prefix_cell,
		contacts.sup_prefix_home AS sup_prefix_home,
		contacts.sup_prefix_other AS sup_prefix_other,
		contacts.sup_prefix_custom AS sup_prefix_custom,
		contacts.title AS title,
		contacts.speed_dial AS speed_dial
		FROM contacts
		WHERE contacts.id = '$urID'";
	
	$theContactRES = mysql_query($browseQuery, $db);
        if(! $theContactRES) {
		error_log('Invalid query: ' . mysql_error());
		exit(1);
	}

	if ($in = mysql_fetch_assoc($theContactRES))
	{
		$tmpTitle = $in['fname'] ." ". $in['lname'];
		if ($in['title'] != '' && $in['company'] != '')
		{
			//show with '-' beacuse both exist
			$tmpCompany = $in['title'] ." - ". $in['company'];
		} else {
			//show without dash, 1 or less exist
			$tmpCompany = $in['title'].$in['company'];
		}	
		
		$xtpl=new XTemplate ("templates/contact_detail.xml");
		$xtpl->assign("tmpTitle",$tmpTitle);
		$xtpl->assign("company",$tmpCompany);
		
		$name = '';//$name is blank when inside a contact
		
		//Display phone numbers under current contact
		if (trim($in['office_phone']) != "")
		{
			$curphone = parse_phone($in['office_phone']);
			$number = return_dial($curphone,$in['sup_prefix_office']);
			$xtpl->assign("type",'Office');
			$xtpl->assign("number",$number);
			$xtpl->parse("main.dir_entry");
		}
		if (trim($in['home_phone']) != "")
		{
			$curphone = parse_phone($in['home_phone']);		
			$number = return_dial($curphone,$in['sup_prefix_home']);
			$xtpl->assign("type",'Home');
			$xtpl->assign("number",$number);
			$xtpl->parse("main.dir_entry");
		}
		if (trim($in['cell_phone']) != "")
		{
			$curphone = parse_phone($in['cell_phone']);		
			$number = return_dial($curphone,$in['sup_prefix_cell']);
			$xtpl->assign("type",'Cell');
			$xtpl->assign("number",$number);
			$xtpl->parse("main.dir_entry");
		}
		if (trim($in['custom_number']) != "")
		{
			$curphone = parse_phone($in['custom_number']);		
			$number = return_dial($curphone,$in['sup_prefix_custom']);
			$xtpl->assign("type",$in['custom_phone']);
			$xtpl->assign("number",$number);
			$xtpl->parse("main.dir_entry");
		}
		if (trim($in['other_phone']) != "")
		{
			$curphone = parse_phone($in['other_phone']);		
			$number = return_dial($curphone,$in['sup_prefix_other']);
			$xtpl->assign("type",'Other');
			$xtpl->assign("number",$number);
			$xtpl->parse("main.dir_entry");
		}
	}
	$xtpl->parse("main");
	$xtpl->out("main");

} else {
	// No specified Container, default to container 'Main'
	$inside = "0";
	list_objects ($inside,"Main",$MAC,$registered,$ob_sec,$ph_sec);
}
	
//	
//	
// Functions
//

//
function list_contacts ($member_cat,$style,$obID,$MAC)
{
	/*
		This function lists the contacts in specifed a category
		First the 'style' is read, which designates whether numbers are togther or seperate from the contact name
		if style is seperate...
			$per_page is set to limit the number contacts to each page.
			A count query is used to count how many contacts will be displayed.  If number of contacts is greater than 0
			then the title information and ID for each contact is fetched.
		if style is together...
			we currently have no way of adding a 'More' button to a directory page, so contacts are not counted
			and are truncated to 31 contacts per Category
		if total contacts == 0, then display prompt to add contacts
	*/
	global $db;
	global $URLBase;

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
	
	//Number of contacts to be displayed per page
	$countQuery = "SELECT
		COUNT(contacts.id) AS total
		FROM contacts
		WHERE contacts.member_of = '$obID'";
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
		//Items that match creteria exist, fetch data
		//
		$browseQuery = "SELECT 
			contacts.id AS id,
			contacts.display_name AS display_name,
			contacts.lname AS lname,
			contacts.fname AS fname,
			contacts.company AS company,
			contacts.office_phone AS office_phone,
			contacts.home_phone AS home_phone,
			contacts.cell_phone AS cell_phone,
			contacts.custom_phone AS custom_phone,
			contacts.custom_number AS custom_number,
			contacts.other_phone AS other_phone,
			contacts.sup_prefix_office AS sup_prefix_office,
			contacts.sup_prefix_cell AS sup_prefix_cell,
			contacts.sup_prefix_home AS sup_prefix_home,
			contacts.sup_prefix_other AS sup_prefix_other,
			contacts.sup_prefix_custom AS sup_prefix_custom,
			contacts.title AS title
			FROM contacts
			WHERE contacts.member_of = '$obID'
			ORDER BY contacts.display_name
			$limitstart";
		$theBrowseRES = mysql_query($browseQuery, $db);
	
		if ($style == 'Seperate')
		{
			//category is set to display phone numbers on a seperate screen
			$xtpl=new XTemplate ("templates/listcontacts_sep.xml");
			
			if ($remainingRows <= $per_page)
				{
					$prompt = ($start + 1) ." to ". ($start + $remainingRows) ." of ". $totalCount.".";
				} else {
					$prompt = ($start + 1) ." to ". ($start + $per_page) ." of ". $totalCount.".";
				}
				
			while ($in2 = mysql_fetch_assoc($theBrowseRES))
			{
				$tmpTitle = $in2['display_name'];
				
				$title = substr($tmpTitle,0,25);
				
				$ID = $in2['id'];
								
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
				$xtpl->assign("obID",$obID);
				$xtpl->assign("start",$start);
				$xtpl->parse("main.contact_more");
			}	

			$xtpl->assign("heading",$member_cat);
			$xtpl->assign("prompt",$prompt);
			$xtpl->parse("main");
			$xtpl->out("main");

		} else { //style "Together"
			
			//category is set to display phone numbers and names on the same screen
			$xtpl=new XTemplate ("templates/listcontacts_tog.xml");
			
			while ($in2 = mysql_fetch_assoc($theBrowseRES))
			{
				$tmpname = $in2['display_name'];
				
				if(substr($tmpname,0,16) != $tmpname)
				{
					//name does not fit
					$name = substr($tmpname,0,12)."...";
				} else {
					//name fits without editing
					$name = $tmpname;
				}

				if (trim($in2['office_phone']) != "")
				{
					$curphone = parse_phone($in2['office_phone']);
					$number = return_dial($curphone,$in2['sup_prefix_office']);
					$xtpl->assign("name",$name);
					$xtpl->assign("type",'Office');
					$xtpl->assign("number",$number);
					$xtpl->parse("main.dir_entry");
				}
				if (trim($in2['home_phone']) != "")
				{
					$curphone = parse_phone($in2['home_phone']);		
					$number = return_dial($curphone,$in2['sup_prefix_home']);
					$xtpl->assign("name",$name);
					$xtpl->assign("type",'Home');
					$xtpl->assign("number",$number);
					$xtpl->parse("main.dir_entry");
				}
				if (trim($in2['cell_phone']) != "")
				{
					$curphone = parse_phone($in2['cell_phone']);		
					$number = return_dial($curphone,$in2['sup_prefix_cell']);
					$xtpl->assign("name",$name);
					$xtpl->assign("type",'Cell');
					$xtpl->assign("number",$number);
					$xtpl->parse("main.dir_entry");
				}
				if (trim($in2['custom_number']) != "")
				{
					$curphone = parse_phone($in2['custom_number']);		
					$number = return_dial($curphone,$in2['sup_prefix_custom']);
					$xtpl->assign("name",$name);
					$xtpl->assign("type",substr($in2['custom_phone'],0,6));
					$xtpl->assign("number",$number);
					$xtpl->parse("main.dir_entry");
				}
				if (trim($in2['other_phone']) != "")
				{	
					$curphone = parse_phone($in2['other_phone']);		
					$number = return_dial($curphone,$in2['sup_prefix_other']);
					$xtpl->assign("name",$name);
					$xtpl->assign("type",'Other');
					$xtpl->assign("number",$number);
					$xtpl->parse("main.dir_entry");
				}
			}

			$xtpl->assign("heading",$member_cat);
			$xtpl->parse("main");
			$xtpl->out("main");
		}
	} else {
		//No contacts in container
		require_once "templates/img_empty_cat.php";
	}
}

function list_objects ($member_of,$title,$MAC,$registered,$ob_sec,$ph_sec)
{
	/*
		This function selects all of the objects in a given container and displays them to the user.
		The member_of id is what tells the qry what objects are in a container
		The total objects are counted so that pagination can be used to control how many objects per page.
		For a user to view an object, the user must have their phone registered, or the object must be public, or the
		global setting of object security must be turned off.
	
	*/
	global $db;
	global $URLBase;
	global $access_lvl;
	
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
		//User is not registered, apply security settings to show only 'Public' access 
		$object_sec_query = "AND object.access = 'Public'";
	} else {
		//Security settings are not in place, or user is registered, do not restrict to 'Public' access
		$object_sec_query = "";
	}
	
	$countQuery = "SELECT
		COUNT(object.id) AS total
		FROM object
		WHERE object.member_of = '$member_of'
		$object_sec_query";
	
	$theCountRES = mysql_query($countQuery, $db);
	//Fetch total items
	if ($in = mysql_fetch_assoc($theCountRES))
	{
		$totalCount = $in['total'];
	}
	
	//Calc remaining rows
	$remainingRows = ($totalCount - $start);
	
	//Qry
	$browseQuery = "SELECT
		object.id AS id,
		object.title AS title,
		object.href AS href,
		object.access AS access,
		object.type AS type
		FROM object
		WHERE object.member_of = '$member_of'
		$object_sec_query
		ORDER BY object.title
		$limitstart";
			
	if ($totalCount != '0')
	{	
		$theBrowseRES = mysql_query($browseQuery, $db);
		
		$xtpl=new XTemplate ("templates/listobjects.xml");
	
			while ($in2 = mysql_fetch_assoc($theBrowseRES))
			{	
				$tmpTitle = $in2['title'];
				$ob_title = substr($tmpTitle,0,25);
				
				if ($in2['type'] == "Link")
				{
					//under an object that is a 'Link' the href is put in the URL tags, not the $URLBase and object ID
					$HREF = $in2['href'];
					$xtpl->assign("title",$ob_title);
					$xtpl->assign("HREF",$HREF);
					$xtpl->parse("main.object_menu.HREF");
					
				} else {
					$xtpl->assign("title",$ob_title);
					$xtpl->assign("url_base",$URLBase);
					$xtpl->assign("MAC",$MAC);
					$xtpl->assign("ID",$in2['id']);
					$xtpl->parse("main.object_menu.ObURL");
				}
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
		//There are no objects in container
		require_once "templates/img_empty_cont.php";
	}
}

function parse_phone ($in_phone)
{
	//remove extraneous characters from phone number
	$chk_phone = trim($in_phone);
	$chkExp = "/(\-)|(\.)|(\()|(\))|(\ )/i"; 
	$out_phone = trim(preg_replace($chkExp, "", $chk_phone));
	return $out_phone;
}

function return_dial($phone,$suppress_prefix)
{
	/*
		This function is used to display the number and for each of the contacts different phones,
		The user is able to hit dial from this screen
		If in global settings prefix is checked, then the custom prefix will be attached to the number
	*/
	global $ph_prfx;
	
	if ($ph_prfx == 'Yes' && $suppress_prefix != '1')
	{
		//globals settings say to add prefix and suppress prefix is not selected fot this number
		global $prefix;
		$number = $prefix.$phone;
	} else {
		$number = $phone;
	}
	return $number;
}
?>
