<?php
if($_POST["admin_action"] == "Kaydet")
{
	if($ADMIN->LANGUAGE->setDefaultLanguage($_POST["default_language"]))
	{
		setGlobal("defaultLanguage", $ADMIN->I18N->language);
		postMessage("Başarıyla Kaydedildi!");
		header("Location:admin.php?page=languageoptions");
		exit;
	}
	else
		postMessage("Hata Oluştu!", true);
}

if(strlen($_GET["delete"]) > 0)
{
	if($ADMIN->LANGUAGE->deleteLanguage($_GET["delete"]))
	{
		postMessage("Başarıyla Silindi!");
		header("Location:admin.php?page=languageoptions");
		exit;
	}
	else
	{
		postMessage("Hata: {$ADMIN->LANGUAGE->error}", true);
	}
}

addScript("js/pages/languages.js");
?>
<form method="post">
	<?php
	$data = $ADMIN->LANGUAGE->listUserSelectedLanguages();
	echo dataGrid($data, "Mevcut Diller", "activeLanguages", "<input type='radio' name='default_language' value='{%locale%}' /> {%language_name%} / {%country_name%} / {%locale%}", "admin.php?page=add_language", "admin.php?page=edit_language&locale={%locale%}", "admin.php?page=languageoptions&delete={%locale%}");
	?>
	<input type="submit" name="admin_action" value="Kaydet" />
</form>