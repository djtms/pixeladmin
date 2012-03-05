<?php
registerAjaxCall("checkemail","checkUserEmail");
registerAjaxCall("deleteuser","deleteUser");
registerAjaxCall("checkusername","checkUsername");

function checkUserEmail()
{
	global $MODEL;
	if($MODEL->USER->getUserByEmail($_POST["email"]))
		echo json_encode(array("error"=>"true","message"=>"Bu \"E-Posta\" kullanımda! "));
	else
		echo json_encode(array("error"=>"false","message"=>"Uygun"));
}

function deleteUser()
{
	global $MODEL;
	
	if($MODEL->USER->deleteUser($_POST["userId"]))
		echo json_encode(array("error"=>false));
	else
		echo json_encode(array("error"=>true));
}

function checkUsername()
{
	global $MODEL;
	if($MODEL->USER->getUserByUsername($_POST["username"]))
		echo json_encode(array("error"=>true,"message"=>"Bu \"Kullanıcı Adı\" kullanımda! "));
	else
		echo json_encode(array("error"=>false,"message"=>"Uygun"));
}