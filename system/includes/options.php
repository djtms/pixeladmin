<?php

function get_option($optionName)
{
	global $DB;
	
	return $DB->get_value("SELECT optionValue FROM {$DB->tables->option} WHERE optionName=?",array($optionName));
}
	
function set_option($optionName,$optionValue,$groupName="")
{
	global $DB;
	
	if($DB->get_value("SELECT COUNT(optionValue) FROM {$DB->tables->option} WHERE optionName=?",array($optionName)) > 0)
		return $DB->execute("UPDATE {$DB->tables->option} SET optionValue=?,groupName=? WHERE optionName=?",array($optionValue,$groupName,$optionName));
	else
		return $DB->insert($DB->tables->option,array("optionName"=>$optionName,"optionValue"=>$optionValue,"groupName"=>$groupName));
}

function delete_option($optionName)
{
	global $DB;

	return $DB->execute("DELETE FROM {$DB->tables->option} WHERE optionName='{$optionName}'");
}

function get_optiongroup($groupName)
{
	global $DB;
	
	return (object)$DB->get_rows("SELECT optionName,optionValue FROM {$DB->tables->option} WHERE groupName=?",array($groupName),FETCH_KEY_PAIR );
}

function delete_optiongroup($groupName)
{
	global $DB;
	
	return $DB->execute("DELETE FROM {$DB->tables->option} WHERE groupName='{$groupName}'");
}