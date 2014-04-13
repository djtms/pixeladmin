<?php
if($_POST["admin_action"] == "save_language")
{
	if($ADMIN->LANGUAGE->setDefaultLanguage($_POST["default_language"]))
	{
		setGlobal("defaultLanguage", $ADMIN->I18N->language);
		postMessage($GT->BASARIYLA_KAYDEDILDI);
		header("Location:admin.php?page=languageoptions");
		exit;
	}
	else
		postMessage($GT->HATA_OLUSTU, true);
}

if(strlen($_GET["delete"]) > 0)
{
	if($ADMIN->LANGUAGE->deleteLanguage($_GET["delete"]))
	{
		postMessage($GT->BASARIYLA_KAYDEDILDI);
		header("Location:admin.php?page=languageoptions");
		exit;
	}
	else
	{
		postMessage($GT->HATA . ": {$ADMIN->LANGUAGE->error}", true);
	}
}

addScript("js/pages/languages.js");
?>
<form method="post">
	<?php
	$data = $ADMIN->LANGUAGE->listUserSelectedLanguages();
	echo dataGrid($data, $GT->MEVCUT_DILLER, "activeLanguages", "<input type='radio' name='default_language' value='{%locale%}' /> {%language_name%} / {%country_name%} / {%locale%}", "admin.php?page=add_language", "admin.php?page=edit_language&locale={%locale%}", "admin.php?page=languageoptions&delete={%locale%}");
	?>
	<button type="submit" name="admin_action" value="save_language"><?php echo $GT->KAYDET; ?></button>
</form>