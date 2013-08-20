<?php

/*
* Author: Mehmet Hazar Artuner
* WebPage: www.hazarartuner.com
* Version: 1.4
* Release Date: 17.12.2012
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
	
	$newString = preg_replace("/(\,|\;|\.|\:) $/", "", $newString);
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
		$rnd = rand(0,(strlen($charset) - 1));
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

function generatePager($link, $page, $eachPageItemCount, $totalItemCount, $pagerVisibleButtonsCount = 5, $display_first_last_buttons = false, $list_type = "link", $labels = array("first"=>"ilk sayfa", "last"=>"son sayfa"))
{
	$pageCount = ceil($totalItemCount / $eachPageItemCount);
	if($list_type == "link"){
        $itemHtml = '<a href="{%link%}">{%page%}</a>';
        $selectedItemHtml = '<span>{%page%}</span>';
    }
    else if($list_type == "selectbox"){
        $itemHtml = '<option value="{%link%}">{%page%}</option>';
        $selectedItemHtml = '<option value="{%link%}" selected="selected">{%page%}</option>';
    }


	if($pageCount > 1){
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
			if($i == $page){
                if($list_type == "link"){
				    $pagerHtml .= preg_replace('/\{\%page\%\}/',$i,$selectedItemHtml);
                }
                else if($list_type == "selectbox"){
                    $item = preg_replace('/\{\%page\%\}/',$i,$link);
                    $item = preg_replace('/\{\%link\%\}/', $item, $selectedItemHtml);
                    $pagerHtml .= preg_replace('/\{\%page\%\}/',$i,$item);
                }
			}
			else
			{
				$item = preg_replace('/\{\%page\%\}/',$i,$link);
				$item = preg_replace('/\{\%link\%\}/', $item, $itemHtml);
				$pagerHtml .= preg_replace('/\{\%page\%\}/',$i,$item);
			}
		}

        // ilk sayfa, son sayfa degerlerini hesapla
        if($display_first_last_buttons){
            $first_page = 1;
            $last_page = $pageCount;

            if($list_type == "link"){
                // First Page
                $item = preg_replace('/\{\%page\%\}/',$first_page, $link);
                $item = preg_replace('/\{\%link\%\}/', $item, $itemHtml);
                $pagerHtml = preg_replace('/\{\%page\%\}/', $labels["first"], $item) . $pagerHtml;

                // Last Page
                $item = preg_replace('/\{\%page\%\}/',$last_page, $link);
                $item = preg_replace('/\{\%link\%\}/', $item, $itemHtml);
                $pagerHtml .= preg_replace('/\{\%page\%\}/', $labels["last"], $item);
            }
            else if($list_type == "selectbox"){
                // Last Page
                $item = preg_replace('/\{\%page\%\}/',$last_page, $link);
                $item = preg_replace('/\{\%link\%\}/', $item, $itemHtml);
                $pagerHtml = preg_replace('/\{\%page\%\}/', $labels["last"], $item) . $pagerHtml;

                // First Page
                $item = preg_replace('/\{\%page\%\}/',$first_page, $link);
                $item = preg_replace('/\{\%link\%\}/', $item, $itemHtml);
                $pagerHtml = preg_replace('/\{\%page\%\}/', $labels["first"], $item) . $pagerHtml;

            }
        }
        //---------------------------------------------------------------------
	}

    if($list_type == "link"){
        $return = in_admin ? '<div class="pagerOuter">' : '';
        $return .= $pagerHtml;
        $return .= in_admin ? '</div>' : '';
    }
    else if($list_type == "selectbox"){
        $return = '<select class="pagerOuter">';
        $return .= $pagerHtml;
        $return .= '</select>';
    }


	return $return;
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

function fixStringForWeb($string){
	$look = array("/\İ/","/\ı/","/\Ü/","/\ü/","/\Ö/","/\ö/","/\Ğ/","/\ğ/","/\Ş/","/\ş/","/\Ç/","/\ç/","/\s/","/\!/","/\*/");
	$replace = array("I","i","U","u","O","o","G","g","S","s","C","c","-","-","-");
	
	return preg_replace($look, $replace, $string);
}

