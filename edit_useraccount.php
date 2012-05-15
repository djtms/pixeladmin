<?php

if($_GET["user"] > 0)
{
	$user_id = $_GET["user"];
	$useraccount = $ADMIN->USER->getUserById($user_id);
	setGlobal("useraccount", $useraccount);
	$edit_useraccount->render();
}
else
{
	postMessage("Kullanıcı bulunamadı!", true);
	header("Location:admin.php?page=useraaccounts");
	exit;
}

