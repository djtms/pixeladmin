<?php

function get_option($option_name)
{
	global $DB;
	
	$o = $DB->get_row("SELECT * FROM {$DB->tables->option} WHERE option_name=?",array($option_name));
	
	switch($o->data_type)
	{
		default:
		case "unknown type":
		case "string":			$o->option_value = (string)$o->option_value;				break;
		case "boolean":			$o->option_value = (boolean)$o->option_value;				break;
		case "integer":			$o->option_value = (integer)$o->option_value;				break;
		case "double":			$o->option_value = (double)$o->option_value;				break;
		case "object":			$o->option_value = json_decode($o->option_value);			break;
		case "array":			$o->option_value = json_decode($o->option_value, true);		break;
		case "NULL":			$o->option_value = null;									break;
	};
	
	return $o->option_value;
}
	
function set_option($option_name, $option_value, $group_name="")
{
	global $DB;
	
	
	$data_type = gettype($option_value);
	
	if(($data_type == "object") || ($data_type == "array"))
	{
		$option_value = json_encode($option_value);
	}
	
	if($DB->get_value("SELECT COUNT(option_value) FROM {$DB->tables->option} WHERE option_name=?",array($option_name)) > 0)
		return $DB->execute("UPDATE {$DB->tables->option} SET option_value=?, group_name=?, data_type=? WHERE option_name=?",array($option_value, $group_name, $data_type, $option_name));
	else
		return $DB->insert($DB->tables->option,array("option_name"=>$option_name,"option_value"=>$option_value,"group_name"=>$group_name, "data_type"=>$data_type));
}

function delete_option($option_name)
{
	global $DB;

	return $DB->execute("DELETE FROM {$DB->tables->option} WHERE option_name='{$option_name}'");
}

function get_optiongroup($group_name)
{
	global $DB;
	
	if($options = $DB->get_rows("SELECT option_name, option_value, data_type FROM {$DB->tables->option} WHERE group_name=?",array($group_name),FETCH_OBJ))
	{
		foreach($options as &$o)
		{
			switch($o->data_type)
			{
				case "boolean":			$o->option_value = (boolean)$o->option_value;				break;
				case "integer":			$o->option_value = (integer)$o->option_value;				break;
				case "double":			$o->option_value = (double)$o->option_value;				break;
				case "string":			$o->option_value = (string)$o->option_value;				break;
				case "object":			$o->option_value = json_decode($o->option_value);			break;
				case "array":			$o->option_value = json_decode($o->option_value, true);		break;
				case "NULL":			$o->option_value = null;									break;
				case "unknown type":	$o->option_value = (string)$o->option_value;				break;
			};
		}
		
		return $options;
	}
	else
		return false;
}

function delete_optiongroup($group_name)
{
	global $DB;
	
	return $DB->execute("DELETE FROM {$DB->tables->option} WHERE group_name='{$group_name}'");
}