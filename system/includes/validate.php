<?php

function validateName($name)
{
	global $MODEL;
	
	return $MODEL->VALIDATE->validateName($name);
}

function validateEmail($email)
{
	global $MODEL;
	
	return $MODEL->VALIDATE->validateEmail($email);
}

function validatePhone($phone)
{
	global $MODEL;
	
	return $MODEL->VALIDATE->validatePhone($phone);
}

function validateFileFormat($filename, $formats = array("docx","doc","pdf","jpeg","jpg","png","gif"))
{
	global $MODEL;
	
	return $MODEL->VALIDATE->validateFileFormat($filename,$formats);
}
