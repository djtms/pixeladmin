<?php

$allowed_pages_if_logged = array("admin","pa-ajax","lookfile");
$allowed_pages_if_not_logged = array("login","resetpassword","newpassword", "complete_registration");
	

if(in_admin):
	global $common_admin_site;
	
	if($common_admin_site != "")
	{
		header("Location:{$common_admin_site}/admin/");
		exit;
	}
	
	
	if($_GET["admin_action"] == "logout")
	{
		add_log("çıkış yaptı");
		$ADMIN->USER->logout();
	}
	
	$page = basename($_SERVER["SCRIPT_NAME"],".php");

	if($ADMIN->USER->loggedInUser)
	{
		$allowed = false;
		foreach($allowed_pages_if_logged as $ap)
		{
			if($page == $ap)
				$allowed = true;
		}
		
		if(!$allowed)
		{
			header("Location:admin.php?page=dashboard");
			exit;
		}
	}
	else
	{
		$allowed = false;
		foreach($allowed_pages_if_not_logged as $ap)
		{
			if($page == $ap)
				$allowed = true;
		}
		
		if(!$allowed)
		{
			header("Location:login.php");
			exit;
		}
	}
endif;

