<?php

// Get Global Preferences
$secQuery = "SELECT
global_pref.ph_sec AS ph_sec,
global_pref.ob_sec AS ob_sec,
global_pref.memo_ob AS memo_ob,
global_pref.prefix AS prefix,
global_pref.ph_prfx AS ph_prfx
FROM global_pref
WHERE global_pref.preference = 'primary'";
$thesecRES = mysql_query($secQuery, $db);

if ($sy = mysql_fetch_assoc($thesecRES))
{
	//set global preferences
	$ph_sec = $sy['ph_sec'];
	$ob_sec = $sy['ob_sec'];
	$ph_prfx = $sy['ph_prfx'];
	$prefix = $sy['prefix'];
	$memo_ob = $sy['memo_ob'];
} else {
	//security defaults to 'yes'
	$ph_sec = 'Yes';
	$ob_sec = 'Yes';
	$ph_prfx = 'No';
}

if (try_get('name'))
{
	// Get MAC Address
	$MAC = defang_input(try_get('name'));
	
	// SQL to check MAC
	$macQuery = "SELECT
	phone.id AS phone_id,
	phone.access_lvl AS access_lvl
	FROM phone
	WHERE phone.MAC = '$MAC'
	AND phone.access_lvl != 'unknown'";
	$themacRES = mysql_query($macQuery, $db);
	
	if ($mc = mysql_fetch_assoc($themacRES))
	{
		if ($mc['access_lvl'] != "")
		{
			//MAC was found as a registered phone
			$registered = 'TRUE';
			$access_lvl = $mc['access_lvl'];
		} else {
			//Access Level of MAC was not defined
			$registered = 'FALSE';
			$access_lvl = "Restricted";
		}
	} else {
		//Qry Fail
		$registered = 'FALSE';
		$access_lvl = "Restricted";
	}
} else {
	//MAC not found in database
	$registered = 'FALSE';
	$access_lvl = "Restricted";
}
?>
