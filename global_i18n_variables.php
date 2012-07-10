<?php

if($_POST["admin_action"] == "updateI18nData")
{
	extract($_POST, EXTR_OVERWRITE);
	$error = false;
	
	// Eğer i18nCode değeri değiştirilecekse
	if(($column == "i18nCode") && ($i18n_code != $value))
	{
		if($ADMIN->I18N->getI18n($value)) // Eğer yeni i18n kodu database'de zaten var ise 
		{
			$error = "Girdiğiniz i18nCode değeri kullanımda, lütfen başka bir değer girin!";
		}
	}
	
	// Eğer bu adıma kadar herhangi bir hata yok ise
	if($error === false)
	{
		if(($column == "i18nCode") && ($i18n_code == "") && $DB->insert($DB->tables->i18n, array("i18nCode"=>$value, "scope"=>"global")))
		{
			echo json_encode(array("success"=>true, "msg"=>""));
		}
		else if(($column == "") || ($i18n_code == "") || $DB->execute("UPDATE {$DB->tables->i18n} SET {$column}=?, scope='global' WHERE i18nCode=?", array($value, $i18n_code)))
		{
			echo json_encode(array("success"=>true, "msg"=>""));
		}
		else
		{
			echo json_encode(array("success"=>false, "msg"=>"* Beklenmedik bir hata oluştu!"));
		}
	}
	else
	{
		echo json_encode(array("success"=>false, "msg"=>$error));
	}
	
	exit;
}
else if($_POST["admin_action"] == "deleteI18nDatas")
{
	$i18nCodes = json_decode($_POST["i18nCodesList"]);
	$code_count = sizeof($i18nCodes);
	$error = false;
	
	for($i=0; $i<$code_count; $i++)
	{
		if(!deleteI18n($i18nCodes[$i]))
			$error = true;
	}
	
	echo json_encode(array("success"=>!$error, "msg"=>""));
	
	exit;
}


$i18n_columns = $DB->get_rows("SHOW COLUMNS FROM {$DB->tables->i18n} WHERE Field != 'scope'", null, FETCH_OBJ);
$i18n_data = $DB->get_rows("SELECT * FROM {$DB->tables->i18n} WHERE scope='global'", null, FETCH_NUM);

$column_count = sizeof($i18n_columns) + 1;
$row_count = sizeof($i18n_data) + 1;

$spreadSheetHeader = "";
$spreadSheetContent = "";

for($c=0; $c<$column_count; $c++)
{
	// İlk column da ise
	if($c === 0)
	{
		$spreadSheetContent .= "<div class='column rowIndex'>";	
	
		for($r=0; $r<$row_count; $r++)
		{
			if($r == 0)
			{
				$spreadSheetHeader .= "<div class='column rowIndex'>";
				$spreadSheetHeader .= "<span class='cell header'></span>";
				$spreadSheetHeader .= "</div>";
			}
			else
			{
				$spreadSheetContent .= "<span row_index='{$r}' class='cell rowCorner'></span>";
			}
		}
		
		$spreadSheetContent .= "</div>";
	}
	else
	{
		
		$spreadSheetContent .= "<div class='column'>";
		
		
		for($r=0; $r<$row_count; $r++)
		{
			$locale = $i18n_columns[$c - 1]->Field;
			$column_name = $i18n_columns[$c -1]->Field;
				
			// eğer birinci satırda ise başlıkları ayarla
			if($r == 0)
			{
				$language = $ADMIN->LANGUAGE->selectLanguage($locale);
				$headerText = $c == 1 ? "I18nCode" : $language->language_name . " - " . $language->country_abbr;
				
				$spreadSheetHeader .= "<div class='column'>";
				$spreadSheetHeader .= "<span class='cell header' column_name='" . $column_name . "'>" . $headerText . "</span>";
				$spreadSheetHeader .= "</div>";
			}
			else
			{
				// TODO: ilerde database'den column sırasını değiştirdiğinde sorun çıkabilir, o yüzden index numarası yerine column adı kullanarak data listelemeye çalış
				$column_index = $c == 1 ? $c-1 : $c;
				$i18n_code = $i18n_data[$r - 1][0];
				$tab_index = ($r * $column_count) + $c;
				$maxLengthAttr = $c == 1 ? " maxlength='255' " : "";
				
				$spreadSheetContent .= "<input {$maxLengthAttr} tabindex='{$tab_index}' row_index='{$r}' id='{$i18n_code}_{$column_name}' class='cell' type='text' column_name='{$column_name}' i18n_code='{$i18n_code}' value='" . $i18n_data[$r - 1][$column_index] . "' />";
			}
		}
		
		
		
		$spreadSheetContent .= "</div>";
	}
	
}


?>
<script type="text/javascript" src="view/js/pages/global_i18n_variables.js"></script>
<div class="spreadsheetOuter">
	<div class="spreadsheetHeader">
		<?php 
			echo $spreadSheetHeader;
		?>
	</div>
	<div class="spreadsheetContentScroller">
		<div class="spreadsheetContent">
		<?php 
			echo $spreadSheetContent;
		?>
		</div>
	</div>
	<div class="spreadsheetFooter">
		<textarea class="spreadsheetContentDisplay"></textarea>
	</div>
</div>