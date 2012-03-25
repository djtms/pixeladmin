<?php
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

$data = $ADMIN->LANGUAGE->listActiveLanguages();

dataGrid($data, "Mevcut Diller", "activeLanguages", "<%language_name%> / <%country_name%> / <%locale%>", "admin.php?page=edit_language", "admin.php?page=edit_language&locale=<%locale%>", "admin.php?page=languageoptions&delete=<%locale%>");
