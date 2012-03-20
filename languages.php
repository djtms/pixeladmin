<?php
if(strlen($_GET["delete"]) > 0)
{
	if($MODEL->LANGUAGE->deleteLanguage($_GET["delete"]))
	{
		postMessage("Başarıyla Silindi!");
		header("Location:admin.php?page=languageoptions");
		exit;
	}
	else
	{
		postMessage("Hata: {$MODEL->LANGUAGE->error}", true);
	}
}

$data = $MODEL->LANGUAGE->listActiveLanguages();

dataGrid($data, "Mevcut Diller", "activeLanguages", "<%language_name%> / <%country_name%> / <%locale%>", "admin.php?page=edit_language", "admin.php?page=edit_language&locale=<%locale%>", "admin.php?page=languageoptions&delete=<%locale%>");
