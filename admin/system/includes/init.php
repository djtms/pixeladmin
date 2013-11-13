<?php
$admin_folder_name = 'admin';
define("in_admin", in_admin());

// Undo Magic Quotes Addings
if(in_admin && get_magic_quotes_gpc())
{
	$_GET = undo_magic_quotes_array($_GET);
	$_POST = undo_magic_quotes_array($_POST);
}

function in_admin()
{
	global $admin_folder_name;
	
	$basename = basename(dirname($_SERVER["SCRIPT_NAME"]));
	$allowed_dirs = array($admin_folder_name,"includes");
	
	foreach($allowed_dirs as $d)
	{
		if($basename == $d)
			return true;
	}
	
	return false;
}

function undo_magic_quotes_array($array)
{
    return is_array($array) ? array_map('undo_magic_quotes_array', $array) : str_replace("\\'", "'", str_replace("\\\"", "\"", str_replace("\\\\", "\\", str_replace("\\\x00", "\x00", $array))));
}