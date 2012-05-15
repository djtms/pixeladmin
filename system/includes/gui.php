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
		<button class="dataGridAddButton" page="<?php echo $addDataLink; ?>">Yeni Ekle</button>
		<?php }?>
		<div class="itemsList">
			<ul id="<?php echo $gridId; ?>"  <?php echo ($sortablekey != null ? ' class="sortableList" sort_event="' . $sortEvent . '" ' : '') ?>>
				<?php
				if(is_array($data) && sizeof($data) > 0)
				{
					preg_match_all("/\<\%([a-zA-Z0-9\.\_\-\=]+)\%\>/", $rowTitleQuery, $rowTitleColumnsMatches);
					preg_match_all("/\<\%([a-zA-Z0-9\.\_\-]+)\%\>/", $editDataLinkQuery, $editDataColumnsMatches);
					preg_match_all("/\<\%([a-zA-Z0-9\.\_\-]+)\%\>/", $deleteDataLinkQuery, $deleteDataColumnsMatches);
					
					$use_edit_button = (($editDataLinkQuery != null) && (strlen(trim($editDataLinkQuery)) > 0)) ? true : false;
					$use_cross_button = (($deleteDataLinkQuery != null) && (strlen(trim($deleteDataLinkQuery)) > 0)) ? true : false;
					
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
												$value  = $search == "<%_index_%>" ? $index : ($dataType == "object" ? $d->{$column} : $d[$column]);
												
												$deleteLink = preg_replace("/" . preg_quote($search) . "/", $value, $deleteLink);
											}
											
											echo $deleteLink;
											
										?>" onclick="return false;"></a>
									<?php }
									
									if($use_edit_button){ ?>
										<a href="<?php 
											$editLink = $editDataLinkQuery;
									
											for($i=0; $i<sizeof($editDataColumnsMatches[0]); $i++)
											{
												$search = $editDataColumnsMatches[0][$i];
												$column = $editDataColumnsMatches[1][$i];
												$value = $search == "<%_index_%>" ? $index : ($dataType == "object" ? $d->{$column} : $d[$column]);
												
												$editLink = preg_replace("/" . preg_quote($search) . "/", $value, $editLink);	
											}
											
											echo $editLink;
										
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


function postMessage($message,$error=false)
{
	global $master;
	set_option("admin_postMessage",'<p ' . ($error ? ' style="color:#fc5900;" ' : '') . ' >' . $message . '</p>');
}