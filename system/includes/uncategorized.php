<?php

if($_POST["admin_action"] == "getUniqueId")
{
	print_r($_POST);
	$prefix = isset($_POST["prefix"]) ? $_POST["prefix"] : null;
	$more_entropy = isset($_POST["more_entropy"]) ? $_POST["more_entropy"] : null;
	
	echo json_encode(array("success"=>true, "uniqid"=>uniqid($prefix, $more_entropy)));
	exit;
}