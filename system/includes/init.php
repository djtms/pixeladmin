<?php
$admin_folder_name = 'admin';
define("in_admin", in_admin());

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