<?php
/*

		utils.php
		
		Joe Hopkins <joe@csma.biz>
		Copyright (c) 2005, McFadden Associates.  All rights reserved.
*/
function defang_input ($input)
{
	// Remove harmful SQL words and characters from inputs.
	$chkExp = "(;)|(select)|(insert)|(update)|(delete)|(drop)|(')";
	$output = trim(eregi_replace($chkExp, "", $input));
	
	$output = trim(eregi_replace("&amp;", "&", $output));
	$output = trim(eregi_replace("&", "&amp;", $output));
	return $output;
}


/**
 * A temporary method of generating GUIDs of the correct format for our DB.
 * @return String contianing a GUID in the format: aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee
 *
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
*/
function create_guid()
{
    $microTime = microtime();
        list($a_dec, $a_sec) = explode(" ", $microTime);

        $dec_hex = sprintf("%x", $a_dec* 1000000);
        $sec_hex = sprintf("%x", $a_sec);

        ensure_length($dec_hex, 5);
        ensure_length($sec_hex, 6);

        $guid = "";
        $guid .= $dec_hex;
        $guid .= create_guid_section(3);
        $guid .= '-';
        $guid .= create_guid_section(5);
        $guid .= '-';
        $guid .= create_guid_section(5);
        $guid .= '-';
        $guid .= create_guid_section(4);
        $guid .= '-';
        $guid .= $sec_hex;
        $guid .= create_guid_section(4);

        return $guid;

}

function create_guid_section($characters)
{
        $return = "";
        for($i=0; $i<$characters; $i++)
        {
                $return .= sprintf("%x", mt_rand(0,15));
        }
        return $return;
}

function ensure_length(&$string, $length)
{
        $strlen = strlen($string);
        if($strlen < $length)
        {
                $string = str_pad($string,$length,"0");
        }
        else if($strlen > $length)
        {
                $string = substr($string, 0, $length);
        }
}

?>
