$(SettingsStart);

function SettingsStart()
{
	changeSMTPStatus();
	$("[name=smtp]").change(changeSMTPStatus);
}

function changeSMTPStatus()
{
	if($("[name=smtp]").attr("checked"))
	{
		$("#smtpSettingsOuter").css("display","block");
	}
	else
		$("#smtpSettingsOuter").css("display","none");
}