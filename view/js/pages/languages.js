$(StartLanguages);

function StartLanguages()
{
	$("[name='default_language'][value='" + defaultLanguage + "']").attr("checked", true);
}