<?php

/**
 * 
 * Array list şeklindeki dataları görsel olarak grid sistemiyle listeler ve istendiğinde çeşitli özellikler ekler.
 * @param array $data  listelenecek data dizisi
 * @param string $gridTitle bu grid için görünmesini istediğiniz başlık
 * @param string $gridId grid için kullanılacak benzersiz bir id adı. benzersiz olduğu müddetçe her ilk harfi harf olmak üzere isim olabilir
 * @param string $rowTitleQuery griddeki her satırın nasıl göstereceğini ifade eden query
 * @param string $addDataLink grid'e bağlı olan dataya yenisini eklemek için kullanılacak sayfa linki
 * @param string $editDataLinkQuery griddeki datayı düzenlemek için kullanılacak sayfa linkini ifade eden query
 * @param string $deleteDataLinkQuery griddeki datayı silmek için kullanılacak sayfa linkini ifade eden query
 * @param string $key_column_name griddeki satırları sıralamak için her bir satırdaki elemanı temsil eden unique değişkenin column adı
 * @param string $order_column_name_or_sort_function_name sıralama işlemi için kullanılan sıra numarasın ifade eden column adı veya sıralama işlemini yapacak fonksiyon adı. bu ikisinden hangisi olacağını bir sonraki parametrenin null olup olmaması belirler.
 * @param string $table_name sıralama işlemi için kullanılacak tablonun adı.
 * @return string işlem sonucunda oluşan dataGrid'i döndürür.
 */
function dataGrid($data, $gridTitle, $gridId, $rowTitleQuery, $addDataLink, $editDataLinkQuery, $deleteDataLinkQuery, $key_column_name = null, $order_column_name_or_sort_function_name = null, $table_name = null)
{
	// sıralama işlemi için ajax ve jqueryui bağlamasını yapıyoruz
	if(($key_column_name != null) && ($order_column_name_or_sort_function_name != null))
	{
		if($_POST["admin_action"] == "sortDataGrid_$gridId")
		{
			global $DB;
			$fixed_array = array();
			$orderList = $_POST["order"];
			
			foreach ($orderList as $order=>$key)
			{
				// Eğer tablo ismi atanmışsa database deki güncellemeyi biz otomatik yapıyoruz.
				if($table_name != null)
				{
					$DB->execute("UPDATE {$table_name} SET {$order_column_name_or_sort_function_name}=? WHERE {$key_column_name}=?", array($order, $key));
				}
				else // Eğer tablo ismi atanmamışsa, değerleri uygun formatta bir array'de topluyoruz.
				{
					$fixed_array[] = (object) array("key"=>$key,"order"=>$order);
				}
			}
			
			// Eğertablo ismi atanmamışsa, ismi yazılan fonksiyona yukarıda düzenlediğimiz array'i argüman olarak atayıp fonksiyonu çalıştırıyoruz.
			if(($table_name === null) && ($order_column_name_or_sort_function_name($fixed_array) === false))
				echo json_encode(array("error"=>true));
			else
				echo json_encode(array("error"=>false));
			
			exit;
		}
	}

	$addDataLink = $addDataLink != null ? '<button class="dataGridAddButton" page="' . (preg_match("/\.php\?/", $addDataLink) ? $addDataLink : "admin.php?" . $addDataLink) . '">Yeni Ekle</button>' : "";
	if($key_column_name != null)
	{
		$sortableClass = "sortableList";
		$sortableEvent = "sort_event='{$order_column_name_or_sort_function_name}'";
	}
	
	$dataCount = sizeof($data);
	$gridItemsHtml = "";
		
	if(is_array($data) && $dataCount > 0)
	{
        $is_image_grid = preg_match('/\{\%file\=.*?\%\}/', $rowTitleQuery) ? true : false;

		$use_edit_button = (($editDataLinkQuery != null) && (strlen(trim($editDataLinkQuery)) > 0)) ? true : false;
		$use_cross_button = (($deleteDataLinkQuery != null) && (strlen(trim($deleteDataLinkQuery)) > 0)) ? true : false;
		
		$edit_button_cleared_link = preg_match("/\.php\?/", $editDataLinkQuery) ? false : true;
		$cross_button_cleared_link = preg_match("/\.php\?/", $deleteDataLinkQuery) ? false : true;
		
		for($i=0; $i<$dataCount;  $i++)
		{
			$data[$i] = (object) $data[$i];
			$data[$i]->__index__ = $i;
			$gridItemsHtml .= "<li " . ($key_column_name != null ? " id='order_" . $data[$i]->{$key_column_name} . "' "  : "") . ($is_image_grid ? ' class="image_grid_item" ' : '') . ">";
			$gridItemsHtml .= "<div class='item'>";
			$gridItemsHtml .= "<p class='text'>" . renderHtml($rowTitleQuery, $data[$i]) . '</p>';
			
			if($use_edit_button || $use_cross_button)
			{
				$gridItemsHtml .= "<div class='rowEditButtonsOuter'>";
				if($use_cross_button)
				{
					$deleteLink = renderHtml($deleteDataLinkQuery, $data[$i]);
					
					$gridItemsHtml .=  "<a class='crossBtn' href='";	
					$gridItemsHtml .= ($cross_button_cleared_link ? "admin.php?" . $deleteLink : $deleteLink);			
					$gridItemsHtml .= "' onclick='return false;'><img src='view/images/transparentLoader.gif' /></a>";
				}

				if($use_edit_button)
				{
					$editLink = renderHtml($editDataLinkQuery, $data[$i]);
					
					$gridItemsHtml .="<a href='";
					$gridItemsHtml .= ($edit_button_cleared_link ? "admin.php?" . $editLink : $editLink);
					$gridItemsHtml .= "' class='editBtn'></a>";
				}
				
				$gridItemsHtml .= "</div>";
			}
			
			$gridItemsHtml .= "</div>";
            if(is_array($data[$i]->sub)){
                $gridItemsHtml .= dataGrid($data[$i]->sub, "", $gridId, $rowTitleQuery, null, $editDataLinkQuery, $deleteDataLinkQuery, $key_column_name, $order_column_name_or_sort_function_name, $table_name);
            }
            $gridItemsHtml .= "</li>";
		}
	}
	else
	{
		$gridItemsHtml .= "<li><div class='item'><p class='text' style='color:#e00 !important;'>Kayıt Bulunamadı!</p></div></li>";
	}
	
	$template = file_get_contents(GUI_TEMPLATES_DIR . "datagrid/datagrid.html");
	return renderHtml($template, array("gridId"=>$gridId, "gridTitle"=>$gridTitle, "addDataLink"=>$addDataLink, "sortableClass"=>$sortableClass, "sortableEvent"=>$sortableEvent, "gridItemsHtml"=>$gridItemsHtml));
}