/**
*
* mysql desenine uygun stringlerin kullanarak şimdiki zamana göre yaş hesabı yapar
* @param string $birthday
* @return number
*/
function calculateAge($birthday){
	return floor((time() - strtotime($birthday)) / 31536000);
}

/**
*
* mysql desenine uygun stringlerin kullanarak şimdiki zamana göre ay hesabı yapar
* @param string $birthday
* @return number
*/
function calculateMonthAmount($birthday){
	return round((time() - strtotime($birthday)) / 2592000);
}

/**
 *
 * Html stringi içindeki {%keyword%} şeklindeki desene uygun stringleri  $values array'i içindeki eşleşen değişkenlerle değiştirir.
 * @param string $html_string
 * @param array $values
 */

function renderHtml($html_string, $values = array())
{
	preg_match_all("/\{\%([a-z0-9_=\-]+)\%\}/i", $html_string, $matches);
	$match_count = sizeof($matches[0]);
	$isValuesTypeArray = is_array($values) ? true : false;
	
	for($i=0; $i<$match_count; $i++)
	{
		$pattern = "/" . preg_quote($matches[0][$i]) . "/";
		$key = $matches[1][$i];
		$value = $isValuesTypeArray ? $values[$key] : $values->{$key};
		
		if(preg_match("/=/", $key))
		{
			$explodedKey = explode("=", $key);
			
			if($explodedKey[0] == "i18n"){
				$i18nCode = $isValuesTypeArray ? $values[$explodedKey[1]] : $values->{$explodedKey[1]};
				$value = getI18n($i18nCode);	
			}
            else if($explodedKey[0] == "file"){
                $file_id = $isValuesTypeArray ? $values[$explodedKey[1]] : $values->{$explodedKey[1]};
                $value = "<img src='" . getThumbImage($file_id, 100, 75, false) . "' />";
            }
		}
		
		$html_string = preg_replace($pattern, $value, $html_string);
	}

	return $html_string;
}

/**
 * 
 * Html tipindeki string içinden istediğimiz elementlerin istediğimiz attribute'unu kaldırmamıza yarar.
 * Varsayılan olarak tüm elementlerin tüm attribute larını kaldırır, ama bunu kendimiz belirlemek istersek gerekli isimleri eğer birden çoksa virgül (,)
 * ile ayırarak kulllanmalıyız. Mesela elementleri belirtirken "p,span,ul" şeklinde olabilir, aynı şekilde attribute belirtirkende "style,type,readonly"
 * şeklinde kullanılabilir.
 * @param string $html_content
 * @param string $attribute
 * @param string $html_filter
 * @return string
 */

function removeHtmlAttribute($html_content, $attribute_fiter = "all", $html_filter = "all")
{
	if($html_filter == "all")
	{
		$tagname_pattern = "[a-z0-9]";
	}
	else
	{
		$filter_array = preg_split("/,/", $html_filter);
		$filter_count = sizeof($filter_array);
		$tagname_pattern  = "(";
		for($i=0; $i<$filter_count; $i++)
		{
			$tagname_pattern .= trim($filter_array[$i]) . "|";
		}
		$tagname_pattern  = substr($tagname_pattern, 0, -1);
		$tagname_pattern .= ")";
	}
	
	if($attribute_fiter == "all")
	{
		$attribute_pattern = "[a-z0-9_-]+";
	}
	else
	{
		$attribute_array = preg_split("/,/", $attribute_fiter);
		$attribute_count = sizeof($attribute_array);
		
		$attribute_pattern  = $attribute_count > 1 ? "(" : "";
		for($i=0; $i<$attribute_count; $i++)
		{
			$attribute_pattern .= trim($attribute_array[$i]) . "|";
		}
		$attribute_pattern  = substr($attribute_pattern, 0, -1);
		$attribute_pattern .= $attribute_count > 1 ? ")" : "";
		
	}
	
	$pattern = "/<{$tagname_pattern}+.*?({$attribute_pattern} *= *(\"|\').*?(\\2)).*?(>|\/>)/i";
	
	return preg_replace($pattern, "", $html_content);
}