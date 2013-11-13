<?php

function validateName($name)
{
	global $ADMIN;
	
	return $ADMIN->VALIDATE->validateName($name);
}

function validateEmail($email)
{
	global $ADMIN;
	
	return $ADMIN->VALIDATE->validateEmail($email);
}

function validatePhone($phone)
{
	global $ADMIN;
	
	return $ADMIN->VALIDATE->validatePhone($phone);
}

function validateFileFormat($filename, $formats = array("docx","doc","pdf","jpeg","jpg","png","gif"))
{
	global $ADMIN;
	
	return $ADMIN->VALIDATE->validateFileFormat($filename,$formats);
}
