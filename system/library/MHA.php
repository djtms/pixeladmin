<?php

/*
* Author: Mehmet Hazar Artuner
* WebPage: www.hazarartuner.com
* Version: 1.4
* Release Date: 12.11.2011
*/

function cropString($text,$limit)
{
	$stringArray = explode(" ",$text);
	$limitExceed = false;
	
	foreach($stringArray as $s)
	{
		if(strlen($newString . $s) < $limit)
			$newString .= $s . ' ';
		else
		{
			$limitExceed = true;
			break;
		}
	}
	
	$newString = substr($newString,0,-1) . ($limitExceed ? "..." : "");
	
	return $newString;
}

function stripTagsInArray(&$array = array())
{
	foreach($array as $key=>$val)
	{
		$array[$key] = strip_tags($val);
	}
}

function randomString($length = 6,$charset = null)
{
	$defaultCharset = 'abcdefghijklmnopqrstuvwxyz>#${[]}|@!^+%&()=*?_-1234567890';
	
	$charset = $charset == null ? $defaultCharset : $charset;
	
	$randomString = '';
	
	for($i = 0; $i<$length; $i++)
	{
		$rnd = rand(0,strlen($charset));
		$randomString .= substr($charset,$rnd,1);
	}
	
	return $randomString;
}

function currentDateTime($type = "datetime")
{
	if($type == "date")	
		return date("Y-m-d",time());
	if($type == "time")	
		return date("H:i:s",time());
	if($type == "datetime")	
		return date("Y-m-d H:i:s",time());
}

function generatePager($link,$page,$eachPageItemCount,$totalItemCount,$pagerVisibleButtonsCount = 5)
{
	$pageCount = ceil($totalItemCount / $eachPageItemCount);
	$itemHtml = '<a href="{%link%}">{%page%}</a>';
	$selectedItemHtml = '<span>{%page%}</span>';
	$pagerHtml = '';
	
	if($pageCount > 1)
	{
		// Calculate Page Numbers List
		/******************************************************************/
			$pagerVisibleButtonsCount--;
			$add = ceil($pagerVisibleButtonsCount/2);
			
			if($pagerVisibleButtonsCount < $pageCount)
			{
				$startIndex = $page<=$add ? 1 : $page - $add;
				
				if(($startIndex + $pagerVisibleButtonsCount) > $pageCount)
				{
					$startIndex = $startIndex - (($startIndex + $pagerVisibleButtonsCount) - $pageCount);
				}
				
				
				$endIndex = $startIndex + $pagerVisibleButtonsCount;
			}
			else
			{
				$startIndex = 1;
				$endIndex = $pageCount;
			}
		/******************************************************************/
		for($i=$startIndex; $i<=$endIndex; $i++)
		{
			if($i == $page)
			{
				$pagerHtml .= preg_replace('/\{\%page\%\}/',$i,$selectedItemHtml); 
			}
			else
			{
				$item = preg_replace('/\{\%page\%\}/',$i,$link);
				$item = preg_replace('/\{\%link\%\}/', $item, $itemHtml);
				$pagerHtml .= preg_replace('/\{\%page\%\}/',$i,$item);
			}
		}
	}
		
	return $pagerHtml;
}


function getDeepUrl($foldername)
{
	/// Derinlik sayısını hesapla
	$currentDirectory = dirname($_SERVER["SCRIPT_NAME"]);
	$parentFoldersInDir = explode("/",$currentDirectory);
	$deepCount = 0;
	$deepUrl = "";
	
	foreach($parentFoldersInDir as $PF)
	{
		if(trim($PF) != "" && $PF != null)
		{
			$deepCount += 1;
		}
	}
	
	// Derinlik url sini hesapla
	for($i=0; $i<$deepCount; $i++) 
	{										
		if(!is_dir($deepUrl . $foldername . "/"))
			$deepUrl .= '../';
		else
			break;
	}
	
	return $deepUrl . $foldername . "/";
}

function fixStringForWeb($string)
{
	$look = array("/\İ/","/\ı/","/\Ü/","/\ü/","/\Ö/","/\ö/","/\Ğ/","/\ğ/","/\Ş/","/\ş/","/\Ç/","/\ç/","/\s/");
	$replace = array("I","i","U","u","O","o","G","g","S","s","C","c","-");;
	
	return preg_replace($look, $replace, $string);
}