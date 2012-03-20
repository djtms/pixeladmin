<?php

extract($_POST, EXTR_SKIP);

if(isset($pa_action))
{
	switch($pa_action)
	{
		case "l_c_b_l": // list countries by language
			echo json_encode($MODEL->LANGUAGE->listCountriesByLanguageAbbreviation($language));
		exit;
		
		case "edit_language":
			if($locale == "no_locale")
			{
				if($MODEL->LANGUAGE->addLanguage("{$language}_{$country}"))
				{
					postMessage("Başarıyla Kaydedildi!");
					header("Location:admin.php?page=languageoptions");
					exit;
				}
				else
				{
					postMessage("Hata Oluştu!", true);
				}
			}
			else
			{
				if($MODEL->LANGUAGE->updateLanguage($locale, "{$language}_{$country}"))
				{
					postMessage("Başarıyla Kaydedildi!");
					header("Location:admin.php?page=languageoptions");
					exit;
				}
				else
				{
					postMessage("Hata Oluştu!", true);
				}
			}
		break;
	}
}


$locale = strlen($_GET["locale"]) > 0 ? $_GET["locale"] : "no_locale";
$selected_language = $MODEL->LANGUAGE->selectLanguage($locale);

setGlobal("selected_language_abbr", isset($selected_language->language_abbr) ? $selected_language->language_abbr  : "no_language");
setGlobal("selected_country_abbr", isset($selected_language->country_abbr) ? $selected_language->country_abbr : "no_country");

?>

<form method="post">
	<input type="hidden" name="locale" value="<?php echo $locale; ?>" />
	<label>Dil:</label>
	<select name="language">
		<option value="null">Seçiniz</option>
		<?php 
			$languages = $MODEL->LANGUAGE->listLanguages();
			foreach($languages as $l)
			{
				?>
				<option value="<?php echo $l->language_abbr; ?>" <?php echo ($l->language_abbr == $selected_language->language_abbr ? ' selected="true" ' : ""); ?> ><?php echo $l->language_name; ?></option>
				<?php 	
			}
		?>
	</select>
	
	<label>Ülke</label>
	<select name="country">
		<option value="null">Seçiniz</option>
	</select>
	
	<button type="submit" name="pa_action" value="edit_language">Kaydet</button>
</form>


<?php 

addScript("js/pages/edit_language.js");