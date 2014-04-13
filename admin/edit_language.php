<?php

extract($_POST, EXTR_OVERWRITE);

if(isset($admin_action))
{

	switch($admin_action)
	{
		case "l_c_b_l": // list countries by language
			echo json_encode($ADMIN->LANGUAGE->listCountriesByLanguageAbbreviation($language));
		exit;
		
		case "get_date_format":
			if($language = $ADMIN->LANGUAGE->selectLanguage($locale))
			{
				echo json_encode(array("success"=>true, "format"=>$language->date_format));
			}
			else
			{
				echo json_encode(array("success"=>false));
			}
		exit;
		
		case "edit_language":
            if($locale == "no_locale")
			{
				if($ADMIN->LANGUAGE->addLanguage("{$language}_{$country}", $date_format))
				{
					postMessage($GT->BASARIYLA_KAYDEDILDI);
					header("Location:admin.php?page=languageoptions");
					exit;
				}
				else
				{
					postMessage($GT->HATA_OLUSTU, true);
				}
			}
			else
			{
				$status = ($_POST["status"] == "active" ? null : 0);
				if($ADMIN->LANGUAGE->updateLanguage($locale, "{$language}_{$country}", $status, $date_format))
				{
					postMessage($GT->BASARIYLA_KAYDEDILDI);
					header("Location:admin.php?page=languageoptions");
					exit;
				}
				else
				{
					postMessage($GT->HATA_OLUSTU, true);
				}
			}
		break;
	}
}


$locale = strlen($_GET["locale"]) > 0 ? $_GET["locale"] : "no_locale";
$selected_language = $ADMIN->LANGUAGE->selectLanguage($locale);

setGlobal("selected_language_abbr", isset($selected_language->language_abbr) ? $selected_language->language_abbr  : "no_language");
setGlobal("selected_country_abbr", isset($selected_language->country_abbr) ? $selected_language->country_abbr : "no_country");

?>

<form method="post">
	<input type="hidden" name="locale" value="<?php echo $locale; ?>" />
	<input type="checkbox" name="status" value="active" <?php echo ($selected_language->status > 0 ? " checked='true' " : ""); ?> />
	<label style="clear:none; margin:2px 0 0 0;"><?php echo $GT->AKTIF; ?></label>
	
	<label><?php echo $GT->DIL; ?>:</label>
	<select name="language">
		<option value="null"><?php echo $GT->SECINIZ; ?></option>
		<?php 
			$languages = $ADMIN->LANGUAGE->listLanguages();
			foreach($languages as $l)
			{
				?>
				<option value="<?php echo $l->language_abbr; ?>" <?php echo ($l->language_abbr == $selected_language->language_abbr ? ' selected="true" ' : ""); ?> ><?php echo $l->language_name; ?></option>
				<?php 	
			}
		?>
	</select>
	
	<label><?php echo $GT->ULKE; ?>:</label>
	<select id="country" name="country">
		<option value="null"><?php echo $GT->SECINIZ; ?></option>
	</select>
	
	<label><?php echo $GT->TARIH_FORMATI; ?>:</label>
	<input id="date_format" type="text" name="date_format" />
	
	<button type="submit" name="admin_action" value="edit_language"><?php echo $GT->KAYDET; ?></button>
</form>


<?php 

addScript("js/pages/edit_language.js");