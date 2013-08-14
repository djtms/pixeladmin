<?php

class PA_VALIDATE
{
	function customValidate($string, $regExpression)
	{
		return preg_match($regExpression, $string);
	}
	
	function validateName($string)
	{
		return preg_match('/^[a-z\sıİüÜşŞğĞçÇöÖ]+$/i',$string) && (strlen($string) > 1);
	}
	
	function validatePhone($string)
	{
		return preg_match('/^\+?[0-9\s]+$/i',$string);
	}
	
	function validateEmail($string)
	{
		return filter_var($string, FILTER_VALIDATE_EMAIL);
	}
	
	function validateFileFormat($fileFullName,$formats = array("docx","doc","pdf","jpeg","jpg","png","gif"))
	{
		$file = explode('.',$fileFullName);
		$filePartsCount = sizeof($file);
		if($filePartsCount<=1)
		{
			return false;
		}
		
		$extension = $file[$filePartsCount - 1];
		$matched = false;
		
		foreach($formats as $f)
		{
			if(preg_match("/^" . $f . "$/i", $extension))
			{
				$matched = true;
				break;
			}
		}
		
		return $matched;
	}
}
