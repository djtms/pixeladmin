<?php

function moduleActivationCode($filedir,$function)
{
	$directory = dirname($filedir);
	$directory = str_replace("\\","/",$directory);
	$lastChar = substr($directory,-1);
	$directory .= $lastChar == "/" ? "" : "/";
	
	global $register_module_function;
	$register_module_function = $function;	
}

function executeActivationCode($directory)
{
	global $register_module_function;
	global $modules_main_file_name;
	
	$moduleFileUrl = $directory . $modules_main_file_name;
	
	if(file_exists($moduleFileUrl)){
		require_once $moduleFileUrl;
		call_user_func($register_module_function);
	}
}

$active_modules = get_option("admin_active_modules");
$active_modules = (substr($active_modules,-1) == ',') ? substr($active_modules,0,-1) : $active_modules;

$modulesArray = explode(',',$active_modules);
global $modules_main_file_name;

foreach($modulesArray as $m)
{
	$moduleFileUrl = dirname(__FILE__) . "/../../" . $m . $modules_main_file_name;

	if(file_exists($moduleFileUrl))
	{
		require_once $moduleFileUrl;
	}
}
