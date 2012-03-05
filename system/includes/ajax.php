<?php
function registerAjaxCall($actionName,$functionName)
{
	global $ajax_functions;
	
	$ajax_functions[] = array("action"=>$actionName,"function"=>$functionName);
}

function executeAjaxCall($actionName)
{
	global $ajax_functions;
	
	foreach($ajax_functions as $a)
	{
		if($actionName == $a["action"])
		{
			call_user_func($a["function"]);
		}
	}
	set_option("postMessage","");
	exit;
}