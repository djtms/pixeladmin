<?php

function dataGrid($data, $gridTitle, $gridId, $rowTitleQuery, $addDataLink, $editDataLinkQuery, $deleteDataLinkQuery, $sortablekey = null, $sortEvent = null)
{
	if(($gridId == null) || (strlen($gridId) <= 0))
	{
		$gridId = uniqid();
	}
	
	// DataGrid sıralama işlemi için ajax ve jqueryui bağlamasını yapıyoruz
	if(($sortablekey != null) && ($sortEvent != null))
	{
		if($_POST["admin_action"] == "sortDataGrid_$gridId")
		{
			$fixed_array = array();
			$orderList = $_POST["order"];
			
			foreach ($orderList as $val=>$key)
			{
				$fixed_array[] = (object) array("key"=>$key,"order"=>$val);
			}
			
			if($sortEvent($fixed_array) === false)
				echo json_encode(array("error"=>true));
			else
				echo json_encode(array("error"=>false));
			
			exit;
		}
	}
	
	?>
	<div class="dataGridOuter">
		<h2 class="dataGridTitle"><?php echo $gridTitle; ?></h2>
		<?php if($addDataLink != null){ ?>
		<button class="dataGridAddButton" page="<?php echo (preg_match("/\.php\?/", $addDataLink) ? $addDataLink : "admin.php?" . $addDataLink ); ?>">Yeni Ekle</button>
		<?php }?>
		<div class="itemsList">
			<ul id="<?php echo $gridId; ?>"  <?php echo ($sortablekey != null ? ' class="sortableList" sort_event="' . $sortEvent . '" ' : '') ?>>
				<?php
				if(is_array($data) && sizeof($data) > 0)
				{
					preg_match_all("/\{\%([a-zA-Z0-9\.\_\-\=]+)\%\}/", $rowTitleQuery, $rowTitleColumnsMatches);
					preg_match_all("/\{\%([a-zA-Z0-9\.\_\-]+)\%\}/", $editDataLinkQuery, $editDataColumnsMatches);
					preg_match_all("/\{\%([a-zA-Z0-9\.\_\-]+)\%\}/", $deleteDataLinkQuery, $deleteDataColumnsMatches);
					
					$use_edit_button = (($editDataLinkQuery != null) && (strlen(trim($editDataLinkQuery)) > 0)) ? true : false;
					$use_cross_button = (($deleteDataLinkQuery != null) && (strlen(trim($deleteDataLinkQuery)) > 0)) ? true : false;
					
					$edit_button_cleared_link = preg_match("/\.php\?/", $editDataLinkQuery) ? false : true;
					$cross_button_cleared_link = preg_match("/\.php\?/", $deleteDataLinkQuery) ? false : true;
					
					$index = 0;
					foreach($data as $d)
					{
						$dataType = gettype($d);
						$index++;
						?>
						<li class="item" <?php 
						if($sortablekey != null)
						{
							echo 'id="order_' . (($dataType == "object") ? $d->{$sortablekey} : $d[$sortablekey]) . '"';
						}
						?>><label class="text"><?php
								$rowTitle = $rowTitleQuery;
								
								for($i=0; $i<sizeof($rowTitleColumnsMatches[0]); $i++)
								{
									$search = $rowTitleColumnsMatches[0][$i];
									$column = $rowTitleColumnsMatches[1][$i];
									
									if(preg_match("/=/", $column))
									{
										$columnParts = explode("=", $column);
										
										if($columnParts[0] == "i18n")
										{
											$columnData = ($dataType == "object") ? getI18n($d->{$columnParts[1]}) : getI18n($d[$columnParts[1]]);
										}
									}
									else
									{
										$columnData = ($dataType == "object") ? $d->{$column} : $d[$column];									
									}
									
									$rowTitle = preg_replace("/" . preg_quote($search) . "/", $columnData, $rowTitle);
								}
								
								echo $rowTitle;
							?></label>
							<?php if($use_edit_button || $use_cross_button){ ?>
								<div class="rowEditButtonsOuter">
									<?php if($use_cross_button){ ?>
										<a class="crossBtn" href="<?php 
											$deleteLink = $deleteDataLinkQuery;
			
											for($i=0; $i<sizeof($deleteDataColumnsMatches[0]); $i++)
											{
												$search = $deleteDataColumnsMatches[0][$i];
												$column = $deleteDataColumnsMatches[1][$i];
												$value  = $search == "{%_index_%}" ? $index : ($dataType == "object" ? $d->{$column} : $d[$column]);
												
												$deleteLink = preg_replace("/" . preg_quote($search) . "/", $value, $deleteLink);
											}
											
											echo ($cross_button_cleared_link ? "admin.php?" . $deleteLink : $deleteLink);
											
										?>" onclick="return false;"></a>
									<?php }
									
									if($use_edit_button){ ?>
										<a href="<?php 
											$editLink = $editDataLinkQuery;
									
											for($i=0; $i<sizeof($editDataColumnsMatches[0]); $i++)
											{
												$search = $editDataColumnsMatches[0][$i];
												$column = $editDataColumnsMatches[1][$i];
												$value = $search == "{%_index_%}" ? $index : ($dataType == "object" ? $d->{$column} : $d[$column]);
												
												$editLink = preg_replace("/" . preg_quote($search) . "/", $value, $editLink);	
											}
											
											echo ($edit_button_cleared_link ? "admin.php?" . $editLink : $editLink);
										
										?>" class="editBtn"></a>
									<?php } ?>
								</div>
							<?php } ?>
						</li>
						<?php 
					}
				}
				else
				{
					?>
					<li class="item"><label class="text" style="color:#e00 !important;">Kayıt Bulunamadı!</label></li>
					<?php 
				}
				?>
			</ul>
		</div><!--itemsList-->
	</div>
	<?php 
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
	$visibleButtonTypes; // Kullanıcının atadığı değere göre gösterilmesi istenen buton tiplerinin listesini tutan değişken.
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
		$file_type = $files[$i]->type;
		$thumb_url = getThumbImage($file_id, 123, 87, false);
		$file_url = "lookfile.php?type={$file_type}&url={$files[$i]->url}";
		$file_type = $files[$i]->type;
		
		$itemsList .= "<li class='gridFile' file='{$file_id}'>";
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