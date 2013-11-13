<?php

if(isset($_POST["admin_action"]))
{
	switch($_POST["admin_action"])
	{
		case "checkemail": checkUserEmail(); exit;
		case "deleteuser": deleteUser(); exit;
		case "checkusername": checkUsername(); exit;
	}
}

function checkUserEmail()
{
	global $ADMIN;
	if($ADMIN->USER->getUserByEmail($_POST["email"]))
		echo json_encode(array("error"=>"true","message"=>"Bu \"E-Posta\" kullanımda! "));
	else
		echo json_encode(array("error"=>"false","message"=>"Uygun"));
}

function deleteUser()
{
	global $ADMIN;
	
	if($ADMIN->USER->deleteUser($_POST["userId"]))
		echo json_encode(array("error"=>false));
	else
		echo json_encode(array("error"=>true));
}

function checkUsername()
{
	global $ADMIN;
	if($ADMIN->USER->getUserByUsername($_POST["username"]))
		echo json_encode(array("error"=>true,"message"=>"Bu \"Kullanıcı Adı\" kullanımda! "));
	else
		echo json_encode(array("error"=>false,"message"=>"Uygun"));
}