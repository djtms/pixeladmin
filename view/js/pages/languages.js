$(LanguagesStart);

function LanguagesStart()
{
	$("#languagesList .crossBtn").click(function(){
		
		if($("#languagesList .crossBtn").length < 2)
		{
			alert("En az bir dil bulunmalı!");
		}
		else if(confirm("Silmek istediğinizden eminmisiniz?"))
		{
			window.location.href = $(this).attr("deleteLink");
		}
	});
}