function postMessage($message, $error=false)
{
	global $master;
	set_option("admin_postMessage",'<p ' . ($error ? ' style="color:#fc5900;" ' : '') . ' >' . $message . '</p>');
}

function fileGrid($files, $gridId, $visibleEditButtons = "all", $rowCount=1, $columnCount=1, $appendExtraHtmlData = "")
{
	$filesNameKey = (strlen($gridId) > 0) ? $gridId . "[]" : ("fileGrid_" . uniqid() . "[]"); // listedeki dosyaların "input" elementlerinin "name" değeri
	$template = file_get_contents(GUI_TEMPLATES_DIR . "filegrid/filegrid.html");
	$itemsList = "";
	$showAllButtons = false; // Tüm butonları kullanıp kullanmaması gerektiğini belirten değişken.
	$visibleButtonTypes = new stdClass(); // Kullanıcının atadığı değere göre gösterilmesi istenen buton tiplerinin listesini tutan değişken.
	$appendExtraHtml = false; // Kullanıcının ekstra html ekleyip eklemediğini belirten değişken.
	$requestedColumnNames = array(); // Kullanıcının (eğer eklediyse) eklediği html data içinde tanımlanmış column adlarının listesini tutan değişken.
	$requestedColumnsCount = 0;
	
	// Edit'leme Butonlarının gösterilip gösterilmemesini kontrol et-----------------------------------------------
	if(in_array($visibleEditButtons, array("", null, false)))
	{
		$showButtons = false;
	}
	else if($visibleEditButtons === true)
	{
		$showAllButtons = true;
	}
	else
	{
		$showButtons = true;
		$requestedButtonTypes = preg_split("/\,/", $visibleEditButtons);
		
		// Gösterilmesi istenen buton tiplerini bir object array olarak kaydet
		foreach($requestedButtonTypes as $b)
		{
			$b = trim($b);
			if($b == "all")
			{
				$showAllButtons = true;
			}
			
			$visibleButtonTypes->{$b} = true;
		}
	}
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	// Kullanıcının ekstra data eklemek isteyip istemediğini belirle------------------------------------------------
	if(!in_array(trim($appendExtraHtmlData), array("", null, false)))
	{
		if(is_string($appendExtraHtmlData))
		{
			$appendExtraHtml = true;
			
			// column adı eklenip eklenmediğini kontrol et
			preg_match_all("/\{\%([\w\_\-]+)\%\}/i", $appendExtraHtmlData, $matches);
			
			// eğer column adı eklenmişse
			if(is_array($matches[1])) //
			{
				$requestedColumnsCount = sizeof($matches[1]);
				$requestedColumnNames = $matches[1];
			}
		}
	}
	////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	
	// File listesini ayarla----------------------------------------------------------------------------------------
	$file_count = sizeof($files);
	for($i=0; $i<$file_count; $i++)
	{
		$file_id = $files[$i]->file_id;
		$thumb_info = getThumbInfo($file_id, 123, 87, false);
		$file_itself = $thumb_info->owner;
		$thumb_url = $thumb_info->url;
		$thumb_url2 = getThumbImage($file_id, 0,600, false);
		$file_url = "lookfile.php?type={$file_itself->type}&url={$thumb_url2}";
		$file_type = $files[$i]->type;
		
		$itemsList .= "<li class='gridFile' file='{$file_id}'>";
		$itemsList .= "<span class='shadow'></span>";
		$itemsList .= "<input type='hidden' name='{$filesNameKey}' />";
		$itemsList .= "<img class='thumbImage' src='$thumb_url' />";
		
		
		// Butonları Ayarla------------------------------------------------------------------------------------------
		if($showButtons === true)
		{
			$itemsList .= "<div class='buttonsOuter'>";
			
			$itemsList .= (($showAllButtons || ($visibleButtonTypes->edit === true)) ? "<span class='btnEdit fBtn' title='Düzenle' file='{$file_id}' filetype='{$file_type}'></span>" : "");
			
			if($file_type != "movie")
			{
				$itemsList .= (($showAllButtons || ($visibleButtonTypes->view === true)) ? "<a class='btnView fancybox fBtn' href='{$file_url}' title='İncele'></a>" : "");
			}
			else
			{
				$itemsList .= (($showAllButtons || ($visibleButtonTypes->play === true)) ? "<a class='btnPlay fancybox fBtn' href='{$file_url}' title='Oynat'></a>" : "");
			}
			
			$itemsList .= (($showAllButtons || ($visibleButtonTypes->delete === true)) ? "<span class='btnDelete fBtn' title='Kaldır'></span>" : "");
			$itemsList .= "</div>";
		}
		//////////////////////////////////////////////////////////////////////////////////////////////////////////////
		
		
		// Kullanıcının tanımladığı (eğer tanımlamışsa) ekstra html datasını işleyip ekle ------------------------------------------------
		if($appendExtraHtml)
		{
			if($requestedColumnsCount > 0)
			{
				$tempTemplate = $appendExtraHtmlData;
				
				for($j=0; $j<$requestedColumnsCount; $j++)
				{	
					$requestedName = $requestedColumnNames[$j];
					$value = $files[$i]->{$requestedName};
					
					$pattern = "/\{\%" . $requestedName . "\%\}/i";
					$tempTemplate = preg_replace($pattern, $value, $tempTemplate);
				}
				
				$itemsList .= $tempTemplate;
			}
			else
			{
				$itemsList .= $appendExtraHtmlData;
			}
			
			
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////////////////////////
		
		$itemsList .= "</li>";
	}
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	
	// Edit Template
	$template = preg_replace("/\{\%itemsList\%\}/", $itemsList, $template);
	$template = preg_replace("/\{\%gridId\%\}/", $gridId, $template);
	$template = preg_replace("/\{\%rowCount\%\}/", $rowCount, $template);
	$template = preg_replace("/\{\%columnCount\%\}/", $columnCount, $template);
	
	return $template;
}