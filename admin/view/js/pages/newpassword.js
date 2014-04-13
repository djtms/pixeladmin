$(NewpasswordStart);

function NewpasswordStart()
{
	$("#password_1").focus();
}

function changePassword()
{
	var password_1 = $.trim($("#password_1").val());
	var password_2 = $.trim($("#password_2").val());
	var error = "false";
	$("#resultText").html("");
	
	if(password_1.length < 6)		
	{
		error = GT.PAROLA_MIN_ALTI_KARAKTER;
	}
	else if(password_1 != password_2)
	{
		error = GT.HATA_PAROLALAR_ESLESMIYOR;
	}
	
	var password = SHA1($("#password_1").val());
	$("[name=password]").val(password);
	
	if(error == "false")
	{
		return true;
	}
	else
	{
		$("#resultText").html(error);
		return false;
	}
}